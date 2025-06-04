# eFood API 🍔🚚

A Laravel-based API for food ordering and delivery management system.

## Overview

This project is a comprehensive API built with Laravel 12 that powers food ordering services, including client-facing features and driver management capabilities. It uses Filament 3 for administration and includes support for location services, payments via Viva Payments, and media management.

## Features

- **Client Module**: Handles customer accounts, orders, and payments 👤
- **Driver Module**: Manages delivery personnel, tracking, and order fulfillment 🚗
- **Admin Panel**: Built with Filament 3 for easy management of the platform ⚙️
- **Localization**: Multi-language support via Laravel Translatable 🌐
- **Payment Integration**: Viva Payments integration for handling transactions 💳
- **Location Services**: Integration with Google Maps for location picking and tracking 📍

## Requirements

- PHP 8.2 or higher
- MySQL/MariaDB
- Composer
- Node.js & NPM (for frontend assets)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Lucifer0885/efood-api-new.git
   cd efood-api-new
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Create and configure your environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Set up your database credentials in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=efood_api
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Configure additional environment variables:
   - `CLIENT_URL`: URL for the client application
   - `DRIVER_URL`: URL for the driver application
   - `GMAP_API`: Google Maps API key
   - `VIVA_*`: Viva Payments configuration

7. Run migrations to set up the database:
   ```bash
   php artisan migrate
   ```

8. Seed the database with initial data (optional):
    <small>To run specific seeders use `--class=<SeederName>` flag </small>
   ```bash
   php artisan db:seed
   ```

## API Routes

The API is organized into modules:

- `/` - Base API endpoint
- `/client` - Client-facing endpoints
- `/driver` - Driver-facing endpoints

## Development ⚡

### Recommended Development Environment

We recommend using [Herd](https://herd.laravel.com/) as your local development environment. Herd provides a native PHP, MySQL, and Redis development environment for macOS and Windows with zero configuration.

To run the development server with Herd:

1. Install Herd following the instructions at [herd.laravel.com](https://herd.laravel.com/)
2. Start Herd and configure your project

## Technologies

- [Laravel 12](https://laravel.com)
- [Filament 3](https://filamentphp.com)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) for API authentication
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable)
- [Spatie Media Library](https://github.com/spatie/laravel-medialibrary)
- [Laravel Viva Payments](https://github.com/sebdesign/laravel-viva-payments)

## License

This project is licensed under the MIT License.

## Maintainers

- [Lucifer0885](https://github.com/Lucifer0885) 👨‍💻

Last updated: 2025-06-04 02:12:31 UTC by Lucifer0885
