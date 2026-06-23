# AI Agent Instructions: Expense Splitter

## Overview
This is a full-stack "Expense Splitter" application. The backend is built with Laravel 11, and the frontend is built with Nuxt 3 (Vue.js) using Tailwind CSS v4 and Pinia for state management. 

## Architecture & Important Files

### 1. Backend (Laravel)
The backend rigidly follows the **Repository and Service Pattern** to decouple business logic from controllers.
- **Controllers:** `backend/app/Http/Controllers/` (e.g., `AuthController.php`) handle incoming HTTP requests and return JSON.
- **Services:** `backend/app/Services/` (e.g., `AuthService.php`) contain core business logic. They MUST implement interfaces defined in `backend/app/Services/Interfaces/`.
- **Repositories:** `backend/app/Repositories/` (e.g., `UserRepository.php`) handle database queries and Eloquent interactions. They MUST implement interfaces defined in `backend/app/Repositories/Interfaces/`.
- **Service Providers:** `backend/app/Providers/AppServiceProvider.php` is where interfaces are bound to their concrete classes for Dependency Injection.
- **Routing:** `backend/routes/api.php` contains all API endpoints.

### 2. Frontend (Nuxt 3)
The frontend uses a modern Nuxt 3 architecture with composables, global state, and a robust component system.
- **Pages:** `frontend/app/pages/` (e.g., `login.vue`, `register.vue`, `index.vue`). Uses Tailwind CSS and PrimeIcons.
- **Components:** `frontend/app/components/` (e.g., `BaseInput.vue`, `BaseButton.vue`). Highly reusable UI elements are stored here.
- **State Management:** `frontend/app/stores/auth.ts` uses Pinia. We use cookies for persistence and hydrate the state via Nuxt plugins.
- **API Wrapper:** `frontend/app/composables/useApi.ts` is an `ofetch` wrapper that automatically attaches the Bearer token and JSON headers to outgoing requests.
- **Plugins:** `frontend/app/plugins/auth.ts` securely triggers the `/me` hydration on page load using Nuxt's context.

## AI Agent Directives
1. **Prioritize Interfaces:** When adding new database entities or logic flows, always create an `Interface` first, bind it in `AppServiceProvider`, and inject it into the Controller/Service via the constructor. Do not hard-couple classes.
2. **Design Philosophy:** Ensure the UI always looks premium. Use Tailwind CSS v4 and integrate `primeicons`. Avoid generic, flat styles. Utilize dynamic hover states, transitions, and component-based structures.
3. **API Consumption:** Always use the `useApi()` composable on the frontend when communicating with the Laravel backend to ensure tokens are securely passed using the `Headers` object.
4. **Commenting:** Add descriptive, single-line comments above all class methods (in both PHP and TypeScript) to maintain clarity.
