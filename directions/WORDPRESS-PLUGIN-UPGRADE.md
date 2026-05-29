# WordPress Plugin Upgrade Runbook

This file records the 2026-05-29 plugin upgrade issue and the correct workflow for the next Med Platform Headless plugin release.

## Current Lesson

WordPress does not identify plugin upgrades only by the visible plugin name. It uses the plugin file path:

```text
wp-content/plugins/<plugin-folder>/<main-plugin-file>.php
```

If a ZIP upload creates a different `<plugin-folder>`, WordPress treats it as a separate plugin and shows `Activate` instead of `Replace current with uploaded`.

For this plugin, the main file is:

```text
medplatform-headless.php
```

The active folder name must be checked before every future package. Do not assume it from the repo folder.

## Symptoms We Saw

- Uploading the plugin showed `Activate` instead of the normal replace/update screen.
- The plugin list showed duplicate `Med Platform Headless` rows.
- Activating a duplicate produced:

```text
Plugin file does not exist.
```

- Hostinger File Manager showed the working plugin has a real folder with this shape:

```text
wp-content/plugins/<active-plugin-folder>/
  medplatform-headless.php
  assets/
  seed/
```

## Root Causes

- The ZIP name and top-level folder changed between packages.
- Version-suffixed ZIP/folder names like `medplatform-headless-clean-install-v131` made WordPress create a new plugin instead of replacing the old one.
- A Windows-created ZIP can contain backslash paths internally. The package script now uses `tar.exe` so entries use forward slashes.
- Some test uploads created inactive duplicate plugins. Those must not be activated.

## Source Files

Edit plugin source here:

```text
wordpress-plugin/medplatform-headless/
  medplatform-headless.php
  assets/admin-media.js
  seed/
```

Package script:

```powershell
powershell -ExecutionPolicy Bypass -File wordpress-plugin\package-plugin.ps1
```

The script creates two packages:

```text
wordpress-plugin/medplatform-headless-clean-install.zip
wordpress-plugin/medplatform-headless-clean-install-manual-overwrite.zip
```

## Before The Next Upgrade

1. Open WordPress Admin > Plugins.
2. Identify the active `Med Platform Headless` row.
3. Confirm its folder in Hostinger File Manager under:

```text
wp-content/plugins/
```

4. If the active folder is not `medplatform-headless-clean-install`, package with the active folder name:

```powershell
powershell -ExecutionPolicy Bypass -File wordpress-plugin\package-plugin.ps1 -LiveFolder "ACTIVE-FOLDER-HERE"
```

5. Bump the version in:

```text
wordpress-plugin/medplatform-headless/medplatform-headless.php
```

Update both:

```php
* Version: X.YY
'User-Agent' => 'Med-Platform-Headless/X.YY'
```

6. Validate locally:

```powershell
php -l wordpress-plugin\medplatform-headless\medplatform-headless.php
npm run build
```

## Preferred Upgrade Route

Use this only if WordPress shows the replace/update screen.

1. Run the package script.
2. Upload the stable folder package:

```text
wordpress-plugin/medplatform-headless-clean-install.zip
```

3. If WordPress says it will replace the current plugin, continue.
4. After replacement, confirm the plugin list shows the new version.
5. Do not activate duplicate inactive copies.

## Stop Condition

If WordPress shows `Activate`, stop.

Do not click `Activate`.

That means WordPress created or detected a separate plugin folder, not an upgrade.

## Manual Overwrite Route

Use this if the WordPress upload flow keeps showing `Activate`.

1. In Hostinger File Manager, open the active plugin folder:

```text
wp-content/plugins/<active-plugin-folder>/
```

2. Upload this package into that folder:

```text
wordpress-plugin/medplatform-headless-clean-install-manual-overwrite.zip
```

3. Extract it inside the active plugin folder.
4. Overwrite existing files.
5. The extracted folder should not create another nested plugin folder.
6. The active folder should still look like:

```text
wp-content/plugins/<active-plugin-folder>/
  medplatform-headless.php
  assets/
  seed/
```

7. Reload WordPress Admin > Plugins and confirm the version.

## Cleanup Rules

- Keep exactly one active `Med Platform Headless` plugin.
- Delete inactive duplicate `Med Platform Headless` rows after confirming they are not the active folder.
- Never delete the only active folder before confirming the replacement package works.
- If a bad upload created a broken plugin row, delete that inactive row instead of activating it.

## Git Workflow

After packaging and validation:

```powershell
git status --short --branch
git add wordpress-plugin/medplatform-headless wordpress-plugin/package-plugin.ps1
git add -f wordpress-plugin/*.zip
git commit -m "Release headless plugin X.YY"
git push origin main
```

Only commit ZIPs that are intentionally release packages.

## 2026-05-29 Resolution

The working resolution was to delete the broken duplicate plugin entries and install version `1.31` as the clean active plugin. For the next upgrade, first confirm the active folder created by that clean install, then package using that exact folder name.
