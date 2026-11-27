"""Failure boundaries using disposable Git and SVN repositories; never production."""

import importlib.util
from pathlib import Path
import shutil
import tempfile
import unittest
from unittest.mock import patch
import zipfile

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('release', ROOT / 'scripts/release.py')
release = importlib.util.module_from_spec(spec)
spec.loader.exec_module(release)


class ReleaseTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.base = Path(self.temporary.name)
        self.source = self.base / 'source'
        self.source.mkdir()
        self.output = self.base / 'output'
        for name in release.inventory(ROOT):
            destination = self.source / name
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(ROOT / name, destination)
        (self.source / '.github').mkdir()
        for name in ('release-files.txt', 'release-baseline.json', 'release-removals.json'):
            shutil.copyfile(ROOT / '.github' / name, self.source / '.github' / name)
        release.run('git', 'init', '-q', cwd=self.source)
        release.run('git', 'config', 'user.email', 'test@example.invalid', cwd=self.source)
        release.run('git', 'config', 'user.name', 'Release test', cwd=self.source)
        release.run('git', 'add', '.', cwd=self.source)
        release.run('git', 'commit', '-qm', 'fixture', cwd=self.source)

    def tearDown(self):
        shutil.rmtree(self.base, ignore_errors=True)
        self.temporary.cleanup()

    def build(self):
        release.build(self.source, self.output)

    def test_complete_recursive_reproducible_package(self):
        self.build()
        release.build(self.source, self.base / 'second')
        self.assertEqual((self.output / (release.SLUG + '.zip')).read_bytes(),
                         (self.base / 'second' / (release.SLUG + '.zip')).read_bytes())
        self.assertEqual(set(release.manifest(self.output / release.SLUG)),
                         set(release.inventory(self.source)))

    def test_missing_nested_file_aborts(self):
        (self.source / 'inc/ald-functions.php').unlink()
        with self.assertRaisesRegex(RuntimeError, 'Missing/empty'):
            self.build()

    def test_empty_asset_file_aborts(self):
        (self.source / 'assets/scripts.js').write_text('')
        with self.assertRaisesRegex(RuntimeError, 'Missing/empty'):
            self.build()

    def test_unlisted_runtime_file_aborts(self):
        (self.source / 'inc/new-unlisted.php').write_text('<?php')
        release.run('git', 'add', '.', cwd=self.source)
        with self.assertRaisesRegex(RuntimeError, 'inventory'):
            self.build()

    def test_version_mismatch_aborts(self):
        readme = self.source / 'README.txt'
        readme.write_text(readme.read_text().replace('Stable tag: ' + release.version(ROOT), 'Stable tag: 0.0.1'))
        with self.assertRaisesRegex(RuntimeError, 'disagree'):
            self.build()

    def test_wrong_tag_and_dirty_release_abort(self):
        with self.assertRaisesRegex(RuntimeError, 'Tag must match'):
            release.build(self.source, self.output, 'v0.0.1')
        (self.source / 'inc/ald-functions.php').write_text('<?php // dirty')
        with self.assertRaisesRegex(RuntimeError, 'must be clean'):
            release.build(self.source, self.output, 'v' + release.version(ROOT))

    def test_symlink_rejected(self):
        path = self.source / 'inc/ald-functions.php'
        path.unlink()
        path.symlink_to(ROOT / 'inc/ald-functions.php')
        release.run('git', 'add', '.', cwd=self.source)
        with self.assertRaisesRegex(RuntimeError, 'Gitlink/symlink'):
            self.build()

    def test_unexplained_deletion_rejected(self):
        with self.assertRaisesRegex(RuntimeError, 'unexplained deletions'):
            release.check_deletions({'inc/nested.php': 'hash'}, {}, set(), 'test')
        self.assertEqual(release.check_deletions({'old.php': 'hash'}, {}, {'old.php'}, 'test'), {'old.php'})

    def test_zip_roots_traversal_duplicates_and_missing_nested_file(self):
        for names in [['wrong-root/file.php'], [release.SLUG + '/../escape'],
                      [release.SLUG + '/file.php', release.SLUG + '/file.php']]:
            path = self.base / 'bad.zip'
            with zipfile.ZipFile(path, 'w') as archive:
                for name in names:
                    archive.writestr(name, 'bad')
            with self.assertRaises(RuntimeError):
                release.zip_manifest(path)
        self.build()
        with self.assertRaisesRegex(RuntimeError, 'missing='):
            release.compare(release.manifest(self.output / release.SLUG),
                            {'ajax-load-div.php': 'bad'}, 'incomplete archive')

    def local_svn_stage(self):
        self.build()
        wc = self.output / 'svn'
        repository = self.base / 'repository'
        release.run('svnadmin', 'create', str(repository))
        url = repository.as_uri()
        release.run('svn', 'mkdir', url + '/trunk', url + '/tags', url + '/assets', '-m', 'initial')
        release.run('svn', 'checkout', '-q', url, str(wc))
        shutil.copytree(self.output / release.SLUG, wc / 'trunk', dirs_exist_ok=True)
        release.run('svn', 'add', '--force', 'trunk', cwd=wc)
        release.run('svn', 'commit', '-qm', 'baseline', cwd=wc)
        before = release.manifest(wc / 'trunk')
        release.run('svn', 'copy', 'trunk', 'tags/' + release.version(self.source), cwd=wc)
        state = {'version': release.version(self.source), 'baseline': before,
                 'before_trunk': before, 'assets': {}, 'assets_only': False}
        release.write_json(self.output / 'stage.json', state)
        return wc

    def test_listing_assets_excluded_from_zip_and_staged_with_mime_types(self):
        wp_dir = self.source / '.wordpress'
        wp_dir.mkdir()
        (wp_dir / 'icon.svg').write_text('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"></svg>')
        (wp_dir / 'icon-256x256.png').write_bytes(b'\x89PNG\r\n\x1a\nicon-256')
        (wp_dir / 'banner-1544x500.png').write_bytes(b'\x89PNG\r\n\x1a\nbanner-1544')
        (wp_dir / 'screenshot-1.png').write_bytes(b'\x89PNG\r\n\x1a\nshot-1')
        release.run('git', 'add', '.wordpress', cwd=self.source)
        release.run('git', 'commit', '-qm', 'add listing assets', cwd=self.source)
        self.build()

        zip_files = release.zip_manifest(self.output / (release.SLUG + '.zip'))
        self.assertFalse(any(p.startswith('.wordpress') or p.startswith('screenshot-') for p in zip_files))
        listing_manifest = release.read_json(self.output / 'listing-assets-manifest.json')
        self.assertEqual(sorted(listing_manifest),
                         ['banner-1544x500.png', 'icon-256x256.png', 'icon.svg', 'screenshot-1.png'])

        repository = self.base / 'initial-repo'
        release.run('svnadmin', 'create', str(repository))
        url = repository.as_uri()
        release.run('svn', 'mkdir', url + '/trunk', url + '/tags', url + '/assets', '-m', 'initial')
        release.write_json(self.source / '.github/release-baseline.json', {'version': None, 'files': {}})
        with patch.object(release, 'SVN_URL', url):
            release.stage(self.source, self.output)
        staged_assets = release.manifest(self.output / 'svn/assets')
        self.assertEqual(staged_assets, listing_manifest)
        self.assertEqual(
            release.run('svn', 'propget', 'svn:mime-type', 'assets/icon.svg', cwd=self.output / 'svn').strip(),
            'image/svg+xml'
        )
        self.assertEqual(
            release.run('svn', 'propget', 'svn:mime-type', 'assets/banner-1544x500.png', cwd=self.output / 'svn').strip(),
            'image/png'
        )

        (wp_dir / 'random-note.txt').write_text('not allowed')
        release.run('git', 'add', '.wordpress/random-note.txt', cwd=self.source)
        with self.assertRaisesRegex(RuntimeError, 'Invalid WordPress.org listing asset filename'):
            release.build(self.source, self.base / 'invalid-asset-build')

    def test_local_svn_accepts_complete_tag_rejects_tampering(self):
        wc = self.local_svn_stage()
        release.validate_stage(self.source, self.output)
        (wc / 'trunk/inc/ald-functions.php').unlink()
        with self.assertRaisesRegex(RuntimeError, 'Staged trunk: missing='):
            release.validate_stage(self.source, self.output)

    def test_local_svn_rejects_listing_asset_changes(self):
        wc = self.local_svn_stage()
        (wc / 'assets/unexpected.txt').write_text('wrong target')
        with self.assertRaisesRegex(RuntimeError, 'Listing assets'):
            release.validate_stage(self.source, self.output)

    def test_publish_denied_outside_actions(self):
        with patch.dict('os.environ', {'GITHUB_ACTIONS': 'false'}):
            with self.assertRaisesRegex(RuntimeError, 'only from GitHub Actions'):
                release.publish(self.source, self.output, 'v0.0.0')

    def prepare_publish(self):
        self.local_svn_stage()
        state = release.read_json(self.output / 'stage.json')
        state.update(root_changed=1, change_digest=release.validate_stage(self.source, self.output))
        release.write_json(self.output / 'stage.json', state)
        data = release.read_json(self.output / 'build.json')
        data['tag'] = 'v' + state['version']
        release.write_json(self.output / 'build.json', data)
        return state

    def test_changed_remote_aborts_before_commit(self):
        state = self.prepare_publish()
        with patch.dict('os.environ', {'GITHUB_ACTIONS': 'true', 'SVN_USERNAME': 'fixture', 'SVN_PASSWORD': 'fixture'}):
            with patch.object(release, 'svn_info', return_value=(9, 2)):
                with self.assertRaisesRegex(RuntimeError, 'Remote SVN changed'):
                    release.publish(self.source, self.output, 'v' + state['version'])

    def test_changed_stage_digest_aborts_before_commit(self):
        state = self.prepare_publish()
        state['change_digest'] = 'unapproved'
        release.write_json(self.output / 'stage.json', state)
        with patch.dict('os.environ', {'GITHUB_ACTIONS': 'true', 'SVN_USERNAME': 'fixture', 'SVN_PASSWORD': 'fixture'}):
            with self.assertRaisesRegex(RuntimeError, 'changed after validation'):
                release.publish(self.source, self.output, 'v' + state['version'])

    def test_existing_tag_is_immutable_and_validation_only_checks_hashes(self):
        wc = self.local_svn_stage()
        release.run('svn', 'commit', '-qm', 'publish fixture tag', cwd=wc)
        output = self.base / 'existing-check'
        release.build(self.source, output)
        with patch.object(release, 'SVN_URL', (self.base / 'repository').as_uri()):
            with self.assertRaisesRegex(RuntimeError, 'already exists'):
                release.stage(self.source, output)
            release.stage(self.source, output, check_existing=True)
            self.assertFalse((output / 'stage.json').exists())
            self.assertTrue((output / 'existing-release.json').exists())


if __name__ == '__main__':
    unittest.main()
