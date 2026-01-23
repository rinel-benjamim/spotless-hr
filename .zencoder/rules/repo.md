---
description: Repository Information Overview
alwaysApply: true
---

# Spotless HR Information

## Summary
Spotless HR is a comprehensive human resources management system built with Laravel 12. It features a modern frontend using Inertia.js with React 19 and Tailwind CSS 4. The application handles employee records, attendance, payroll, schedules, and absence management.

## Structure
- **app/**: Core Laravel application logic, including Models (Employee, Attendance, Payroll, etc.), Controllers, and Actions.
- **bootstrap/**: Application bootstrapping and configuration of middleware/exceptions.
- **config/**: Configuration files for the framework and various packages (Fortify, Inertia, etc.).
- **database/**: Database migrations, factories, and seeders.
- **resources/js/**: React-based frontend application using Inertia.js.
- **resources/css/**: Global styles and Tailwind CSS configuration.
- **routes/**: Route definitions for web, console, and settings.
- **tests/**: Testing suite using the Pest framework.

## Language & Runtime
**Language**: PHP, TypeScript  
**Version**: PHP ^8.2 (Laravel 12), React 19, TypeScript 5  
**Build System**: Vite (Frontend), Composer (Backend)  
**Package Manager**: npm, composer

## Dependencies
**Main Dependencies**:
- `laravel/framework`: ^12.0
- `inertiajs/inertia-laravel`: ^2.0
- `laravel/fortify`: ^1.30 (Authentication)
- `laravel/wayfinder`: ^0.1.9 (Type-safe routing)
- `@inertiajs/react`: ^2.3.7
- `react`: ^19.2.0
- `tailwindcss`: ^4.0.0
- `lucide-react`: ^0.475.0 (Icons)
- `date-fns`: ^4.1.0 (Date utilities)

**Development Dependencies**:
- `pestphp/pest`: ^4.3 (Testing)
- `laravel/sail`: ^1.41 (Docker development environment)
- `laravel/boost`: ^1.8 (Developer productivity)
- `laravel/pint`: ^1.24 (Code style)

## Build & Installation
```bash
# Install backend dependencies
composer install

# Install frontend dependencies
npm install

# Build frontend assets
npm run build

# Setup application (from composer.json)
composer run setup
```

## Docker

**Dockerfile**: Not explicitly present, but uses Laravel Sail.
**Configuration**: Laravel Sail provides a Docker-based development environment.
**Run Command**:
```bash
./vendor/bin/sail up
```

## Testing

**Framework**: Pest (PHP), PHPUnit
**Test Location**: `tests/Feature`, `tests/Unit`
**Naming Convention**: Standard Pest/PHPUnit naming (`ExampleTest.php`)
**Configuration**: `phpunit.xml`, `tests/Pest.php`

**Run Command**:

```bash
php artisan test
# or via composer
composer run test
```
