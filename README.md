# FromDarna

Tunisian homemade food delivery platform built with Laravel.

## Technologies

- Laravel 10
- PHP
- MySQL / MariaDB
- Blade
- HTML, CSS, JavaScript

## Installation

1. Clone the repository.
2. Install dependencies:

   ```bash
   composer install
   ```

3. Create your environment file:

   ```bash
   copy .env.example .env
   ```

4. Generate the application key:

   ```bash
   php artisan key:generate
   ```

5. Configure your database in `.env`.
6. Run migrations:

   ```bash
   php artisan migrate
   ```

7. Start the development server:

   ```bash
   php artisan serve
   ```