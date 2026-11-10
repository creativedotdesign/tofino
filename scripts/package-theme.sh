#!/usr/bin/env bash
set -euo pipefail

# Stage a production install so packaging never removes local analysis tools.
THEME_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$THEME_DIR"
BUILD_DIR="${TOFINO_BUILD_DIR:-$THEME_DIR/dist}"
ARCHIVE_DIR="${TOFINO_ARCHIVE_DIR:-$THEME_DIR/dist-release}"
test -f "$BUILD_DIR/.vite/manifest.json"

STAGE_DIR="$(mktemp -d)"
trap 'rm -rf "$STAGE_DIR"' EXIT
mkdir -p "$STAGE_DIR/tofino/dist" "$ARCHIVE_DIR"
rsync -a --exclude-from=.distignore --exclude='/vendor/' --exclude='/dist/' ./ "$STAGE_DIR/tofino/"
rsync -a --exclude='hot' --exclude='hot.local' "$BUILD_DIR/" "$STAGE_DIR/tofino/dist/"
cp composer.json composer.lock "$STAGE_DIR/tofino/"
composer install --working-dir="$STAGE_DIR/tofino" --no-dev --prefer-dist \
  --optimize-autoloader --no-interaction
rm "$STAGE_DIR/tofino/composer.json" "$STAGE_DIR/tofino/composer.lock"

ARCHIVE="$ARCHIVE_DIR/tofino.zip"
rm -f "$ARCHIVE"
(cd "$STAGE_DIR" && zip -rq "$ARCHIVE" tofino)
unzip -tq "$ARCHIVE"

# Keep development dependencies, signatures, and configuration out of releases.
ARCHIVE_LIST="$(unzip -Z1 "$ARCHIVE")"
ROOT_DEV_PATHS='^tofino/(node_modules|stubs|scripts|tests|src|\.cache|\.playwright-cli|\.git|\.github|build|dist-release)(/|$)'
DEV_CONFIG='^tofino/(eslint\.config\.js|vitest\.config\.ts|phpcs\.xml|phpstan\.neon\.dist|\.prettierignore|\.prettierrc|\.nvmrc|package(-lock)?\.json|composer\.(json|lock))$'
DEV_VENDOR='^tofino/vendor/(phpstan|squizlabs|szepeviktor|php-stubs)/'
FRONTEND_SOURCE='^tofino/(features|modules)/.*\.(ts|vue)$'

if grep -Eq "$ROOT_DEV_PATHS|$DEV_CONFIG|$DEV_VENDOR|$FRONTEND_SOURCE" <<<"$ARCHIVE_LIST"; then
  echo 'Development files found in the release archive.' >&2
  exit 1
fi

mkdir "$STAGE_DIR/verified"
unzip -q "$ARCHIVE" -d "$STAGE_DIR/verified"

# Check every optimized autoload entry against the extracted release files.
php -r '
  $loader = require $argv[1];
  foreach ($loader->getClassMap() as $class => $file) {
    if (!is_file($file)) {
      fwrite(STDERR, "Missing autoload file for {$class}: {$file}\n");
      exit(1);
    }
  }
  foreach (["enshrined\\svgSanitize\\Sanitizer", "League\\HTMLToMarkdown\\HtmlConverter"] as $class) {
    if (!class_exists($class)) {
      fwrite(STDERR, "Missing runtime dependency: {$class}\n");
      exit(1);
    }
  }
' "$STAGE_DIR/verified/tofino/vendor/autoload.php"

grep -R -q --include='*.css' 'body.tofino-hide-admin-notices' "$STAGE_DIR/verified/tofino/dist/assets"
echo "Verified production archive: $ARCHIVE"
