# Backend - Equipment Reservation System API

The backend is built with **Laravel 11**, **PHP 8.3**, and **MySQL 8**, structured using **Clean Architecture** patterns (Repository and Service layers) interacting entirely via interfaces (Contracts) to ensure testability, readability, and clean boundaries.

## Architecture Structure

```
app/
├── Http/
│   ├── Controllers/       # HTTP Controllers (slim; depends only on Service/Repository Interfaces)
│   ├── Middleware/        # Localization middleware to translate responses dynamically
│   └── Requests/          # Form Requests for input validation
├── Repositories/
│   ├── Contracts/         # Repository interfaces (abstraction)
│   └── Eloquent/          # Eloquent-specific repository implementations
├── Services/
│   ├── Contracts/         # Business logic service interfaces
│   └── ReservationService # Core business logic implementation (conflict check, side effects)
└── Models/                # Database models and relations
```

---

## Technical Features

1. **Repository & Service Pattern**: Controllers inject Interfaces. DB operations are handled in Repositories, while complex business workflows are placed in Services.
2. **Conflict Resolution**: The reservation service guarantees that no equipment under maintenance can be reserved. It prevents booking overlapping dates for the same equipment.
3. **Automated State Machine (Side Effects)**:
   - When a reservation is marked `approved`, the associated equipment status switches to `reserved`, with `assigned_to` and `due_date` updated automatically.
   - When cancelled or rejected, the equipment automatically reverts back to `available`.
4. **Dynamic Localization**: Built-in middleware detects client-side `Accept-Language` headers and translates responses into English (`en`) or Vietnamese (`vi`).
5. **Unified JSON Response**: Standardized structures for error, error, and paginated responses are inherited globally from the base `Controller`.

---

## API Endpoints

All endpoints are prefix-protected with `/api` and require `Authorization: Bearer <token>` unless specified:

### Auth Endpoints
- `POST /api/login` (Public) - Login and receive Sanctum API Token.
- `POST /api/logout` - Revoke current active token.
- `GET /api/user` - Get current authenticated user profile details.

### Equipment Endpoints (CRUD)
- `GET /api/equipment` - Retrieve paginated equipment. Supports query filters `status`, `assigned_to`, `search`, and `page`.
- `POST /api/equipment` (Admin Only) - Create equipment.
- `GET /api/equipment/{id}` - View equipment detail and booking history.
- `PUT /api/equipment/{id}` (Admin Only) - Update equipment details.
- `DELETE /api/equipment/{id}` (Admin Only) - Hard delete equipment.

### Reservations Endpoints
- `GET /api/reservations` - Get paginated reservations list. Users see their own; Admin sees all. Filterable by `status`, `equipment_id`, `user_id`.
- `POST /api/reservations` - Create booking request.
- `GET /api/reservations/{id}` - Details of a specific booking.
- `PUT /api/reservations/{id}` - Update reservation dates (User pending only) or approve/reject/cancel (Admin).
- `DELETE /api/reservations/{id}` - Delete or cancel reservation request.

---

## Run Manually (Local Environment)

If you are running the project directly without Docker, make sure you configure your local `.env` and run:

```bash
# Install dependencies
composer install

# Generate key
php artisan key:generate

# Run migration & seed
php artisan migrate --seed

# Start dev server
php artisan serve
```
