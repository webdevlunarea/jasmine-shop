#!/usr/bin/env sh
set -eu
BASELINE=0260b558678a26f5393a34077cdaae51950dcff0
TARGET=${1:-public/css/storefront-catalog.css}
git show "${BASELINE}:public/css/storefront-catalog.css" > "${TARGET}"
printf '%s\\n' "RESTORED: ${TARGET} from ${BASELINE}"