# Equipment Reservation System

This is a lightweight Equipment Reservation System built for internal company use. The system features a modern, clean architecture, responsive layout, dynamic localization, and role-based access control (Admin and User).

## System Architecture & Features

The project is split into two main packages, organized according to Clean Architecture principles:

- **[Backend (Laravel 11 / PHP 8.3)](file:./backend/README.md)**: Designed with the Repository & Service pattern, interfaces, localized API responses, and custom validation requests.
- **[Frontend (Vue 3 / Vuetify 3 / TypeScript)](file:./frontend/README.md)**: Structured with Vue Router navigation guards, Pinia store state management, dynamic i18n localization switcher, and Vuetify responsive components.

---

## Technical Stack

- **Backend**: Laravel 11, PHP 8.3, MySQL 8, Sanctum Authentication.
- **Frontend**: Vue 3 SPA, Vuetify 3, TypeScript, Pinia, Axios, Vue I18n.
- **Environment**: Docker & Docker Compose.

---

## Quick Start (Using Docker Compose)
### 1. Setup
Clone the repository, navigate to the project root directory, and run:
```bash
docker compose up -d --build

# Install dependencies
docker compose exec backend composer install

# If you got error with php version check, just using tag --ignore-platform-reqs
docker compose exec backend composer install --ignore-platform-reqs

# Configure env
docker compose exec backend cp .env.example .env
# Edit .env to set your DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Generate app key
docker compose exec backend php artisan key:generate
```

### 2. Run Database Migrations and Seed Test Users
Execute database migrations and seed the initial users directly inside the running backend container:
```bash
# Run migrations
docker compose exec backend php artisan migrate

# Seed test data
docker compose exec backend php artisan db:seed
```

The system will be accessible at:
- **Frontend SPA**: `http://localhost:3000`
- **Backend API**: `http://localhost:8000`

---

## Seeding & Test Credentials

The database contains the following pre-configured user credentials (`password@123` for all accounts):

| Name | Email | Password | Role | Description |
| :--- | :--- | :--- | :--- | :--- |
| **System Admin** | `admin@company.com` | `password@123` | **Admin** | Full access to manage equipment and approve/reject reservations. |
| **Nguyen Van A** | `usera@company.com` | `password@123` | **User** | Access to view available equipment and manage own reservations. |
| **Tran Thi B** | `userb@company.com` | `password@123` | **User** | Access to view available equipment and manage own reservations. |

---

## Assumptions & Design Choices

1. **Database**: Built-in support for MySQL 8 in Docker. The local PHP SQLite setup is bypassed in favor of a robust Dockerized MySQL database.
2. **Deletions**:
   - **Equipment**: Hard delete is used.
   - **Reservations**: Users can delete their own pending reservations. Deleting an approved reservation automatically reverts the associated equipment to `available`.
3. **Localization**: Middleware on the backend dynamically reads `Accept-Language` headers (defaulting to Vietnamese `vi` if absent). The frontend intercepts requests and injects the header automatically.
4. **Clean Architecture**: Direct calling of Eloquent Models/Repositories in controllers has been eliminated. Flow goes from `Controller` -> `Service Interface` -> `Service Class` -> `Repository Interface` -> `Eloquent Repository`.
