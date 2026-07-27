# Cakto Campaign Setup

This Laravel API exposes the public endpoint below for the Cakto app campaign:

```text
POST https://YOUR-LARAVEL-DOMAIN/api/webhooks/cakto
```

Before enabling it in Cakto, set these deployment environment values:

```env
CAKTO_BASIC_WEBHOOK_SECRET=<the Basic webhook secret>
CAKTO_PLUS_WEBHOOK_SECRET=<the Plus webhook secret>
```

In Cakto, create one webhook for both campaign products and select `purchase_approved`.
The endpoint checks the configured secret and webhook secret and records whether it belongs to Basic or Plus, then stores the approved sale only once using the Cakto purchase id. It does not activate any existing app plan yet.

After deployment, run:

```bash
php artisan migrate --force
php artisan config:clear
```
