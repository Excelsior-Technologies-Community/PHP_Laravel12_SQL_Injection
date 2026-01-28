# PHP_Laravel12_SQL_Injection

## Project Overview

This Laravel application demonstrates SQL Injection vulnerabilities and their prevention in a practical, hands-on way. The project shows both vulnerable and secure coding practices to help developers understand and prevent SQL injection attacks.

## Features

* Vulnerable examples demonstrating unsafe SQL query practices
* Secure examples showing proper SQL injection prevention techniques
* Interactive interface to test different SQL injection attacks
* Educational structure focused on learning best practices

## Prerequisites

* PHP 8.0 or higher
* Composer
* MySQL or MariaDB
* Laravel 12.x

## Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/laravel-sql-injection-demo.git
cd laravel-sql-injection-demo
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment

Copy the environment file:

```bash
cp .env.example .env
```

Update database credentials in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_sql_demo
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Run Migrations and Seed Database

```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```

### Step 6: Start Development Server

```bash
php artisan serve
```

Open in browser:

```
http://localhost:8000
```

## Project Structure

```
laravel-sql-injection-demo/
├── app/
│   ├── Http/Controllers/
│   │   ├── VulnerableUserController.php
│   │   └── HomeController.php
│   ├── Models/User.php
│   └── Rules/NoSqlInjection.php
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── vulnerable/users.blade.php
│   └── home.blade.php
├── database/
│   ├── migrations/create_users_table.php
│   └── seeders/UserSeeder.php
└── routes/web.php
```

## Demo Sections

### 1. Unsafe Raw SQL Queries

Route: `/unsafe-raw`

Demonstrates SQL injection vulnerability through direct string concatenation.

```php
$users = DB::select("SELECT * FROM users WHERE name LIKE '%$search%'");
```

### 2. Unsafe whereRaw Method

Route: `/unsafe-whereraw`

```php
$users = User::whereRaw("name LIKE '%$search%'")->get();
```

### 3. Safe Parameterized Queries

Route: `/safe-parameterized`

```php
$users = DB::select(
    "SELECT * FROM users WHERE name LIKE ? OR email LIKE ?",
    ["%$search%", "%$search%"]
);
```

### 4. Safe Eloquent ORM

Route: `/safe-eloquent`

```php
$users = User::where('name', 'LIKE', "%$search%")->get();
```

### 5. Safe Query Builder

Route: `/safe-querybuilder`

```php
$users = DB::table('users')
    ->where('name', 'LIKE', "%$search%")
    ->get();
```

## SQL Injection Test Inputs

```
' OR '1'='1
' UNION SELECT * FROM users --
'; DROP TABLE users; --
admin' --
' OR SLEEP(5) --
```

## Prevention Techniques Implemented

* Parameterized queries
* Eloquent ORM
* Query Builder
* Input validation using NoSqlInjection rule
* Input sanitization middleware
* Proper error handling

## Best Practices

### Do

* Use Eloquent ORM
* Use Query Builder
* Use parameter binding
* Validate all user input
* Handle errors securely
* Keep Laravel and PHP updated

### Do Not

* Concatenate user input in SQL
* Use whereRaw without bindings
* Use DB::raw with user input
* Expose SQL errors

## Security Middleware

Custom middleware logs suspicious SQL patterns and can be extended to reject malicious input.

## Custom Validation Rule

The NoSqlInjection rule detects common SQL injection patterns and blocks unsafe input.

## Testing

### Manual Testing

* Visit each demo route
* Try SQL injection inputs
* Compare vulnerable and secure behavior

### Automated Testing

```bash
php artisan test
```
### Screenshort
<img width="1657" height="946" alt="image" src="https://github.com/user-attachments/assets/144a4c52-f305-48bf-90c2-deed09ed4fe1" />
<img width="1577" height="963" alt="image" src="https://github.com/user-attachments/assets/87a609d4-ae75-4c50-ae31-d8c1743cc4c9" />
<img width="1610" height="966" alt="image" src="https://github.com/user-attachments/assets/0c0e8624-7c4a-4989-a53c-420615b3d65a" />

## Troubleshooting

### Database Errors

* Check `.env` configuration
* Ensure MySQL service is running

### Migration Issues

```bash
php artisan migrate:fresh
```

### Autoload Issues

```bash
composer dump-autoload
```

### Permission Issues

```bash
chmod -R 755 storage bootstrap/cache
```

## Contributing

* Fork the repository
* Create a feature branch
* Commit your changes
* Submit a pull request

## License

This project is open-source and available under the MIT License.

