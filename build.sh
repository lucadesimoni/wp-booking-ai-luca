#!/usr/bin/env bash
#
# Build a distributable WordPress plugin ZIP.
#
# Produces dist/wp-booking-luca.zip containing only the runtime files, laid out
# inside a `wp-booking-luca/` folder so it installs cleanly via
# Plugins → Add New → Upload Plugin.
#
# Usage: ./build.sh

set -euo pipefail

SLUG="wp-booking-luca"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DIST="${ROOT}/dist"
STAGE="${DIST}/${SLUG}"

# Files/folders that make up the shippable plugin.
INCLUDE=(
	"wp-booking-system.php"
	"uninstall.php"
	"index.php"
	"readme.txt"
	"changelog.txt"
	"LICENSE"
	"includes"
	"assets"
)

echo "Cleaning previous build..."
rm -rf "${STAGE}" "${DIST}/${SLUG}.zip"
mkdir -p "${STAGE}"

echo "Staging plugin files..."
for item in "${INCLUDE[@]}"; do
	if [ -e "${ROOT}/${item}" ]; then
		cp -R "${ROOT}/${item}" "${STAGE}/"
	fi
done

# Strip any stray VCS/OS cruft from the staged copy.
find "${STAGE}" -name '.DS_Store' -delete 2>/dev/null || true
find "${STAGE}" -name '*.map' -delete 2>/dev/null || true

echo "Creating ZIP..."
( cd "${DIST}" && zip -rq "${SLUG}.zip" "${SLUG}" )

echo "Cleaning staging directory..."
rm -rf "${STAGE}"

echo "Done: dist/${SLUG}.zip"
