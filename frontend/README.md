# Frontend - Equipment Reservation SPA

The frontend is a modern **Vue 3 Single Page Application (SPA)** styled with **Vuetify 3** and written in **TypeScript** using **Pinia** for state management.

## Project Structure

```
src/
├── components/     # Reusable layout and ui components (MainLayout.vue)
├── pages/          # Page views (Login, EquipmentList, EquipmentDetails, etc.)
├── plugins/        # Registered plugins (Vuetify, Axios, Vue I18n)
├── router/         # Vue Router definition with navigation auth guards
└── stores/         # Pinia state stores (auth.ts)
```

---

## Technical Features

1. **Role-Based Routing Protection**: Navigation guards inspect meta fields on routes. Guest-only paths (like `/login`) are protected from logged-in users, and admin/user routes are blocked for guests.
2. **Axios HTTP Client Interceptor**:
   - Outgoing requests automatically inject `Authorization: Bearer <token>` from local storage.
   - Inject the user's active locale language in the `Accept-Language` header to keep the backend response synchronized.
   - Redirect to `/login` if any backend API responds with `401 Unauthorized`.
3. **Interactive UI (Vuetify 3)**:
   - Dynamic chips displaying available (green), reserved (blue), and maintenance (yellow/orange) statuses.
   - Forms to add/edit equipment and reservations inside modern dialogs (modals) without reloading the page.
   - Debounced search queries for seamless equipment filtering.
4. **Dynamic Language Switcher**: Toggles interface text and backend error messages instantly between English and Vietnamese.
5. **Direct Page Navigation**: Pagination handles page numbers natively, allowing users to jump directly to any page using the numerical selector.

---

## Running Manually (Development Environment)

If running outside the Docker Compose network, follow these steps:

### 1. Install Dependencies
```bash
npm install
```

### 2. Run the Development Server
```bash
npm run dev
```

The application will run locally on `http://localhost:3000`.

---

## Build for Production
To build the static assets for production deployment:
```bash
npm run build
```
This runs type-checking and outputs production bundle files in the `dist` directory.
