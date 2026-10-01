# Sharifi Academy

LMS modular monolith: Laravel 13, MySQL, Inertia.js, React, Tailwind, Fortify.

## Setup

1. Copy environment file and configure MySQL:

```sh
cp .env.example .env
php artisan key:generate
```

Or start MySQL with Docker:

```sh
docker compose up -d mysql
```

Then set in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sharifi_academy
DB_USERNAME=sharifi
DB_PASSWORD=secret
```

2. Install dependencies and migrate:

```sh
composer install
npm install
php artisan migrate --seed
npm run build
```

3. Run dev server:

```sh
composer dev
```

## Demo users (after seed)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sharifi.test | password |
| Instructor | instructor@sharifi.test | password |
| Student | student@sharifi.test | password |

## Panels

- Public: `/`
- Student: `/student/dashboard`
- Instructor: `/instructor`
- Admin: `/admin`

## Payments

Zarinpal sandbox is used when `ZARINPAL_MERCHANT_ID` is empty (local auto-verify flow).
