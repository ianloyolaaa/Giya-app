# GIYA

Laravel 13 + PostgreSQL. A localized travel companion for religious tourism and
pilgrimage in Metro Cebu.

---

## Run it on a new laptop

**Install first:** PHP 8.3+, Composer 2, PostgreSQL 16+, Git.

**Enable the PHP extensions.** Run `php --ini`, open that file, uncomment:

```ini
extension=pdo_pgsql
extension=pgsql
extension=fileinfo
extension=gd
```

Check with `php -m`. All four must appear.

**Then:**

```bash
git clone <repo-url> Giya-app
cd Giya-app
composer install

cp .env.example .env          # PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

**Create the database** in psql or pgAdmin:

```sql
CREATE DATABASE giya_db;
```

**Set your password** in `.env`:

```env
DB_DATABASE=giya_db
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password
```

**Finish:**

```bash
php artisan migrate --seed
php artisan storage:link
php artisan giya:tiles         # offline map tiles, 15-20 min
php artisan serve
```

Open http://127.0.0.1:8000



---

## When it breaks

| Error | Fix |
|---|---|
| `could not find driver` | Extensions not enabled, or wrong `php.ini` |
| `password authentication failed` | Fix `.env`, then `php artisan config:clear` |
| `Connection refused` | PostgreSQL service not running |
| `No application encryption key` | `php artisan key:generate` |
| `relation "..." does not exist` | Wrong database in `.env`, or `migrate` not run |
| Images 404 | `php artisan storage:link` |
| Blank map | `php artisan giya:tiles` |
| "Find my location" does nothing | Use `127.0.0.1`, not a LAN IP — geolocation needs HTTPS or localhost |
| Changes not showing | `php artisan view:clear` |

Keep **one** database. Spares from earlier attempts cause errors that look like
code faults.

---

## After `git pull`

```bash
composer install
php artisan migrate
php artisan view:clear
```

---

## Layout

```
app/Models/              16 models, one per ERD entity
app/Console/Commands/    giya:tiles — offline tile downloader
database/migrations/     full ERD schema
public/assets/js/        leaflet.js (self-hosted) + giya-leaflet.js
public/tiles/            offline map tiles
public/assets/css/       giya.css (design system + dark theme)
```

**Design decisions worth defending:**

- Counters like `rating` and `total_churches_visited` are computed from related
  tables, not stored — they cannot drift out of sync.
- Font sizes are in `rem`, so the Preferences size setting actually rescales the UI.
- CSS and JS carry `?v=filemtime(...)`, so edits appear without a hard refresh.
- Leaflet, fonts and icons are self-hosted; nothing loads from a CDN.

---

## Limitations

For the manuscript:

- Routes are straight lines with distances, not driving directions. Turn-by-turn
  hands off to Google Maps and needs a connection.
- Language preference saves but does not translate yet — no `lang/` files.
- Uploaded images sit on local disk; they vanish on redeploy on hosts like Render.
- Two additions beyond the original ERD: `devotee_preferences` and
  `churches.address`. **Both need adding to the diagram and Data Dictionary.**

---

## Before the defence

Disconnect from the internet and confirm: login renders styled, `/map` draws
tiles and pins, routes build, `/plan/create` saves, `/profile` switches theme and
font size, `/admin/destinations` shows the picker map, and the browser console is
clean.
