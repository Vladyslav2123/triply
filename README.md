# 🌍 Triply.blog API

A modern Laravel-based platform for travel experiences and listings. This repository contains the backend API for the Triply.blog travel platform.

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Development Setup](#development-setup)
  - [Docker Development](#docker-development)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Security](#-security)
- [License](#-license)
- [Contact](#-contact)

## 🔍 Project Overview

Triply.blog is a platform that connects travelers with unique experiences and accommodations. The API provides endpoints for managing user profiles, listings, experiences, reviews, reservations, and more.

## 🛠 Tech Stack

### Core Technologies
- **PHP 8.3** - Modern PHP with type hints and attributes
- **Laravel 11** - Latest version of the Laravel framework
- **PostgreSQL** - Primary database
- **Redis** - For caching and queue management
- **AWS S3** - For file storage (photos)

### Development & Testing
- **Pest PHP** - Modern testing framework
- **Laravel Pint** - PHP code style fixer
- **Laravel IDE Helper** - Better IDE integration
- **Laravel Debugbar** - Debugging tool

### API & Documentation
- **L5-Swagger** - OpenAPI/Swagger documentation
- **Laravel Sanctum** - API authentication (session-based)

### Admin Panel
- **Filament** - Admin panel framework

### Infrastructure
- **Docker & Docker Compose** - Containerization
- **GitLab CI/CD** - Continuous integration and deployment
- **Caddy** (Production) / **Nginx** (Development) - Web servers

## 🏗 Project Structure

The project follows a well-organized structure with clear separation of concerns:

```
├── app/                  # Application code
│   ├── Actions/          # Single-purpose action classes
│   ├── Casts/            # Custom attribute casts
│   ├── Console/          # Artisan commands
│   ├── Constants/        # Application constants
│   ├── Enums/            # PHP 8.1+ enums
│   ├── Exceptions/       # Custom exceptions
│   ├── Filament/         # Admin panel resources
│   ├── Http/             # Controllers, middleware, requests
│   ├── Mail/             # Mail templates
│   ├── Models/           # Eloquent models
│   ├── Notifications/    # Notification classes
│   ├── Observers/        # Model observers
│   ├── OpenApi/          # Swagger/OpenAPI documentation
│   ├── Policies/         # Authorization policies
│   ├── Providers/        # Service providers
│   ├── QueryBuilders/    # Custom query builders
│   ├── Rules/            # Custom validation rules
│   ├── Services/         # Business logic services
│   └── ValueObjects/     # Value objects
├── bootstrap/            # Framework bootstrap files
├── config/               # Configuration files
├── database/             # Migrations, factories, seeders
├── docker/               # Docker configuration
│   ├── common/           # Shared Docker files
│   ├── dev/              # Development environment
│   └── prod/             # Production environment
├── docs/                 # Documentation files
├── public/               # Publicly accessible files
├── resources/            # Views, assets, lang files
├── routes/               # Route definitions
├── storage/              # Application storage
└── tests/                # Test files
```

## 🚀 Getting Started

### Prerequisites

- Docker & Docker Compose
- PHP 8.3+
- Composer
- Git

### Development Setup

1. Clone the repository:

```bash
git clone git@gitlab.com:triply/backend.git
cd backend
```

2. Install dependencies:

```bash
composer install
```

3. Environment setup:

```bash
cp .env.example .env
php artisan key:generate
```

4. Start the development environment:

```bash
composer dev
```

This will concurrently run:

- Laravel development server
- Queue worker
- Vite for frontend assets

### Docker Development

For a complete Docker-based development environment:

```bash
cd docker/dev
docker compose up -d
```

This will start all necessary services including:
- Web server
- PHP-FPM
- PostgreSQL
- Redis
- Queue worker

## 📚 API Documentation

API documentation is generated using L5-Swagger and is available at `/api/documentation`. The documentation includes:

- Endpoint descriptions
- Request/response schemas
- Authentication requirements
- Example requests

To regenerate the documentation after making changes:

```bash
php artisan l5-swagger:generate
```

## 🧪 Testing

The project uses Pest PHP for testing. To run the tests:

```bash
composer test
```

Or to run specific test suites:

```bash
php artisan test --filter=UserTest
```

## 🚢 Deployment

The project uses GitLab CI/CD for automated deployments. The pipeline includes:

1. Connection check
2. Registry image creation
3. Container deployment
4. Verification
5. Debugging (if needed)

Deployment configurations are located in the `.gitlab-ci.yml` file and the `docker/prod` directory.

## 🛡️ Security

The application implements several security measures:

- Session-based authentication with Laravel Sanctum
- CSRF protection with X-XSRF-TOKEN
- Authorization policies for all resources
- Input validation for all requests
- Secure file handling for uploads

If you discover any security-related issues, please email support@triply.blog.

## 📄 License

This project is licensed under the MIT License. See the LICENSE file for details.

## 👥 Contact

For support or inquiries:

- Email: support@triply.blog
