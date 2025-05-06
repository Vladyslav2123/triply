# Triply.blog

A Laravel-based platform for travel experiences and listings.

## 🛠 Tech Stack

- PHP 8.3
- Laravel 11
- Docker & Docker Compose
- PostgreSQL
- Caddy (Production) / Nginx (Development)
- Swagger/OpenAPI for API documentation

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- PHP 8.3+
- Composer

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

### Docker Development Environment

```bash
cd docker/dev
docker compose up -d
```

### Production Deployment

The project uses GitLab CI/CD for automated deployments. The pipeline includes:

1. Connection check
2. Registry image creation
3. Container deployment
4. Verification
5. Debugging (if needed)

## 📚 API Documentation

API documentation is available via Swagger UI at `/api/documentation`.

## 🧪 Testing

```bash
composer test
```

## 🛡️ Security

If you discover any security-related issues, please email support@triply.blog.

## 📄 License

This project is licensed under the MIT License.

## 👥 Contact

For support or inquiries:

- Email: support@triply.blog
