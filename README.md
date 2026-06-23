# Expense Splitter

Welcome to the **Expense Splitter** project! This is a modern, decoupled full-stack application designed to help users securely track shared expenses, balances, and groups.

## Tech Stack
* **Backend:** Laravel 11, Sanctum (API Authentication)
* **Frontend:** Nuxt 3, Vue 3, Pinia (State Management)
* **Styling:** Tailwind CSS v4, PrimeIcons

---

## Workflow & Project Architecture

### The Backend Architecture
To make the application highly scalable and testable, we bypass "Fat Controllers" and implement strict **Service and Repository Patterns**.

1. **Repositories:** All direct database interactions and Eloquent queries live in Repositories (e.g., `UserRepository`). 
2. **Services:** All core business logic (e.g., hashing passwords, issuing Sanctum tokens) lives in Services (e.g., `AuthService`).
3. **Interfaces:** Both Repositories and Services are abstracted behind Interfaces (e.g., `AuthServiceInterface`). This prevents hard-coupling, enforces strict contracts, and allows us to swap implementations effortlessly.
4. **Controllers:** Controllers (e.g., `AuthController`) are extremely thin. They validate incoming requests (using FormRequests like `LoginRequest`), pass data to the injected Service, and return standardized JSON responses.

### The Frontend Architecture
We leverage Nuxt 3 to build a secure, component-driven User Interface.

1. **State Management:** We use Pinia (`stores/auth.ts`) to track the current user and their token. The token is also stored in a secure Cookie using Nuxt's `useCookie()`.
2. **Hydration Plugin:** A global Nuxt plugin (`plugins/auth.ts`) checks for the auth cookie on page load. It securely hits the backend `/me` endpoint to fetch the user's fresh data, preventing stale local data.
3. **API Wrapper:** We created a reusable `useApi()` composable that wraps Nuxt's native `$fetch`. It intercepts requests to automatically attach the `Authorization: Bearer <token>` Header, ensuring secure communication with the backend without boilerplate code.
4. **Component System:** The UI is broken into highly reusable pieces (e.g., `BaseInput.vue`, `BaseButton.vue`, `DashboardCard.vue`). These abstract away complex Tailwind classes and duplicate logic (like password show/hide functionality).

---

## Recent Changes & Changelog

* **Auth Module Setup:** Integrated Laravel Sanctum. Established `register`, `login`, `logout`, and `me` API endpoints.
* **Service/Repository Refactor:** Built `AuthService` and `UserRepository` using constructor dependency injection.
* **Form Requests:** Implemented `LoginRequest` and `RegisterRequest` for strict validation (including unique email constraints).
* **Frontend Setup:** Initialized Nuxt 3, installed Pinia, upgraded to Tailwind CSS v4, and integrated PrimeIcons.
* **Base Components:** Created `BaseInput.vue` (with dynamic password toggles), `BaseButton.vue` (with automatic loading spinners), and `DashboardCard.vue`.
* **Nuxt Context Handling:** Addressed Nuxt Context isolation bugs by shifting cookie hydration into a plugin and utilizing direct `$fetch` within Pinia actions.

---

## Getting Started

1. **Backend:** 
   - Navigate to `backend/`
   - Copy `.env.example` to `.env`
   - Set up your database credentials
   - Run migrations: `php artisan migrate`
   - Run server: `php artisan serve`

2. **Frontend:** 
   - Navigate to `frontend/`
   - Install dependencies: `npm install`
   - Run dev server: `npm run dev`
