#!/usr/bin/env bash
set -euo pipefail

repo="$(git rev-parse --show-toplevel)"
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
target="${1:-docs/audits/android-referral-20261021/rollback-copy/NativeCheckoutOrderService.php}"
mkdir -p "$(dirname "$repo/$target")"
cp "$script_dir/baseline/NativeCheckoutOrderService.php" "$repo/$target"
php -l "$repo/$target"
baseline="$(git -C "$repo" hash-object "$script_dir/baseline/NativeCheckoutOrderService.php")"
restored="$(git -C "$repo" hash-object "$target")"
test "$baseline" = "$restored"
printf 'RESTORED_COPY_OK baseline=%s restored=%s\n' "$baseline" "$restored"
