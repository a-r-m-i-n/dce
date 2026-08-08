# Repository Instructions

## DDEV Is Mandatory

- Run all project tooling through DDEV. Never run Composer, PHP, TYPO3 CLI, NPM, MySQL, or related binaries directly on the host.
- Start the environment with `ddev start`; execute container commands as `ddev exec -d <container-path> <command>`.
- The repository is mounted at `/var/www/dce`. Run extension-level Composer commands and quality checks there.
- The TYPO3 installation is a separate Composer project in a persistent volume at `/var/www/html/v14`; it is not a subdirectory of the host checkout.
- Use the project directory for runtime commands, for example `ddev exec -d /var/www/html/v14 vendor/bin/typo3 cache:flush`.

## Setup And Verification

- `ddev install-v14` recreates installation files. Do not run it merely to update dependencies or clear caches.
- After changing extension code, install the quality-tool dependencies with `ddev exec -d /var/www/dce composer install`, then run `ddev exec -d /var/www/dce composer check`. The check runs EditorConfig validation, PHP CS Fixer in dry-run mode, PHPStan level 5, TypoScript lint, and strict Composer validation in that order.
- Focused checks are Composer scripts in `/var/www/dce`: `composer editorconfig-check`, `composer phpcs`, `composer phpstan`, and `composer typoscript-lint`. `composer fix` modifies files before running PHPStan and TypoScript lint; review the resulting diff even if the command fails.
- No PHPUnit test suite is configured. For TYPO3-sensitive changes, run the relevant focused checks and verify behavior at `https://v14.dce.ddev.site`.
- Render documentation with `ddev docs`; `ddev launch-docs` only opens the already-rendered output.

## Compatibility And Runtime

- The active Composer target is PHP 8.4 with TYPO3 `^14.3`.
- DCE plugin configuration and TCA are generated and loaded from cache by `Classes/Components/ContentElementGenerator`. After changing registration, TCA, or generator behavior, flush caches in the v14 installation before evaluating the result.
- PHP classes use the `T3\Dce\` namespace mapped to `Classes/`; TYPO3 service wiring is in `Configuration/Services.yaml`, while hooks and plugin bootstrap remain in `ext_localconf.php`.
