# GIYA — Localized Pilgrimage Companion for Metro Cebu

Laravel 13 + PostgreSQL implementation of the GIYA capstone project.
The application runs entirely offline after installation: no CDN, no
remote fonts, no tile server, no third-party JavaScript.

---

## Installation

```bash
# 1. Copy these folders into your existing Laravel project
#    (app/, database/, resources/, routes/, bootstrap/, public/)

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Create the database in PostgreSQL
#    CREATE DATABASE giya_db;

# 4. Schema and seed data
php artisan migrate --seed

# 5. Clear caches and run
php artisan optimize:clear
php artisan serve
```

Open `http://localhost:8000`.

### Seeded accounts

| Role  | Email                     | Password    |
|-------|---------------------------|-------------|
| Admin | admin@giya.app            | `Admin@123` |
| User  | maria.santos@email.com    | `User@123`  |

---

## Optional: install the display fonts

The design uses Playfair Display for headings and Lato for body text.
The stylesheet declares them via `@font-face` with a Georgia / system-ui
fallback, so the app renders correctly even if the files are absent.

To install them, download the four `.woff2` files into
`public/assets/fonts/` with these exact names:

```
playfair-display-400.woff2
playfair-display-700.woff2
lato-400.woff2
lato-700.woff2
```

Verify each file is roughly 15 KB or larger. A file of a few hundred
bytes means the download failed and the fallback font will be used.

---

## Optional: replace the destination artwork

`public/images/churches/` ships with generated SVG artwork. To use real
photographs, drop `.jpg` files named after the slugified church name:

```
public/images/churches/basilica-del-santo-nino.jpg
public/images/churches/simala-shrine.jpg
public/images/churches/magellans-cross-chapel.jpg
public/images/churches/cebu-metropolitan-cathedral.jpg
```

`Church::imagePath()` prefers `.jpg` over `.svg`, so no code change is
needed — the new photos appear as soon as the files exist.

---

## Architecture

```
app/
  Models/                 8 Eloquent models, all mapped to the existing schema
  Http/Controllers/       Page controllers + Auth/ + Admin/
  Http/Middleware/        AdminMiddleware (role gate)

database/
  migrations/             4 migrations, PostgreSQL CHECK constraints
  seeders/                10 Metro Cebu destinations, 5 schedules, 2 accounts

resources/views/
  layouts/                app · auth · admin
  components/             navbar, footer, flash, church-card, stars,
                          empty-state, pagination, offline-map,
                          line-chart, bar-chart
  auth/  plan/  admin/    page templates

public/
  assets/css/giya.css         design system + utilities
  assets/css/giya-icons.css   70 icons as inline SVG masks
  assets/js/giya.js           modal, password toggle, mobile nav
  images/                     logo · icons · churches · backgrounds · avatars
```

### Notable decisions

**No third-party front-end dependencies.** Bootstrap, Bootstrap Icons,
Leaflet and Chart.js were each replaced with a purpose-built equivalent
so the application has nothing to fetch at runtime:

- *Icons* are CSS `mask-image` data-URIs keyed to `.bi-*` class names.
- *Modals* are handled by `GiyaUI.Modal` with `data-modal-open` /
  `data-modal-close` triggers, Escape and backdrop dismissal.
- *Charts* are server-rendered SVG (`<x-line-chart>`, `<x-bar-chart>`)
  built from data the controller already aggregated.
- *Maps* are server-rendered SVG (`<x-offline-map>`) using an
  equirectangular projection of the latitude/longitude stored in the
  `churches` table. Pins, routes, labels and click-to-select all work
  with the machine disconnected.

**Mail is offline by default.** `.env.example` sets `MAIL_MAILER=log`, so
password-reset links are written to `storage/logs/laravel.log` rather
than sent over the network. Switch to `smtp` when a mail server is
available; the controller already handles send failures gracefully.

**The `is_walked_in` field does not exist in this project.** A
project-wide search found no occurrences in the schema, models,
controllers, views or the Profile section. The boolean columns that do
exist are `is_visited`, `is_premium`, `is_featured`, `is_active` and
`is_recurring`, none of which are walk-in related. No drop migration
was required.

---

## Routes

All internal navigation resolves through named routes. No route or link
points at an external service.

| Route name          | Method | URI                       |
|---------------------|--------|---------------------------|
| `login`             | GET    | /login                    |
| `register`          | GET    | /register                 |
| `password.request`  | GET    | /forgot-password          |
| `password.reset`    | GET    | /reset-password/{token}   |
| `home`              | GET    | /home                     |
| `map`               | GET    | /map                      |
| `chatbot`           | GET    | /chatbot                  |
| `profile`           | GET    | /profile                  |
| `profile.update`    | PATCH  | /profile                  |
| `profile.password`  | PATCH  | /profile/password         |
| `plan.hub`          | GET    | /plan                     |
| `plan.create`       | GET    | /plan/create              |
| `plan.visita`       | GET    | /plan/visita-iglesia      |
| `plan.index`        | GET    | /plan/my-itineraries      |
| `plan.store`        | POST   | /plan                     |
| `plan.stop.visited` | POST   | /plan/stop/visited        |
| `plan.show`         | GET    | /plan/{itinerary}         |
| `plan.destroy`      | DELETE | /plan/{itinerary}         |
| `admin.dashboard`   | GET    | /admin                    |
| `admin.users`       | GET    | /admin/users              |
| `admin.destinations`| GET    | /admin/destinations       |
| `admin.schedules`   | GET    | /admin/schedules          |
| `admin.feedback`    | GET    | /admin/feedback           |
| `admin.transactions`| GET    | /admin/transactions       |

---

## Offline verification

Disconnect the machine, then confirm:

- [ ] `php artisan serve` starts and `/login` renders with full styling
- [ ] Sign in works; the navbar, icons and avatars all display
- [ ] `/map` draws the destination map with pins and labels
- [ ] Sidebar search and category filters respond
- [ ] `/plan/create` builds a route and saves it
- [ ] `/plan/{id}` shows the route map and marks stops visited
- [ ] `/profile` opens both modals and saves changes
- [ ] `/admin` renders both charts
- [ ] The browser console reports no failed requests
