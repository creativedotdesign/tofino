# Code quality

Use the Node version in `.nvmrc` (22.22.0) or a supported Node 24+ version,
and PHP 8.3 or newer. Install development dependencies with `composer install`
and `npm ci` before running checks.

## Commands

| Command | Purpose |
| --- | --- |
| `npm run check` | Run all static checks and isolated unit tests without changing source. |
| `npm run typecheck` | Check production Vue/TypeScript and build configuration with vue-tsc. |
| `npm run lint:frontend` | Check production Vue/TypeScript, tooling, and existing test helpers with ESLint. |
| `npm run lint:frontend:fix` | Apply safe ESLint fixes; review the diff afterwards. |
| `npm run format:check` | Check frontend, stylesheet, manifest, and configuration formatting. |
| `npm run format` | Apply Prettier formatting. |
| `npm run test:unit` | Verify admin autosize lifecycle behavior without WordPress or a browser. |
| `npm run check:php` | Check PHP syntax, PHPStan level 6, and PHPCS. |
| `composer analyse:php` | Run PHPStan for PHP 8.3 and WordPress APIs. |
| `composer lint:php` | Check the PHP readability rules in `phpcs.xml`. |
| `composer format:php` | Apply PHPCBF fixes; review the diff afterwards. |
| `npm run build` | Build frontend assets after the checks pass. |
| `npm run check:release` | Build without local environment files and verify the production ZIP. |
| `npm run package` | Create and verify `dist-release/tofino.zip` from the current build. |

Run `npm run lint:frontend:fix` before `npm run format` so Prettier can format
newly expanded blocks. Re-run `npm run check` after fixing source. Browser QA
remains a separate step; see [testing.md](testing.md).

## Readability rules

PHP uses two-space indentation, braced control flow, one statement per line,
and DocBlocks describing named functions and classes. Describe array values
and optional integration contracts precisely enough for static analysis.
Keep public hooks and child-theme APIs compatible when improving annotations.

Vue and TypeScript retain the existing Vue accessibility rules and add typed
promise checks, braced control flow, readable statement spacing, and purposeful
JSDoc for named production implementations. Prettier owns layout; ESLint owns
correctness and structural readability. Short callbacks do not need comments
that simply repeat their code. Tests retain their recommended ESLint checks.

PHPStan scans WordPress/ACF signatures and `stubs/integrations.stub` without
booting WordPress. The latter describes the optional WPGraphQL and Yoast APIs
used by the theme; keep those signatures aligned when their integrations change.
`stubs/wordpress.stub` corrects the generated `wp_nav_menu` container argument
annotation. These files are never included at runtime. There is no PHPStan
baseline or blanket error suppression.

## CI and releases

Both development and release workflows run `npm run check` before the build.
The package command stages a separate `composer install --no-dev`, verifies the
runtime autoloader and required dependencies, and checks that the archive omits
analysis tools, signatures, caches, source, tests, and development configuration.
It preserves the local development dependency installation.

Dependency security auditing remains separate from this gate. A passing static
check does not imply a clean dependency audit or completed browser QA.

Release CI uses `npm run check:release`. It builds into a temporary directory with
no local `.env` and no `VITE_THEME_PATH`, verifies portable relative URLs and
manifest assets, then packages that exact build. Local `dist/` and hot files are
preserved. Set `VITE_THEME_PATH` for a deployment-specific base in ordinary builds;
otherwise production URLs stay relative to their built modules.
