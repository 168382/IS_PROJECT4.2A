# Lost & Found Tracking System

A polished campus lost-and-found platform built with Laravel. It helps students and staff report items, discover likely matches, submit verifiable ownership claims, and manage recovery through a clear staff workflow.

## Highlights

- Secure session authentication with login throttling and role-based access for students, staff, and administrators.
- Detailed lost and found reports with image uploads, dynamic categories, dates, locations, colours, and brands.
- Searchable item catalogue with category, keyword, location, colour, and date filters.
- Automated matching through the included NLP service, with a resilient local fallback matcher.
- Evidence-based claim flow: proof of ownership is required, self-claims and duplicate claims are blocked, and competing claims close automatically after approval.
- Personal dashboard with reports, matches, claim status, and actionable notifications.
- Staff administration for claims, all reports, users, audit history, and recovery metrics.

## Technology

- PHP 8.3 and Laravel 13
- Bootstrap 5 and Font Awesome
- JSON-backed repository layer for zero-configuration local development
- Optional Python/Flask NLP matcher in `machine_learning/`

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan serve
```

Visit `http://127.0.0.1:8000` and create an account. Existing local demo data is stored in `database/json/`.

To run the optional matching API in a second terminal:

```bash
cd machine_learning
source venv/bin/activate
python api.py
```

The application continues to produce matches when this service is unavailable by using its built-in fallback matcher.

## Quality checks

```bash
php artisan test
php artisan view:cache
php artisan route:list
```

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, a strong `APP_KEY`, and a real mail configuration before deployment.
- Replace the JSON repository layer with a database-backed implementation before running multiple web workers or deploying at scale.
- Do not publish the demo JSON data or use any seeded credentials in production. Provision administrator accounts securely and rotate all passwords.
- Store user-uploaded files on protected, backed-up object storage and configure an appropriate retention policy.
