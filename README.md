# Halveron

Website for Halveron, a fictional independent asset manager in St James's, London, on the
agency WordPress platform (Cloud Run). WordPress core, wp-config, mu-plugins (yacf, GCS
uploads) and the APCu object cache live in the `wp-base` image; this repo owns only what
is unique to the client.

## What you own

| Path | Purpose |
| --- | --- |
| `static/` | Verified static HTML/CSS prototype; the source the theme is ported from |
| `wp-content/themes/` | The `halveron` theme |
| `wp-content/plugins/` | Client plugins (rarely needed) |
| `wp-content/yacf/` | Content model: post types, taxonomies, fields, options pages (YAML) |
| `wp-content/seed/` | `seed.php` (idempotent content seed) + its images. `run.php` is the platform's bootstrap runner, never edit it |
| `Dockerfile` | Three steps: copy `wp-content`, bundle the theme CSS, `wp-build`. Bump the `wp-base` tag to take platform updates |
| `firebase.json` | Firebase Hosting rewrite for `halveron.junaid.guru` (site `halveron-junaid`) |

## Static prototype

`static/` is the verified HTML source of the theme. Every page, string and image there is
final; the WordPress theme and seed reproduce it 1:1. Serve it with:

```bash
python3 -m http.server 8770 --directory static
```

Then open http://localhost:8770.

## Local development

Open in the devcontainer. It builds the image, starts MariaDB, installs WordPress
(admin / password), builds the yacf model and runs `wp-content/seed/run.php`.
Site: http://localhost:8080

The devcontainer mounts your `wp-content` dirs into the running image and enables
OPcache timestamp validation, so edits apply on refresh. Only one client devcontainer
can run at a time on this host (all map to host ports 8080/3307); stop one before
starting another.

If a container comes up "healthy" but WordPress cannot reach the database ("Error
establishing a database connection"), it likely lost its network attachment (a known
Docker Desktop flake after repeated `down`/`up` cycles): `docker inspect <container>
--format '{{len .NetworkSettings.Networks}}'` shows `0`. Fix:
`docker network connect --alias db <project>_default <db-container>` then
`docker network connect <project>_default <app-container>`.

## Onboarding checklist

1. firebase-cloud, branch `feat/halveron`: `terraform/apps/halveron.tf`, the catalog entry in
   `apps/mysql-catalog.tf` and the outputs in `apps/outputs.tf` and the root `outputs.tf`.
   Run `terraform apply` (the first apply is expected to fail creating the Cloud Run
   service because the DB secret versions do not exist yet).
2. personal-cloud: `gh workflow run terraform-mysql-apps.yaml --repo poly-glot/personal-cloud --ref main`
   to provision the database and secret versions, then re-run `terraform apply`. If it
   409s ("already exists"), `terraform import` the service instead of recreating it.
3. `gh secret set WIF_PROVIDER -R poly-glot/halveron --body "$(terraform output -raw halveron_wif_provider)"`
   and the same for `GCP_SA_EMAIL` from `halveron_gcp_sa_email`.
4. Admin password:
   `openssl rand -base64 30 | tr -d '/+=' | cut -c1-24 | tr -d '\n' | gcloud secrets create halveron-wp-admin-pass --project firebase-cloud-491613 --replication-policy=automatic --data-file=-`
5. Push to `main`. The deploy workflow builds, deploys Cloud Run service `halveron`, runs
   the bootstrap job and deploys Firebase Hosting.
6. Create the bootstrap job once (the production image has no wp-cli; `run.php` installs
   WordPress and runs the seed):
   ```bash
   gcloud run jobs create halveron-bootstrap --project firebase-cloud-491613 --region europe-west2 \
     --image europe-west2-docker.pkg.dev/firebase-cloud-491613/firebase-cloud/halveron:<tag> \
     --command php --args /app/public/wp-content/seed/run.php \
     --service-account halveron-runtime@firebase-cloud-491613.iam.gserviceaccount.com \
     --set-secrets DB_HOST=db-host:latest,DB_USER=halveron-db-user:latest,DB_PASS=halveron-db-pass:latest,DB_NAME=halveron-db-name:latest,WP_ADMIN_PASS=halveron-wp-admin-pass:latest \
     --set-env-vars DB_SSL=1,WP_TITLE=Halveron,GCS_UPLOADS_BUCKET=<bucket> \
     --max-retries 0 --task-timeout 600
   gcloud run jobs execute halveron-bootstrap --project firebase-cloud-491613 --region europe-west2 --wait
   ```
   CI updates and re-executes the job on every push.
7. DNS: add the record from `terraform output halveron_required_dns`
   (usually `CNAME halveron -> halveron-junaid.web.app` for `halveron.junaid.guru`).

## Production

Pushes to `main` deploy to Cloud Run (service `halveron`) in `firebase-cloud-491613` /
`europe-west2`, then re-run the bootstrap job so content model and seed changes land.
Firebase Hosting serves `halveron.junaid.guru` by rewriting `**` to the service and
forwarding only the `__session` cookie: the public site works, but wp-admin stays on the
run.app URL. Media uploads go to the app's GCS bucket via the runtime service account.
wp-cron runs from Cloud Scheduler; file mods are disabled on Cloud Run.
