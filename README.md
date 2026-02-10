# Sports Club API

A production-ready REST API built with Symfony 7 and API Platform, featuring secure authentication and a complete membership management system.

---

## 🚀 Features

### 🔐 Authentication & Security
- JWT Authentication (LexikJWTAuthenticationBundle)
- Role-Based Access Control (ROLE_USER, ROLE_ADMIN)
- Fine-grained permission system using Symfony Voters
- Business rule enforcement with proper HTTP error handling

### 🏟 Club Management
- Full CRUD operations for clubs
- Automatic OWNER assignment on club creation
- Search & ordering filters
- Pagination support

### 👥 Membership System
- Many-to-Many relationship via Membership entity
- Unique membership constraint (User ↔ Club)
- Roles within clubs: `OWNER`, `MEMBER`
- Custom endpoint to join a club
- Prevention of duplicate memberships
- Business logic preventing owners from joining their own club

### 🧪 Testing
- Functional API tests using ApiTestCase
- Tests covering:
  - Club creation
  - Automatic OWNER membership creation
  - Join club flow
  - 409 conflict handling

---
## 🛠 Tech Stack

- PHP 8.3
- Symfony 7
- API Platform 3
- Doctrine ORM
- PostgreSQL
- LexikJWTAuthenticationBundle
- PHPUnit

---

## ⚙️ Installation

1. **Créer** un dossier
```bash
mkdir sports-club
cd sports-club
```
2. **Cloner** les dépôts
```bash
git clone https://github.com/Enzob4/sports-club-frontend.git
git clone https://github.com/Enzob4/sports-club-api.git
```
3. **Démarrer** docker
```bash
cd sports-club-api
docker compose up -d
```
4. **Génerer** les clés de sécurité
```bash
docker compose exec backend php bin/console lexik:jwt:generate-keypair
```
5. **Créer et charger** la base de données et les fixtures
```bash
docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction
```
6. **Se connecter** à http://localhost:3000/
```bash
admin@example.com
password
```

## 🧪 Run Tests
```bash
php bin/phpunit
```

## 📖 API Documentation
Once the server is running (symfony serve), explore the API via the built-in documentation:
- Swagger UI: https://localhost:8000/api/docs
- Authentication: Send a POST request to /api/auth with email and password to receive your JWT.
---

## 📌 Example Endpoints
- POST /api/auth
- POST /api/clubs Authorization: Bearer {token}
- POST /api/clubs/{id}/join
Authorization: Bearer {token}
---