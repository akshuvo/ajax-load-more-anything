#!/usr/bin/env python3
"""Isolated disposable WordPress smoke test for the packaged Load More Anything ZIP."""
import hashlib
import os
from pathlib import Path
import random
import secrets
import subprocess
import sys
import time
import urllib.request


WP_CLI_URL = 'https://raw.githubusercontent.com/wp-cli/builds/ad68e4ad3b29c3d3bdb361e5ed9b37e709123a9b/phar/wp-cli.phar'
WP_CLI_SHA256 = 'ce34ddd838f7351d6759068d09793f26755463b4a4610a5a5c0a97b68220d85c'


def run_cmd(args, check=True):
    subprocess.run(args, check=check)


def main():
    if len(sys.argv) != 3:
        sys.exit('Usage: smoke_test.py <path-to-zip> <temp-test-dir>')

    archive = Path(sys.argv[1]).resolve()
    test_dir = Path(sys.argv[2]).resolve()
    test_dir.mkdir(parents=True, exist_ok=True)

    phar_path = test_dir / 'wp-cli.phar'
    urllib.request.urlretrieve(WP_CLI_URL, phar_path)
    actual_hash = hashlib.sha256(phar_path.read_bytes()).hexdigest()
    if actual_hash != WP_CLI_SHA256:
        sys.exit(f'WP-CLI SHA-256 mismatch: {actual_hash}')

    site_dir = test_dir / 'site'
    wp_cli = ['php', '-d', 'memory_limit=512M', str(phar_path), f'--path={site_dir}']
    slug = os.environ.get('WPORG_PLUGIN_SLUG', 'ajax-load-more-anything')
    db_name = f'alma_release_{int(time.time())}_{random.randint(1000, 9999)}'

    run_cmd(wp_cli + ['core', 'download', '--version=7.1.4'])
    run_cmd(wp_cli + [
        'config', 'create',
        f'--dbname={db_name}',
        f"--dbuser={os.environ['WP_DB_USER']}",
        f"--dbpass={os.environ.get('WP_DB_PASSWORD', '')}",
        f"--dbhost={os.environ['WP_DB_HOST']}",
        '--skip-check',
    ])
    run_cmd(wp_cli + ['db', 'create'])
    try:
        run_cmd(wp_cli + [
            'core', 'install',
            '--url=http://alma-release.test',
            '--title=Load More Anything Release Test',
            '--admin_user=release_test',
            f'--admin_password={secrets.token_hex(20)}',
            '--admin_email=release@example.invalid',
            '--skip-email',
        ])
        run_cmd(wp_cli + ['plugin', 'install', str(archive), '--activate'])
        smoke_script = Path(__file__).resolve().parents[1] / 'tests' / 'smoke.php'
        run_cmd(wp_cli + ['eval-file', str(smoke_script)])
        run_cmd(wp_cli + ['plugin', 'deactivate', slug])
        run_cmd(wp_cli + ['plugin', 'activate', slug])
        run_cmd(wp_cli + ['plugin', 'is-active', slug])
    finally:
        run_cmd(wp_cli + ['db', 'drop', '--yes'], check=False)


if __name__ == '__main__':
    main()
