# Cakto Campaign Setup

This Laravel API exposes the public endpoint below for the Cakto app campaign:

```text
POST https://YOUR-LARAVEL-DOMAIN/api/webhooks/cakto
```

Before enabling it in Cakto, set these deployment environment values:

```env
CAKTO_WEBHOOK_SECRET=<the secret configured in Cakto>
CAKTO_CAMPAIGN_PRODUCT_ID=<the Cakto product UUID>
```

In Cakto, create a webhook for only this product and select `purchase_approved`.
The endpoint checks the configured secret and product id, then stores the approved sale only once using the Cakto purchase id. It does not activate any existing app plan yet.

After deployment, run:

```bash
php artisan migrate --force
php artisan config:clear
```
