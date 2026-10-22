#!/usr/bin/env sh
set -eu
repo="$(git rev-parse --show-toplevel)"
cd "$repo"
patch="$(dirname "$0")/DIFF_FILE.patch"
git apply --reverse --ignore-whitespace --check "$patch"
git apply --reverse --ignore-whitespace "$patch"
printf '%s\n' 'Rollback applied. Tracked security-hardening changes reverted.'