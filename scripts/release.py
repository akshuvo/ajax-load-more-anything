#!/usr/bin/env python3
"""Build, stage and verify Load More Anything WordPress.org releases. Only `publish` writes to SVN."""

import argparse
import hashlib
import json
import os
from pathlib import Path, PurePosixPath
import re
import shutil
import subprocess
import sys
import time
import urllib.request
import xml.etree.ElementTree as ET
import zipfile


SLUG = os.environ.get('WPORG_PLUGIN_SLUG', 'ajax-load-more-anything')
SVN_URL = 'https://plugins.svn.wordpress.org/' + SLUG
RUNTIME_DIRS = {'admin', 'appsero', 'assets', 'inc', 'languages'}
ROOT_FILES = {'ajax-load-div.php', 'README.txt', 'README.md'}
LISTING_ASSET_DIR = '.wordpress'
LISTING_BLUEPRINT = 'blueprints/blueprint.json'
LISTING_ASSET_RE = re.compile(
    r'^(banner-(772x250|1544x500)\.(png|jpg)|icon-(128x128|256x256)\.(png|jpg)|icon\.svg|screenshot-[1-9]\d*\.(png|jpg|gif)|blueprints/blueprint\.json)$'
)
LISTING_ASSET_MIME = {
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.json': 'application/json',
}


def require(condition, message):
    if not condition:
        raise RuntimeError(message)


def run(*args, cwd=None, stdin=None):
    # Never include command arguments in exceptions: SVN authentication is private.
    result = subprocess.run(args, cwd=cwd, input=stdin, stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE)
    require(result.returncode == 0, args[0] + ' failed: ' + result.stderr.decode(errors='replace'))
    return result.stdout.decode()


def write_json(path, value):
    path.write_text(json.dumps(value, indent=2, sort_keys=True) + '\n')


def read_json(path):
    return json.loads(path.read_text())


def safe_path(value):
    p = PurePosixPath(value)
    require(value and not p.is_absolute() and '..' not in p.parts and
            str(p) == value and '\\' not in value and '@' not in value,
            'Unsafe package path: ' + value)
    return value


def inventory(source):
    paths = (source / '.github/release-files.txt').read_text().splitlines()
    require(paths == sorted(set(paths)) and paths, 'Inventory must be sorted and unique')
    for path in paths:
        safe_path(path)
        require(path in ROOT_FILES or path.split('/')[0] in RUNTIME_DIRS,
                'Development file in inventory: ' + path)
    require(ROOT_FILES <= set(paths), 'Required root files missing')
    for directory in RUNTIME_DIRS:
        require(any(p.startswith(directory + '/') for p in paths),
                'Required runtime directory missing: ' + directory)
    return paths


def manifest(root):
    require(root.is_dir() and not root.is_symlink(), 'Missing/unsafe directory: ' + str(root))
    files = {}
    for path in sorted(root.rglob('*')):
        relative = path.relative_to(root)
        if '.svn' in relative.parts:
            continue
        require(not path.is_symlink(), 'Symlink rejected: ' + str(relative))
        if path.is_file():
            files[safe_path(relative.as_posix())] = hashlib.sha256(path.read_bytes()).hexdigest()
    return files


def compare(expected, actual, label):
    missing = sorted(expected.keys() - actual.keys())
    extra = sorted(actual.keys() - expected.keys())
    changed = sorted(p for p in expected.keys() & actual.keys() if expected[p] != actual[p])
    require(not (missing or extra or changed),
            f'{label}: missing={missing}, extra={extra}, changed={changed}')


def version(source):
    main = (source / 'ajax-load-div.php').read_text()
    header_match = re.search(r'^\s*\*\s*Version:\s*([^\s]+)', main, re.M)
    const_match = re.search(r"const\s+VERSION\s*=\s*'([^']+)'", main)
    readme_match = re.search(r'^Stable tag:\s*(\S+)', (source / 'README.txt').read_text(), re.M)
    require(header_match and const_match and readme_match, 'Missing version declaration')
    values = [header_match.group(1), const_match.group(1), readme_match.group(1)]
    require(len(set(values)) == 1, 'Version declarations disagree: ' + repr(values))
    require(re.fullmatch(r'\d+\.\d+\.\d+', values[0]), 'Use a numeric patch version')
    return values[0]


def zip_manifest(path):
    with zipfile.ZipFile(path) as archive:
        require(archive.testzip() is None, 'ZIP CRC check failed')
        files = {}
        for item in archive.infolist():
            require(item.filename.startswith(SLUG + '/'), 'Incorrect ZIP root')
            if item.is_dir():
                continue
            name = safe_path(item.filename[len(SLUG) + 1:])
            require(name not in files, 'Duplicate ZIP entry: ' + name)
            require((item.external_attr >> 16) & 0o170000 != 0o120000, 'ZIP symlink rejected')
            files[name] = hashlib.sha256(archive.read(item)).hexdigest()
        return files


def listing_assets(source, allow_untracked=False):
    asset_dir = source / LISTING_ASSET_DIR
    require(not asset_dir.is_symlink(), 'Unsafe listing asset directory')
    if not asset_dir.exists():
        return []
    require(asset_dir.is_dir(), 'Unsafe listing asset directory')
    blueprint_dir = (asset_dir / LISTING_BLUEPRINT).parent
    require(not blueprint_dir.is_symlink(), 'Symlink rejected: blueprints')
    names = []
    if allow_untracked:
        for path in sorted(asset_dir.rglob('*')):
            filename = path.relative_to(asset_dir).as_posix()
            require(not path.is_symlink(), 'Symlink rejected: ' + filename)
            if path.is_dir():
                require(path == blueprint_dir, 'Nested listing asset directory rejected: ' + filename)
                continue
            if filename == '.DS_Store':
                continue
            require(LISTING_ASSET_RE.fullmatch(filename), 'Invalid WordPress.org listing asset filename: ' + filename)
            names.append(filename)
        return sorted(names)
    entries = run('git', 'ls-files', '--stage', '-z', '--', LISTING_ASSET_DIR, cwd=source).split('\0')
    for entry in filter(None, entries):
        metadata, path = entry.split('\t', 1)
        require(metadata.split()[0] in ('100644', '100755'), 'Gitlink/symlink rejected: ' + path)
        filename = PurePosixPath(path).relative_to(LISTING_ASSET_DIR).as_posix()
        require(LISTING_ASSET_RE.fullmatch(filename), 'Invalid WordPress.org listing asset filename: ' + filename)
        names.append(filename)
    return sorted(names)


def collect_runtime_paths(source, allow_untracked=False):
    if allow_untracked:
        found = []
        for root_file in sorted(ROOT_FILES):
            candidate = source / root_file
            if candidate.exists():
                require(candidate.is_file() and not candidate.is_symlink(), 'Unsafe root file: ' + root_file)
                found.append(root_file)
        for runtime_dir in sorted(RUNTIME_DIRS):
            directory = source / runtime_dir
            if directory.exists():
                require(directory.is_dir() and not directory.is_symlink(), 'Unsafe runtime dir: ' + runtime_dir)
                for path in sorted(directory.rglob('*')):
                    if path.is_symlink():
                        require(False, 'Gitlink/symlink rejected: ' + path.relative_to(source).as_posix())
                    if path.is_file() and path.name != '.DS_Store':
                        found.append(path.relative_to(source).as_posix())
        return sorted(found)

    entries = run('git', 'ls-files', '--stage', '-z', cwd=source).split('\0')
    tracked = []
    for entry in filter(None, entries):
        metadata, name = entry.split('\t', 1)
        if name in ROOT_FILES or name.split('/')[0] in RUNTIME_DIRS:
            require(metadata.split()[0] in ('100644', '100755'), 'Gitlink/symlink rejected: ' + name)
            tracked.append(name)
    return sorted(tracked)


def build(source, output, tag=None, allow_untracked=False):
    require(not output.exists(), 'Output must be a fresh directory')
    require(not output.is_relative_to(source), 'Build outside the source checkout')
    if tag:
        require(not allow_untracked, 'Release builds cannot allow untracked files')
    paths = inventory(source)
    tracked = collect_runtime_paths(source, allow_untracked=allow_untracked)
    require(tracked == paths, 'Tracked runtime paths differ from release inventory')
    asset_names = listing_assets(source, allow_untracked=allow_untracked)
    release_version = version(source)
    if tag:
        require(tag == 'v' + release_version, 'Tag must match every version declaration')
        require(not run('git', 'status', '--porcelain', '--untracked-files=all', cwd=source).strip(),
                'Release checkout must be clean')
        require(run('git', 'rev-parse', 'HEAD', cwd=source) ==
                run('git', 'rev-parse', 'refs/tags/' + tag + '^{commit}', cwd=source),
                'HEAD differs from the release tag commit')
    package = output / SLUG
    package.mkdir(parents=True)
    for name in paths:
        original = source / name
        require(original.is_file() and not original.is_symlink() and original.stat().st_size > 0,
                'Missing/empty required file: ' + name)
        destination = package / name
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(original, destination)
        destination.chmod(0o644)
    expected = manifest(package)
    listing_manifest = {}
    if asset_names:
        listing_dir = output / 'listing-assets'
        listing_dir.mkdir(parents=True)
        for name in asset_names:
            original = source / LISTING_ASSET_DIR / name
            require(original.is_file() and not original.is_symlink() and original.stat().st_size > 0,
                    'Missing/empty listing asset: ' + name)
            if name == LISTING_BLUEPRINT:
                require(isinstance(read_json(original), dict), 'Playground Blueprint must be a JSON object')
            destination = listing_dir / name
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(original, destination)
            destination.chmod(0o644)
        listing_manifest = manifest(listing_dir)
    archive_path = output / (SLUG + '.zip')
    with zipfile.ZipFile(archive_path, 'w', compression=zipfile.ZIP_DEFLATED) as archive:
        for name in paths:
            item = zipfile.ZipInfo(SLUG + '/' + name, date_time=(2020, 1, 1, 0, 0, 0))
            item.external_attr = 0o100644 << 16
            archive.writestr(item, (package / name).read_bytes(), compress_type=zipfile.ZIP_DEFLATED)
    compare(expected, zip_manifest(archive_path), 'Built ZIP')
    write_json(output / 'manifest.json', expected)
    write_json(output / 'listing-assets-manifest.json', listing_manifest)
    write_json(output / 'build.json', {'version': release_version, 'tag': tag,
               'slug': SLUG,
               'commit': run('git', 'rev-parse', 'HEAD', cwd=source).strip(),
               'files': len(expected), 'listing_assets': len(listing_manifest)})
    print(f'Built {SLUG} {release_version}: {len(expected)} files, {len(listing_manifest)} listing assets; ZIP hashes match')


def svn_info(url):
    entry = ET.fromstring(run('svn', 'info', '--xml', '--non-interactive', url)).find('entry')
    return int(entry.get('revision')), int(entry.find('commit').get('revision'))


def svn_exists(url):
    result = subprocess.run(['svn', 'info', '--xml', '--non-interactive', url],
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    return result.returncode == 0


def export(url, destination, revision):
    run('svn', 'export', '--quiet', '--non-interactive', '-r', str(revision), url, str(destination))
    return manifest(destination)


def allowed_removals(source, release_version):
    removals = read_json(source / '.github/release-removals.json').get(release_version, {})
    for path, reason in removals.items():
        safe_path(path)
        require(isinstance(reason, str) and len(reason.strip()) >= 20,
                'A reviewed removal needs a meaningful reason: ' + path)
    return set(removals)


def check_deletions(before, expected, allowed, label):
    missing = set(before) - set(expected)
    require(not (missing - allowed), label + ': unexplained deletions: ' + repr(sorted(missing - allowed)))
    return missing


def status_entries(wc):
    status = run('svn', 'status', '--xml', '--no-ignore', cwd=wc)
    entries = []
    for entry in ET.fromstring(status).iter('entry'):
        state = entry.find('wc-status')
        entries.append((entry.get('path').replace(os.sep, '/'), state.get('item'), state.get('props')))
    return status, entries


def validate_stage(source, output):
    state = read_json(output / 'stage.json')
    expected = read_json(output / 'manifest.json')
    wc = output / 'svn'
    assets_only = bool(state.get('assets_only', False))
    compare(expected, manifest(output / SLUG), 'Approved package')
    compare(expected, zip_manifest(output / (SLUG + '.zip')), 'Approved ZIP')
    if not assets_only:
        compare(expected, manifest(wc / 'trunk'), 'Staged trunk')
        compare(expected, manifest(wc / 'tags' / state['version']), 'Staged tag')
    compare(state['assets'], manifest(wc / 'assets'), 'Listing assets')
    staged_assets = set(state.get('staged_assets', []))
    allowed = set() if assets_only else allowed_removals(source, state['version'])
    if not assets_only:
        removed = check_deletions(state['baseline'], expected, allowed, 'Known-good release')
        removed |= check_deletions(state['before_trunk'], expected, allowed, 'SVN trunk')
        require(allowed <= removed, 'Stale/unneeded removal exceptions must be removed')
    status, entries = status_entries(wc)
    for path, item, props in entries:
        is_allowed_asset = (
            path.startswith('assets/') and
            path.removeprefix('assets/') in staged_assets and
            item in ('added', 'modified', 'normal')
        )
        is_allowed_asset = is_allowed_asset or (
            path == 'assets/blueprints' and LISTING_BLUEPRINT in staged_assets and
            item == 'added' and props == 'none'
        )
        if assets_only:
            require(is_allowed_asset, 'SVN change outside approved assets: ' + path)
        else:
            require(path == 'trunk' or path.startswith('trunk/') or
                    path == 'tags/' + state['version'] or path.startswith('tags/' + state['version'] + '/') or
                    is_allowed_asset,
                    'SVN change outside trunk/new tag/approved assets: ' + path)
        require(item in ('added', 'modified', 'deleted', 'normal') and
                (props in ('none', 'normal') or (item == 'added' and props == 'modified')),
                'Unexpected SVN status/property change: ' + repr((path, item, props)))
        if item == 'deleted':
            relative = path.removeprefix('trunk/')
            require(relative in allowed, 'Unexpected SVN deletion: ' + relative)
    (output / 'svn-status.xml').write_text(status)
    diff = run('svn', 'diff', cwd=wc)
    (output / 'svn-diff.txt').write_text(diff)
    return hashlib.sha256((ET.canonicalize(status) + diff).encode()).hexdigest()


def stage_listing_assets(output, wc, desired_assets, before_assets):
    if not desired_assets:
        return
    for name in sorted(desired_assets):
        require(LISTING_ASSET_RE.fullmatch(name), 'Invalid WordPress.org listing asset filename: ' + name)
        destination = wc / 'assets' / name
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(output / 'listing-assets' / name, destination)
    run('svn', 'add', '--force', '--no-ignore', '--no-auto-props', 'assets', cwd=wc)
    for name in sorted(desired_assets.keys() - before_assets.keys()):
        mime = LISTING_ASSET_MIME[Path(name).suffix.lower()]
        run('svn', 'propset', 'svn:mime-type', mime, 'assets/' + name, cwd=wc)


def stage(source, output, check_existing=False, allow_missing_svn=False, assets_only=False):
    build_data = read_json(output / 'build.json')
    release_version = build_data['version']
    require(version(source) == release_version, 'Source version changed')
    require(not (output / 'svn').exists(), 'Stage once into a fresh build')

    if allow_missing_svn and not svn_exists(SVN_URL):
        write_json(output / 'svn-pending.json', {
            'slug': SLUG,
            'svn_url': SVN_URL,
            'status': 'WordPress.org SVN repository not yet provisioned; skipped SVN staging in validation mode',
        })
        print(f'WordPress.org SVN repository {SVN_URL} not yet provisioned; validation-only build verified')
        return

    revision, root_changed = svn_info(SVN_URL)
    tags = ET.fromstring(run('svn', 'list', '--xml', '-r', str(revision), SVN_URL + '/tags'))
    existing_tags = [e.findtext('name') for e in tags.iter('entry')]
    existing = release_version in existing_tags

    if assets_only:
        listing_manifest_path = output / 'listing-assets-manifest.json'
        desired_assets = read_json(listing_manifest_path) if listing_manifest_path.is_file() else {}
        require(bool(desired_assets), 'No listing assets in .wordpress/ to stage')
        wc = output / 'svn'
        run('svn', 'checkout', '--quiet', '--non-interactive', '--depth', 'immediates',
            '-r', str(revision), SVN_URL, str(wc))
        run('svn', 'update', '--quiet', '--non-interactive', '--set-depth', 'infinity',
            '-r', str(revision), 'assets', cwd=wc)
        before_assets = manifest(wc / 'assets')
        stage_listing_assets(output, wc, desired_assets, before_assets)
        expected_assets = {**before_assets, **desired_assets}
        state = {'version': release_version, 'revision': revision, 'root_changed': root_changed,
                 'baseline': {}, 'before_trunk': {}, 'assets': expected_assets,
                 'staged_assets': sorted(desired_assets), 'assets_only': True}
        write_json(output / 'stage.json', state)
        state['change_digest'] = validate_stage(source, output)
        write_json(output / 'stage.json', state)
        print(f'Staged {len(desired_assets)} listing assets (assets-only) at SVN r{revision}. No commit made.')
        return

    if existing and check_existing:
        require(build_data['tag'] is None, 'Existing-tag check is validation-only')
        expected = read_json(output / 'manifest.json')
        for name, url in [('existing-tag', '/tags/' + release_version), ('existing-trunk', '/trunk')]:
            compare(expected, export(SVN_URL + url, output / name, revision), name)
        write_json(output / 'existing-release.json', {'version': release_version, 'files': len(expected),
                   'revision': revision, 'status': 'matches published release; no SVN changes staged'})
        print('Existing release matches exactly; no SVN changes staged')
        return
    require(not existing, 'SVN tag already exists; never overwrite it')

    baseline_policy = read_json(source / '.github/release-baseline.json')
    baseline_version = baseline_policy.get('version')
    if baseline_version is None:
        require(not existing_tags,
                'SVN already has published tags; pin .github/release-baseline.json before releasing')
        baseline = {}
    else:
        require(tuple(map(int, release_version.split('.'))) >
                tuple(map(int, baseline_version.split('.'))), 'Version must exceed known-good baseline')
        baseline = export(SVN_URL + '/tags/' + baseline_version, output / 'baseline', revision)
        compare(baseline_policy['files'], baseline, 'Pinned known-good SVN release')

    wc = output / 'svn'
    run('svn', 'checkout', '--quiet', '--non-interactive', '--depth', 'immediates',
        '-r', str(revision), SVN_URL, str(wc))
    run('svn', 'update', '--quiet', '--non-interactive', '--set-depth', 'infinity',
        '-r', str(revision), 'trunk', 'assets', cwd=wc)
    before_trunk = manifest(wc / 'trunk')
    expected = read_json(output / 'manifest.json')
    allowed = allowed_removals(source, release_version)
    check_deletions(baseline, expected, allowed, 'Known-good release')
    deletions = check_deletions(before_trunk, expected, allowed, 'SVN trunk')
    write_json(output / 'before-trunk.json', before_trunk)
    write_json(output / 'baseline-manifest.json', baseline)
    for name in sorted(deletions):
        run('svn', 'delete', '--', 'trunk/' + name, cwd=wc)
    for name in expected:
        destination = wc / 'trunk' / name
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(output / SLUG / name, destination)
    run('svn', 'add', '--force', '--no-ignore', '--no-auto-props', 'trunk', cwd=wc)
    run('svn', 'copy', 'trunk', 'tags/' + release_version, cwd=wc)
    before_assets = manifest(wc / 'assets')
    listing_manifest_path = output / 'listing-assets-manifest.json'
    desired_assets = read_json(listing_manifest_path) if listing_manifest_path.is_file() else {}
    stage_listing_assets(output, wc, desired_assets, before_assets)
    expected_assets = {**before_assets, **desired_assets}
    state = {'version': release_version, 'revision': revision, 'root_changed': root_changed,
             'baseline': baseline, 'before_trunk': before_trunk, 'assets': expected_assets,
             'staged_assets': sorted(desired_assets), 'assets_only': False}
    write_json(output / 'stage.json', state)
    state['change_digest'] = validate_stage(source, output)
    write_json(output / 'stage.json', state)
    print(f'Staged {len(expected)} files and {len(desired_assets)} listing assets at SVN r{revision}; no unexplained deletions. No commit made.')


def publish(source, output, tag=None):
    require(os.environ.get('GITHUB_ACTIONS') == 'true', 'Publish only from GitHub Actions')
    require(os.environ.get('SVN_USERNAME') and os.environ.get('SVN_PASSWORD'), 'Missing SVN secrets')
    state = read_json(output / 'stage.json')
    build_data = read_json(output / 'build.json')
    assets_only = bool(state.get('assets_only', False))
    if not assets_only:
        require(tag == 'v' + state['version'] == build_data['tag'], 'Release tag mismatch')
    require(run('git', 'rev-parse', 'HEAD', cwd=source).strip() == build_data['commit'] and
            not run('git', 'status', '--porcelain', '--untracked-files=all', cwd=source).strip(),
            'Release source changed after build')
    require(validate_stage(source, output) == state['change_digest'], 'SVN changes changed after validation')
    require(svn_info(SVN_URL)[1] == state['root_changed'], 'Remote SVN changed; rebuild and review')
    commit_message = (
        'Update WordPress.org listing assets from ' + build_data['commit']
        if assets_only
        else 'Release ' + state['version'] + ' from ' + build_data['commit']
    )
    result = run('svn', 'commit', '--non-interactive', '--no-auth-cache',
                 '--username', os.environ['SVN_USERNAME'], '--password-from-stdin',
                 '-m', commit_message,
                 cwd=output / 'svn', stdin=(os.environ['SVN_PASSWORD'] + '\n').encode())
    (output / 'svn-commit.txt').write_text(result)
    print(result.strip())


def download(url, destination):
    request = urllib.request.Request(url, headers={'User-Agent': 'LoadMoreAnything-release-verification'})
    with urllib.request.urlopen(request, timeout=60) as response:
        destination.write_bytes(response.read())


def verify(output, attempts=1, assets_only=False):
    release_version = read_json(output / 'build.json')['version']
    expected = read_json(output / 'manifest.json')
    revision, changed = svn_info(SVN_URL)
    listing_manifest_path = output / 'listing-assets-manifest.json'
    desired_assets = read_json(listing_manifest_path) if listing_manifest_path.is_file() else {}
    if desired_assets:
        published_assets = export(SVN_URL + '/assets', output / 'published-assets', revision)
        for name, digest in sorted(desired_assets.items()):
            require(published_assets.get(name) == digest, 'Published listing asset mismatch: ' + name)
    if assets_only:
        write_json(output / 'verification.json', {
            'version': release_version,
            'svn_revision': changed,
            'snapshot_revision': revision,
            'listing_assets': len(desired_assets),
            'status': 'verified_assets_only',
        })
        print(f'VERIFIED {len(desired_assets)} listing assets at SVN r{changed}')
        return
    for name, url in [('published-trunk', '/trunk'), ('published-tag', '/tags/' + release_version)]:
        compare(expected, export(SVN_URL + url, output / name, revision), name)
    error = None
    tested = ''
    for attempt in range(attempts):
        try:
            path = output / 'wordpress-org.zip'
            download(f'https://downloads.wordpress.org/plugin/{SLUG}.{release_version}.zip', path)
            compare(expected, zip_manifest(path), 'WordPress.org release ZIP')
            request = urllib.request.Request(
                'https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request%5Bslug%5D=' + SLUG,
                headers={'User-Agent': 'LoadMoreAnything-release-verification'})
            with urllib.request.urlopen(request, timeout=60) as response:
                info = json.load(response)
            require(info.get('version') == release_version, 'WordPress.org listing has not updated')
            tested = re.search(r'^Tested up to:\s*(\S+)',
                               (output / SLUG / 'README.txt').read_text(), re.M).group(1)
            require(str(info.get('tested', '')).split('.')[:2] == tested.split('.')[:2],
                    'WordPress.org Tested up to has not updated')
            error = None
            break
        except Exception as exc:
            error = str(exc)
            print(f'WordPress.org not verified ({attempt + 1}/{attempts}): {error}', flush=True)
            if attempt + 1 < attempts:
                time.sleep(30)
    require(error is None, 'Publication verification failed: ' + str(error))
    compare(expected, zip_manifest(output / (SLUG + '.zip')), 'GitHub package')
    write_json(output / 'verification.json', {'version': release_version, 'svn_revision': changed,
               'snapshot_revision': revision, 'files': len(expected), 'tested': tested, 'status': 'verified'})
    print(f'VERIFIED {SLUG} {release_version}: trunk, tag, ZIP, assets and listing; {len(expected)} files')


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('command', choices=['build', 'stage', 'publish', 'verify'])
    parser.add_argument('--source', type=Path, default=Path(__file__).resolve().parents[1])
    parser.add_argument('--output', type=Path, required=True)
    parser.add_argument('--tag')
    parser.add_argument('--attempts', type=int, default=1)
    parser.add_argument('--check-existing', action='store_true', help='Validation-only: compare an existing tag without staging')
    parser.add_argument('--allow-missing-svn', action='store_true', help='Validation-only: skip SVN staging before WP.org approves the plugin slug')
    parser.add_argument('--allow-untracked', action='store_true', help='Local dry-run only: package runtime files from working tree')
    parser.add_argument('--assets-only', action='store_true', help='Stage/publish/verify only .wordpress/ listing assets to SVN /assets')
    args = parser.parse_args()
    source, output = args.source.resolve(), args.output.resolve()
    try:
        if args.command == 'build':
            build(source, output, args.tag, allow_untracked=args.allow_untracked)
        elif args.command == 'stage':
            stage(source, output, check_existing=args.check_existing,
                  allow_missing_svn=args.allow_missing_svn, assets_only=args.assets_only)
        elif args.command == 'publish':
            publish(source, output, args.tag)
        else:
            verify(output, args.attempts, assets_only=args.assets_only)
    except Exception as exc:
        print('RELEASE ABORTED: ' + str(exc), file=sys.stderr)
        return 1
    return 0


if __name__ == '__main__':
    sys.exit(main())
