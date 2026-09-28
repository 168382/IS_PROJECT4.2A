# Lost & Found Tracking System

A polished campus lost-and-found platform built with Laravel. It helps students and staff report items, discover likely matches, submit verifiable ownership claims, and manage recovery through a clear staff workflow.

## Highlights

- Secure session authentication with login throttling and role-based access for students, staff, and administrators.
- Detailed lost and found reports with image uploads, dynamic categories, dates, locations, colours, and brands.
- Searchable item catalogue with category, keyword, location, colour, and date filters.
- Automated text matching through the included NLP service, with a local fallback matcher. Uploaded item photos add visual similarity to match ranking when both reports have images.
- Evidence-based claim flow: proof of ownership is required, self-claims and duplicate claims are blocked, and competing claims close automatically after approval.
- Personal dashboard with reports, matches, claim status, and actionable notifications.
- Staff administration for claims, all reports, users, audit history, and recovery metrics.

## Technology

- PHP 8.2.4 and Laravel 12
- Bootstrap 5 and Font Awesome
- SQLite for item reports and their uploaded photos; JSON-backed users, categories, claims, matches, and notifications
- Optional Python/Flask NLP matcher in `machine_learning/`

## Local setup

The application requires PHP 8.2.4 or later. Frontend asset builds require Node.js
20.19 or later (or Node.js 22.12 or later).

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan serve
```

Visit `http://127.0.0.1:8000` and create an account. User accounts, claims, and
matching records remain in `database/json/`; lost/found item reports and their
photo bytes are stored together in the SQLite `item_records` table. PHP requires
the `pdo_sqlite` and `gd` extensions. A new report's photo is served through an
authenticated route and does not require the public storage symlink.
Visual similarity uses a perceptual fingerprint with colour and category checks
to supplement text matching; it suggests potential matches, not proof of ownership.
On a fresh install, the report and search category menus use the built-in categories
until a local `database/json/categories.json` file is created. An existing categories
file takes precedence.

For an existing installation, back up `database/json/`, `storage/app/public/`, and
the SQLite database before upgrading. After `php artisan migrate`, run
`php artisan items:import-json` once to copy existing lost/found reports and any
available photos into SQLite. The import leaves the original files untouched
and can be rerun; missing photo files are reported and cannot be recovered by
the import. Keep the original JSON files for users, claims, and matches.
`php artisan storage:link` is still needed to display existing claim-proof photos.

To build frontend assets:

```bash
npm run build
```

The Laravel Vite integration can optimize font fallbacks when the optional
`fontaine` package is installed. Its absence does not prevent builds.

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
- Migrate the remaining JSON-backed users, claims, matches, and notifications to a transactional database before running multiple web workers or deploying at scale.
- Do not publish the demo JSON data or use any seeded credentials in production. Provision administrator accounts securely and rotate all passwords.
- Back up the SQLite database (including uploaded item photos) and protect it from public access; configure a retention policy for photos and claim-proof files.
