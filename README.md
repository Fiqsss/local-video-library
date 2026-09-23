# VidoHub Laravel starter

This is a Laravel application scaffold intended to be copied into a fresh Laravel project. It includes the public video collection, detail/player page, recommendations, admin CRUD for videos, category management, URL sources, and uploaded media stored on the server.

## Requirements
- A fresh Laravel application (Laravel 11/12 recommended)
- PHP 8.2+ and Composer
- MySQL/MariaDB or another Laravel-supported database
- Node is not required for these Blade views

## Install
1. Create a project: `composer create-project laravel/laravel vidohub`
2. Copy the included `app/`, `database/`, `resources/`, and `routes/` directories into the project, merging folders.
3. Configure `.env` database values (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. Run:
   ```bash
   php artisan migrate
   php artisan storage:link
   php artisan serve
   ```
5. Open `/` for the public collection and `/admin` for the management dashboard.

## Notes
- The admin routes are not protected by authentication in this starter. Before deploying publicly, add Laravel authentication/authorization middleware to the `/admin` route group.
- Uploaded videos are stored under `storage/app/public/videos` and served through the public storage symlink.
- The upload validation currently allows video MIME types MP4, WebM, Ogg, QuickTime and Matroska, up to 500 MB. PHP and web-server upload limits must also be raised to match.
- URL mode requires a direct, browser-playable media URL. A normal page URL from a streaming website may not be embeddable because of access controls, CORS, DRM or provider restrictions.
- Duration is entered by the administrator as `minutes:seconds`.
- Local laptop paths cannot be used as public media sources. Choose “Upload dari laptop” to transmit the file to the Laravel server.
- This starter does not include thumbnails upload; the thumbnail field accepts an optional image URL.
