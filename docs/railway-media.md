# Railway media deployment

## Repository findings

Uploads use `Storage::disk('public')` or `store(..., 'public')`. Paths are relative:
`flyer_images.image`, `webinars.poster_image`, `videos.thumbnail`, `videos.video_path`,
`videos.interactive_path`, and `articles.cover_image`. Flyer images live in the
related table, not the Flyer row. Uploaded interactive packages also use this disk;
the built-in interactive package lives in `public/interactive-video`.

The public disk root is `storage/app/public`. Its URL is `APP_URL/storage`.
Blade uses both `asset('storage/...')` and public-disk `url()`, so both require
the public storage link and actual files. The local junction points correctly to
the local public disk. `FILESYSTEM_DISK=local` is not the cause: uploads explicitly
select `public`. Keep production `APP_URL` set to the HTTPS public origin.

Git ignores `storage/app/public` contents and `public/storage`, as expected.
It does not ignore `database/seeders/assets`: 218 assets were tracked at inspection,
including all 217 distinct local paths referenced by exported JSON. All 217 also
existed in local storage. The remaining asset is `demo-video.webm`.
Current Article covers, Webinar posters and Video thumbnails/video paths in the JSON are null.
These exports cannot recover newer media that was never included in the bundle.

All four content seeders already copy exported assets to the public disk, but only
when seeding. They also update database content; missing bundled assets can result
in null paths (or skipped Flyer images). Do not rerun them as a media repair.
The exporter now fails on missing source files instead of silently exporting null.
The JSON for the failing content type is not replaced, though earlier content
types in a full export may already have completed. Use `--only=webinars` to export
only Webinar records and their posters. Uploaded interactive packages are not
exported. Do not run it against an incomplete production filesystem.

The repository has a media restoration command and `scripts/railway-media-start.sh`,
but no Railway, Docker, Nixpacks, or Procfile configuration connecting that wrapper
to the deployed service. The actual Railway Start Command and volume attachment
must be checked in the dashboard. The original wrapper also restored files without
requiring persistent storage or verifying the final link target; it could succeed
while writing to ephemeral storage. The revised wrapper prevents that failure.
The precise live failure (missing file, link, volume, or incorrect APP_URL) still
must be checked in the running container.
No live Railway credentials, settings, container, or database were available during
this investigation.

## Before the next deployment

1. In the **currently running Railway application container**, inspect storage.
   The first command below requires this patch to be installed; if the current
   deployment predates it, inspect the broken URLs' relative paths directly with
   `ls -l storage/app/public/RELATIVE_PATH` and back up files before redeploying.

   ```sh
   php artisan content:restore-media --check
   ls -ld public/storage storage/app/public
   readlink -f public/storage
   printenv RAILWAY_VOLUME_MOUNT_PATH
   cat /proc/self/mountinfo
   ```

   `--check` reads current database paths, reports each missing local file, and
   exits nonzero if any are missing. It skips null paths and external HTTP URLs.
   It does not change files or database rows, test HTTP access, or check every
   dependency inside an interactive package.

   Run these commands via Railway SSH into the **application service**, not the
   database service. `railway run` executes locally with Railway variables and
   does not inspect production files. Inspect `php artisan config:show filesystems`
   there if root/URL configuration is uncertain; do not share credentials from
   other disk configurations.

2. Back up any surviving production uploads **before** changing the mount or
   redeploying. Copy them off the current container, preserving relative paths.
   An empty volume hides files previously present at its mount location. Keep
   complete interactive package directories, not just their entry HTML files.
   Missing files absent from both Git and backups must be recovered from the
   original uploads; database paths alone cannot reconstruct them.

3. Attach a persistent Railway volume to the application service at
   `/app/storage/app/public`. Confirm the application directory is `/app` in the
   deployment; if different, use that absolute directory plus `/storage/app/public`.
   Mount only the public-media directory, not all of `/app` or `/app/storage`.
   Railway supplies `RAILWAY_VOLUME_MOUNT_PATH` automatically. Do not set it by
   hand to imitate a volume: startup checks Linux mount metadata too.
   Restore the backed-up production uploads into that volume before startup
   restoration where possible, so originals take precedence over bundled copies.

4. Set Railway `APP_URL=https://YOUR-PUBLIC-DOMAIN` (no `/public` suffix).
   Ensure the application process can write the mounted directory. If config is
   cached with old environment values, run `php artisan config:clear`, then rebuild
   the cache using the correct production environment if your deployment uses it.

## Deployment commands

Keep the existing build and server configuration. In Railway Settings -> Deploy,
use this pre-deploy command for migrations:

```sh
php artisan migrate --force
```

Do not add `--seed`, `migrate:fresh`, or `db:seed` to repair existing media.
Do not restore media or create its link in pre-deploy: that container has no volume
and its filesystem changes do not persist into the application container.

Wrap the existing **Start Command** with:

```sh
sh scripts/railway-media-start.sh YOUR_EXISTING_SERVER_COMMAND_AND_ARGUMENTS
```

`YOUR_EXISTING_SERVER_COMMAND_AND_ARGUMENTS` must be replaced with the exact current
server command. For example, **only if your existing command is**
`php artisan serve --host=0.0.0.0 --port=$PORT`, the complete replacement is:

```sh
sh scripts/railway-media-start.sh php artisan serve --host=0.0.0.0 --port=$PORT
```

For a server command containing shell operators, pass it as `sh -c '...'` after the
wrapper. Keep your existing production web server rather than switching servers
for this fix. The wrapper restores bundled files after volume mounting, creates
the storage link, then executes your server. Restoration streams files, checks
write failures, skips existing files, and never accesses the database.

The wrapper uses `php artisan content:restore-media --prepare --no-interaction`.
It stops startup if the configured public disk is not inside the actual Railway
volume, is not writable, or `public/storage` resolves to a different location.
Missing links are created; conflicting paths are preserved and reported.
The legacy bundle `demo-video.webm` is also restored to the path used by VideoSeeder,
`videos/demo-uploaded-file.webm`, without replacing an existing file.

For local restoration, use `php artisan content:restore-media` and the existing
local storage link. `--prepare` is Railway-specific and requires Linux mount data.
`--check` is strictly read-only and cannot be combined with `--prepare`.

If `storage:link` reports an existing path, inspect it. A correct existing symlink
is fine; a real directory or stale link needs investigation before replacement.
Do not delete a real directory that might contain uploads.

## Verify a fresh redeploy

1. In the running application container, run `php artisan content:restore-media
   --check`. Resolve every missing path from the bundle, surviving local files,
   or backups. An empty set of references is not proof that expected images exist.
2. Open Flyer galleries, Webinar posters, Video thumbnails/uploaded playback, and
   Article covers. In browser Network, verify their actual media requests return
   200 (or 206 for video), the expected content type, and the correct HTTPS host.
   A filesystem check alone cannot verify the server follows the link.
3. Upload a new image through the existing admin interface and record its relative
   path. This tests a file that is **not** present in the committed bundle.
4. Record SHA-256 hashes for representative files, including that new upload:
   `sha256sum storage/app/public/RELATIVE_PATH`. Save the output off-container.
5. Redeploy the same commit with the same volume attached. Repeat the database
   check, compare hashes, and verify the same URLs and pages. The new upload must
   survive; successful recovery of bundled files alone does not prove persistence.
6. Enable volume backups for uploads created after the exported snapshot.

Local verification: `php artisan test --filter=RestoreContentMediaTest` uses fake
storage and mocked database reads. It checks a fresh restore, byte equality,
repeatability, preservation of existing/new files, missing-media diagnostics,
volume validation, Linux mount metadata parsing, and conflicting link preservation.
Seven tests passed (480 assertions). The read-only local database check found
224 local media references with zero missing files. These tests do not prove
that a real Railway volume is attached or that its web server serves the symlink;
complete the production redeploy procedure above.

## Commit and deploy

After reviewing the changes, run from the repository root:

```sh
git diff --check
git add app/Console/Commands/RestoreContentMedia.php scripts/railway-media-start.sh tests/Feature/RestoreContentMediaTest.php docs/railway-media.md
git diff --cached --stat
git commit -m "Verify persistent Railway media storage before startup"
git push
```

If the changes were already committed, skip the commit step. This fix does not
change database exports or upload files. Existing `database/seeders/assets` are
already tracked; do not force-add ignored storage directories or `.env`.
Complete the backup and volume configuration before triggering a new deployment.

Railway references:
- https://docs.railway.com/volumes
- https://docs.railway.com/deployments/pre-deploy-command

## Follow-up: Flyer serving and original Webinar posters

Read-only inspection of the local database found two Webinar records (IDs 1 and 2),
both with `poster_image = null`. Neither `storage/app/public/webinars` nor
`database/seeders/assets/webinars` exists. The original Git version of
`webinars.json` also has null posters, and no Webinar assets were found in Git
history. No original posters were available to export; do not invent paths or
reuse unrelated images. Recover the original posters and associate them with
the correct Webinar through the existing admin upload form, then run:

```sh
php artisan content:export-seeders --only=webinars
php artisan test --filter='ExportContentSeedersTest|RestoreContentMediaTest'
git diff -- database/seeders/data/webinars.json
git status --short database/seeders/assets/webinars
```

Review that each recovered poster has a relative `webinars/...` path and a
corresponding file under `database/seeders/assets/webinars`. Null remains valid
for records that truly have no poster. Export does not modify database rows.
If production Webinar rows also have null paths, deploying files alone cannot
associate them: upload the original posters to those records through production
admin as well. `WebinarSeeder` updates other record fields too; do not run it
blindly against edited production content.

Railway CLI inspection returned `Unauthorized. Please login with railway login`.
Consequently neither execution of `db:seed`/`storage:link` nor the live files or
symlink could be verified. The wrapper does not invoke `db:seed`; it calls
`content:restore-media --prepare`, which calls `storage:link` only when the link
is missing. Merely committing the wrapper does not configure Railway to run it.
After `railway.cmd login` and `railway.cmd link`, use `railway.cmd ssh` to enter
the application container and run:

```sh
php artisan content:restore-media --check
ls -l storage/app/public/flyers/01LmGdT8IR1GnC4yMcTbfbImfCyiHq4Vf5K5sv0Y.png
ls -ld public/storage
readlink -f public/storage
readlink -f storage/app/public
cmp storage/app/public/flyers/01LmGdT8IR1GnC4yMcTbfbImfCyiHq4Vf5K5sv0Y.png public/storage/flyers/01LmGdT8IR1GnC4yMcTbfbImfCyiHq4Vf5K5sv0Y.png
```

The two `readlink` outputs must match; `ls` must show an actual file and `cmp`
must succeed. Check the deployed Start Command and logs for `Verified mounted
public-media volume and public/storage target.` and `Restored ... files`.
Finally request `/storage/flyers/01LmGdT8IR1GnC4yMcTbfbImfCyiHq4Vf5K5sv0Y.png`
on the public HTTPS domain and verify HTTP 200 with an image content type.
