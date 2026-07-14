# Multi-site Audit Report (2026-07-14)

## Scope
- Verify multi-site setup for sdak.obec.in and second site in same server/web codebase.
- Verify database separation per site config.
- Check migration readiness and execute migrations if possible.

## Findings

### 1) Site configuration exists and is domain-based
- Loader: config/config.php
- Domain-specific configs are loaded from: config/sites/{domain}.php
- Found site configs:
  - config/sites/sdak.obec.in.php
  - config/sites/saak.obec.in.php
  - config/sites/localhost.php

Note: Requested domain name was "sask.obec.in" but codebase currently contains "saak.obec.in" (no file/config reference for sask.obec.in found).

### 2) Separate databases are configured per site
- sdak.obec.in -> DB_NAME: obecin_sdakks
- saak.obec.in -> DB_NAME: obecin_saak

Result: Database separation is configured correctly at code level.

### 3) Deployment script supports 2-site migration
- File: deploy-lftp.sh
- Supports site 1 migration via MIGRATE_URL
- Supports site 2 migration via MIGRATE_URL_2 (optional) and DEPLOY_SECRET_2 (optional)

### 4) Migration execution attempt (CLI)
Executed:
- php -r '$_SERVER["HTTP_HOST"]="sdak.obec.in"; ...; require "migrate.php";'
- php -r '$_SERVER["HTTP_HOST"]="saak.obec.in"; ...; require "migrate.php";'

Outputs:
- sdak.obec.in: DB connection failed (SQLSTATE[HY000] [2002] Connection refused)
- saak.obec.in: DB connection failed (SQLSTATE[HY000] [2002] Connection refused)

Result: Migration did not run in this environment because database is unreachable from current machine/session.

## Migration readiness
- Migration runner exists: migrate.php
- Migration folder exists: migrations/
- Latest migration file in repository: migrations/032_fix_duplicate_receipt_numbers.sql

## Recommended next actions on server
1. Run migration endpoint directly on production host(s):
   - https://sdak.obec.in/migrate.php?key=YOUR_SECRET
   - https://saak.obec.in/migrate.php?key=YOUR_SECRET (or second secret if configured)
2. If second domain should be sask.obec.in (not saak.obec.in):
   - Add config/sites/sask.obec.in.php
   - Ensure DNS/vhost points to the same project root
   - Set correct DB credentials for sask
3. Ensure .deploy.env has both migration URLs if deploying both sites from same pipeline.

## Files reviewed
- config/config.php
- config/sites/sdak.obec.in.php
- config/sites/saak.obec.in.php
- config/sites/localhost.php
- web/index.php
- deploy-lftp.sh
- .deploy.env.example
- migrate.php
