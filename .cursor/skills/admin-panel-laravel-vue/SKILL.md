---
name: admin-panel-laravel-vue
description: Guides building admin panels with Laravel backend and Vue 3 frontend. Use when developing REST APIs, Sanctum auth, Vue 3 Composition API, Pinia, admin dashboards, CRUD with Laravel + Vue, order management, or image uploads in this stack.
---

# Admin Panel Skills (Laravel + Vue 3)

Apply this skill when building or modifying admin panel features using Laravel (API) and Vue 3 (SPA).

---

## Backend (Laravel)

- **API**: RESTful resource routes; return JSON via **API Resources** for consistent shapes. Use `apiResource()` or explicit `Route::get/post/put/delete` with clear naming.
- **Auth**: **Laravel Sanctum** for SPA or token auth. Use `sanctum` middleware on API routes; for SPA, ensure CORS and cookie/session config match the frontend origin.
- **Validation**: **Form Request** classes for every mutable endpoint; never validate in controller with `$request->validate()` for non-trivial rules.
- **ORM**: Use **Eloquent relationships** (`belongsTo`, `hasMany`, etc.) and eager load to avoid N+1 (`with()`, `load()`). Prefer query scopes for reusable filters.
- **Authorization**: **Policies** for resource access; call `$this->authorize('action', $model)` in controllers. Gate/role checks where needed (e.g. Owner vs Staff).
- **Database**: **Migrations** for schema; **seeders** for dev/demo data. Use foreign keys and indexes (e.g. `user_id`, `status`, `created_at`).
- **Storage**: **Laravel Storage** (`Storage::disk('public')` or custom disk) for uploads; use `store()` / `storeAs()` and return URLs via `Storage::url()` or a signed URL when appropriate.
- **Pagination**: Return paginated lists with `->paginate($perPage)`; include meta (current_page, total, per_page) in API response (Laravel’s default pagination format is fine).
- **Filtering**: Prefer query parameters (e.g. `?status=active&search=`) and apply in controller or dedicated query builder/scopes; keep logic out of the route definition.
- **Events**: Use Laravel **events** (and listeners) for side effects (e.g. notifications, logs) rather than stuffing logic in controllers.

---

## Frontend (Vue 3)

- **API**: **Vue 3 Composition API** with `<script setup>`; use **TypeScript** for props, emits, and API response types.
- **Build**: **Vite** as the build system; align with Laravel’s Vite plugin if the app is served by Laravel.
- **Routing**: **Vue Router** for SPA routes; use route guards for auth (redirect to login when unauthenticated).
- **State**: **Pinia** stores for global state (e.g. user, UI flags); keep server state in components or composables and sync via API, using Pinia where it’s shared.
- **HTTP**: **Axios** (or fetch) with a base instance: set base URL, attach Sanctum credentials (e.g. `withCredentials: true`), and optionally an auth token. Prefer a small **API service layer** (e.g. `api/users.js`) that wraps endpoints.
- **Forms**: **Reactive forms** with `ref`/`reactive` and validation (e.g. VeeValidate or manual); show errors from API (Form Request validation messages) and optionally client-side rules.
- **Components**: **Component-based architecture**; small, reusable components. Use composables for shared logic (e.g. `usePagination`, `useFormSubmit`).
- **Types**: Define TypeScript interfaces for API responses and payloads; keep them next to the API service or in a shared `types` folder.

---

## UI / UX

- **Styling**: **Tailwind CSS** for layout and components; use a consistent spacing scale (e.g. `p-4`, `p-6`, `gap-4`).
- **Layout**: **Sidebar + topbar** layout for admin; sidebar for navigation, topbar for user menu and breadcrumbs. Make it responsive (collapse sidebar on small screens).
- **Tables**: **Data tables** with column headers, sort (if needed), and **search + filters** that sync to query params and API (server-side when data is large).
- **Modals**: Use **modal dialogs** for create/edit forms or confirmations; manage open/close state in parent or a composable; avoid deep modal nesting.
- **Forms**: **Form validation UI** (inline errors, disabled submit until valid or show errors on submit); consistent input/button styling.
- **Feedback**: **Toast notifications** for success/error after API calls; show **loading states** (skeleton or spinner) and **empty states** (message + optional CTA) for lists and detail views.

---

## Data Management

- **CRUD**: Align frontend actions with REST (GET list/detail, POST create, PUT/PATCH update, DELETE). Use one API service module per resource when possible.
- **Pagination**: Consume Laravel pagination meta (current_page, last_page, per_page, total); provide prev/next and optional page size selector. Sync page (and filters) to **query parameters** so views are shareable and back-button friendly.
- **Filtering**: **Server-side filtering** via query params; reflect filters in the URL and in the request. Reset to page 1 when filters change.
- **Optimistic UI**: Where appropriate (e.g. toggle, quick edit), update local state immediately and revert on API error; for critical flows (e.g. payment), prefer wait-for-response.

---

## Security

- **Auth**: **Sanctum** token or session; never send credentials in URLs. Use HTTPS in production.
- **Roles**: **Role-based access** (e.g. Owner vs Staff) enforced in Laravel (middleware, policies); hide or disable UI for disallowed actions based on same roles/permissions.
- **Resources**: **Policy-based** checks on every resource mutation and sensitive read; return 403 when unauthorized.
- **Input**: Validate all inputs with **Form Requests**; sanitize and validate file types/sizes for uploads.
- **Files**: Validate **file type and size** server-side; store outside web root or use a non-public disk if needed; serve via controller or signed URLs when required.

---

## Image Handling

- **Preview**: Allow **image preview** before upload (e.g. `URL.createObjectURL(file)` in Vue); revoke object URL on cleanup to avoid leaks.
- **Validation**: **Client-side**: type (e.g. image/jpeg, image/png) and size; **server-side**: MIME/extension and size again; reject invalid files with clear messages.
- **Storage**: Store on **Laravel public disk** (or configured disk) with a safe name (e.g. hash or UUID); avoid user-controlled filenames.
- **URLs**: Use **Storage::url()** or app URL + path for display in the admin; consider thumbnails or responsive URLs if needed.

---

## Order Management Logic

- **Status**: Model order status as an enum or string (e.g. pending, confirmed, preparing, completed, cancelled); define **allowed transitions** (e.g. pending → confirmed, confirmed → preparing).
- **Transitions**: Enforce transitions in a service or policy; validate “from → to” before updating; optionally emit events for notifications or logging.
- **Items**: Model **order items** as a related resource (e.g. `Order` hasMany `OrderItem`); store quantity, unit price, and product/variant reference.
- **Totals**: Compute **order totals** server-side (e.g. sum of items + tax/shipping if any); never trust frontend-calculated totals for persistence or payment.

---

## Developer Experience

- **Structure**: **Clean folder layout**: e.g. `api/` (or `services/`), `composables/`, `components/`, `views/`, `types/` on the frontend; Laravel’s standard `app/Http/Controllers`, `app/Models`, Form Requests, Policies on the backend.
- **Types**: **Type-safe** code: TypeScript on Vue side; PHP type hints and return types on Laravel side.
- **Composables**: Reuse logic in **composables** (e.g. `useAuth`, `usePagination`, `useCrud`); keep components thin.
- **API layer**: **API service abstraction**: one module (or class) per domain (users, orders, products) with methods that call Axios and return typed data.
- **Commits**: **Commit discipline**: small, logical commits; messages that describe what and why (e.g. “feat(orders): add status transition validation”).

---

## Quick Checklist for New Features

- [ ] Laravel: Form Request, Policy, API Resource (if list/detail), migration if schema change
- [ ] Vue: Types for payload/response, API service method, component + composable if reused
- [ ] URL/query params for list page (pagination, filters)
- [ ] Loading, empty, and error states in UI
- [ ] Toasts or inline feedback for success/failure
- [ ] Auth and role checks on backend; UI reflects permissions
