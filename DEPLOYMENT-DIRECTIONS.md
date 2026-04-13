# Med Platform Runbook

This file is the practical operating guide for this project.

It explains:

- how the live frontend gets updated
- how WordPress content changes trigger redeploys
- how plugin deployment differs from frontend deployment
- what to do when something does not show up live

## Quick Start

Use these rules day to day:

- frontend code change: commit and push to `main`
- CMS content change: let WordPress create a deploy-trigger commit, or use manual push update
- live plugin PHP change: verify the actual Hostinger plugin file, not just the repo
- no new GitHub commit means no new Hostinger redeploy
- the GitHub traffic workflow is separate and does not control the live site

## Environment

Current known URLs:

- frontend: `https://mediplatform.org/`
- CMS: `https://wheat-octopus-390597.hostingersite.com/`

Current repo and branch:

- repo path: `e:\Web_Dev\Med`
- branch: `main`

Current local `.env` expectation:

```env
WORDPRESS_API_URL=https://wheat-octopus-390597.hostingersite.com/wp-json
```

The frontend reads WordPress content from the configured API URL. Relevant code:

- [src/lib/site-data.ts](/e:/Web_Dev/Med/src/lib/site-data.ts:6)
- [src/components/ui/Form.astro](/e:/Web_Dev/Med/src/components/ui/Form.astro:12)

## Architecture

This project is headless:

- Astro is the frontend
- WordPress is the CMS
- GitHub is the deployment source of truth for the frontend
- Hostinger rebuilds the frontend from the configured GitHub branch

There are two separate live-update concerns:

1. frontend deployment
2. WordPress plugin deployment

Do not treat them as the same thing.

## Daily Workflows

### Frontend Code Release

Use this when you changed Astro, styles, config, build logic, or any repo-tracked frontend code.

Steps:

1. make your code changes locally
2. verify locally
3. commit to `main`
4. push to `origin/main`
5. wait for Hostinger to redeploy
6. confirm the live frontend changed

Typical local verification:

```bash
npm run build
```

Useful local commands:

```bash
npm run dev
npm run dev:local-cms
npm run dev:headless
npm run build
npm run check
npm run typecheck
```

### CMS Content Release

Use this when content changed in WordPress and the frontend needs to rebuild against fresh CMS data.

Flow:

1. content changes are made in WordPress
2. the headless plugin triggers a build event
3. the plugin updates `.hostinger/deploy-trigger.json` in GitHub
4. GitHub receives a new commit on the configured branch
5. Hostinger redeploys the frontend
6. the rebuilt frontend reads the latest WordPress content

The plugin commit is small on purpose. It usually does not commit the whole frontend codebase.

Relevant code:

- [wordpress-plugin/medplatform-headless/medplatform-headless.php](/e:/Web_Dev/Med/wordpress-plugin/medplatform-headless/medplatform-headless.php:6181)
- [.hostinger/deploy-trigger.json](/e:/Web_Dev/Med/.hostinger/deploy-trigger.json:1)

Typical commit message:

- `Trigger Hostinger deploy: manual_push_update`

### Manual CMS Redeploy

Use this when WordPress content is already correct but you want to force a fresh frontend redeploy.

In WordPress:

- go to `Settings -> Med Platform Headless`
- use the manual push action

Relevant handler:

- [wordpress-plugin/medplatform-headless/medplatform-headless.php](/e:/Web_Dev/Med/wordpress-plugin/medplatform-headless/medplatform-headless.php:4977)

This calls:

```php
mp_headless_trigger_build('manual_push_update', true)
```

## WordPress Auto Deploy Settings

The WordPress plugin exposes GitHub auto-deploy settings here:

- `Settings -> Med Platform Headless`

Relevant code:

- [wordpress-plugin/medplatform-headless/medplatform-headless.php](/e:/Web_Dev/Med/wordpress-plugin/medplatform-headless/medplatform-headless.php:5357)

Important settings:

- GitHub repo owner
- GitHub repo name
- GitHub branch
- GitHub token
- trigger file path
- automatic GitHub pushes enabled

Expected values for this project:

- owner: `sufiancool-jpg`
- repo: `Med`
- branch: `main`
- trigger path: `.hostinger/deploy-trigger.json`

Token expectation:

- fine-grained GitHub PAT
- contents read/write access to this repository

## Plugin Deployment

Plugin deployment is separate from frontend deployment.

Tracked plugin source in this repo:

- [wordpress-plugin/medplatform-headless/medplatform-headless.php](/e:/Web_Dev/Med/wordpress-plugin/medplatform-headless/medplatform-headless.php:1)

Important rule:

- pushing plugin code to GitHub does not guarantee the live WordPress plugin file changed on Hostinger

### Known Hostinger Plugin Issue

Prior handoff notes confirmed that ZIP upload attempts did not reliably replace the live plugin main file.

Observed symptoms:

- WordPress still showed plugin version `0.1.13`
- the live main plugin file stayed unchanged
- the plugin could become confused or temporarily deactivated

Operational meaning:

- frontend redeploys can succeed while the live WordPress plugin remains stale
- if a live CMS feature is missing, verify the actual server file before assuming Git or deploy is wrong

### Safe Plugin Update Rule

After any live plugin update attempt:

1. verify the plugin version shown in WordPress
2. verify the actual contents of `wp-content/plugins/medplatform-headless/medplatform-headless.php`
3. verify the expected field or behavior exists in wp-admin

### Fallback Plugin Recovery

If Hostinger upload/update fails again:

1. open `wp-content/plugins/medplatform-headless/medplatform-headless.php` in Hostinger
2. replace it with the intended plugin file contents
3. save
4. refresh the Plugins page in WordPress
5. confirm the version changed
6. confirm the target behavior appears

This is a host-side plugin replacement issue, not a GitHub redeploy issue.

## Local Development

For local headless development:

```bash
npm install
npm run wp:setup
npm run dev:headless
```

Local WordPress admin:

- URL: `http://127.0.0.1:8081/wp-admin`
- username: `admin`
- password: `admin12345!`

### Windows Caveat

The handoff notes indicate:

- PHP was installed
- MariaDB was installed
- WP-CLI was installed
- the local WordPress workflow was only partially hardened on Windows

Practical meaning:

- Astro frontend work is usable locally
- `npm run build` is a reliable baseline check
- local WordPress setup may still need environment-specific fixes
- a broken local `wp:setup` does not automatically mean the live deployment pipeline is broken

## Troubleshooting

### CMS Change Did Not Show Up Live

Check:

1. WordPress GitHub auto-deploy settings are configured correctly
2. automatic GitHub pushes are enabled, or manual push update was used
3. the GitHub token is still valid and writable
4. a new commit appeared on `main`
5. Hostinger started and completed a redeploy

If no GitHub commit appears after a CMS update, the likely failure is the plugin GitHub push step.

### Trigger Commit Exists But Site Did Not Update

Check:

1. Hostinger is connected to the correct repo and branch
2. Hostinger build logs show a successful redeploy
3. the frontend still points to the correct `WORDPRESS_API_URL`
4. the CMS content is actually published and available through the API

### Repo Has Plugin Code But Live CMS Does Not

Check:

1. the live plugin version in WordPress
2. the contents of `wp-content/plugins/medplatform-headless/medplatform-headless.php`
3. whether the live plugin file was ever truly replaced
4. whether the change was pushed to GitHub but never properly applied on Hostinger

A frontend redeploy cannot fix a stale live plugin PHP file.

### Local Push Did Not Update Live Site

Check:

1. the push actually reached `origin/main`
2. Hostinger is tracking `main`
3. the Hostinger build completed successfully
4. no environment or API issue broke the build

## GitHub Traffic Workflow

There is one separate GitHub Actions workflow named:

- `Save Repo Traffic`

Files:

- [.github/workflows/traffic.yml](/e:/Web_Dev/Med/.github/workflows/traffic.yml:1)
- [.github/save_traffic.js](/e:/Web_Dev/Med/.github/save_traffic.js:1)

Purpose:

- fetch GitHub repository traffic clone data
- write `.github/data/clones.json`

Important:

- it is unrelated to Hostinger deployment
- it does not control the live frontend
- it does not power visible frontend data in this repo

Schedule:

- `0 0 * * 0`
- every Sunday at `00:00 UTC`

Recent hardening that was applied:

- `actions/checkout` updated to `v4`
- `actions/setup-node` updated to `v4`
- explicit branch checkout added
- explicit push ref added
- clearer `GRAPH_TOKEN` error reporting added

### If Traffic Workflow Fails

Check:

1. `GRAPH_TOKEN` exists in GitHub Actions secrets
2. the token still has the required access
3. the workflow log shows the exact API error

This workflow is non-critical for normal frontend deployment.

## Operational Artifacts

These local paths may exist as recovery or debug artifacts and should be treated carefully:

- `medplatform-headless/`
- `medplatform-headless-live-backup/`
- `.astro-dev.log`
- `.astro-dev.err.log`

Do not assume they are intended product changes. Check `git status --short` first.

## Pre-Flight Checklist

Before making deploy-related decisions:

1. run `git status --short`
2. run `git log --oneline -10`
3. confirm whether the issue is frontend code, CMS content, or live plugin PHP
4. confirm whether a GitHub commit was created
5. confirm whether Hostinger actually redeployed

## Short Summary

Remember this model:

- GitHub branch updates drive frontend redeploys
- WordPress content changes trigger tiny GitHub commits to force redeploys
- Hostinger rebuilds the frontend from GitHub
- the live plugin file on Hostinger may still need separate verification
- the traffic workflow is unrelated to the deployment path
