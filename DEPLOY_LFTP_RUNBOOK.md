# LFTP Deploy Runbook

## Current standard
- Primary deploy method: use lftp via deploy-lftp.sh
- Current production domains in same codebase: sdak.obec.in, saak.obec.in
- Database mapping:
  - sdak.obec.in -> obecin_sdakks
  - saak.obec.in -> obecin_saak

## One-time setup
1. Copy env template:

```bash
cp .deploy.env.example .deploy.env
```

2. Set required values in .deploy.env:
- FTP_HOST, FTP_PORT, FTP_USER, FTP_PASS
- REMOTE_DIR
- SITE_URL
- DEPLOY_SECRET
- REMOTE_STATE_URL
- BACKUP_URL
- MIGRATE_URL
- MIGRATE_URL_2 (for saak)
- DEPLOY_SECRET_2 (optional, if different from DEPLOY_SECRET)

3. Set baseline commit:

```bash
git rev-parse HEAD > .deploy.git_hash
```

## Deploy steps
1. Commit local changes.
2. Run deploy:

```bash
./deploy-lftp.sh
```

3. Script behavior:
- Upload only changed files from git diff
- Delete removed files on remote
- If migrations/ changed:
  - run backup-db.php
  - run migrate.php for site 1
  - run migrate.php for site 2..10 when MIGRATE_URL_2..MIGRATE_URL_10 are defined

## Add future domains on same web root
1. Add site config file:
- config/sites/{domain}.php

2. Add deploy vars in .deploy.env:
- MIGRATE_URL_3, DEPLOY_SECRET_3 (and next numbers as needed)

3. Deploy with same script:

```bash
./deploy-lftp.sh
```

## Quick pre-deploy check
- .deploy.env exists and secrets are set
- .deploy.git_hash exists
- MIGRATE_URL + MIGRATE_URL_2 configured
- New domain config exists before adding MIGRATE_URL_N
