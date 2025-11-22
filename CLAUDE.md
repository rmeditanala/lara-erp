# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **modern ERP (Enterprise Resource Planning) web application** built with Laravel 12 + Vue 3. It's a starter kit that provides a complete authentication system, modern tooling, and a scalable architecture for building enterprise applications.

**Technology Stack:**
- Backend: PHP 8.2+ with Laravel 12 framework
- Frontend: Vue 3 with TypeScript using Inertia.js for SPA experience
- Database: SQLite (development) with migrations support
- Styling: TailwindCSS v4 with shadcn-vue component system
- Build Tool: Vite with Laravel plugin
- Testing: Pest PHP framework
- Authentication: Laravel Fortify with 2FA support

## Common Development Commands

### Frontend Development
```bash
npm run dev          # Start Vite development server with HMR
npm run build        # Build frontend assets for production
npm run build:ssr    # Build SSR assets (includes client build)
npm run lint         # ESLint with auto-fix
npm run format       # Prettier formatting for resources/
npm run format:check # Check formatting without changes
```

### Backend Development
```bash
php artisan serve       # Start Laravel development server
php artisan migrate     # Run database migrations
php artisan test        # Run Pest PHP tests
php artisan tinker      # Laravel REPL
```

### Full Development Workflow
```bash
composer dev            # Runs: PHP server + queue + logs + Vite dev server (recommended)
composer dev:ssr        # Runs: PHP server + queue + logs + SSR server
composer setup          # Complete project setup (install deps, .env, migrate, build)
composer test           # Run all tests with config cache clear
```

### Code Quality
```bash
./vendor/bin/pint       # PHP code formatting (Laravel Pint)
php artisan test        # Run Pest tests
npm run lint           # Frontend linting
```

## Architecture Overview

### SPA Architecture with Inertia.js
- **Progressive Web App**: Single-page application with server-rendered initial pages
- **No REST APIs**: Inertia.js bridges Laravel backend with Vue frontend directly
- **Reactive Updates**: Data updates via Inertia "lazy loading" without full page reloads
- **SSR Support**: Configured for server-side rendering on port 13714

### Directory Structure

**Backend (Laravel):**
- `/app/Http/Controllers/` - Feature-based controllers (auth, settings, etc.)
- `/app/Http/Middleware/` - Custom middleware for Inertia requests and appearance
- `/app/Models/` - Eloquent models (minimal in starter kit)
- `/config/` - Laravel configuration files
- `/database/` - Migrations, factories, and seeders
- `/routes/` - Web routes (API routes can be added as needed)
- `/tests/` - Pest PHP tests (Feature and Unit)

**Frontend (Vue.js):**
- `/resources/js/pages/` - Vue page components organized by feature:
  - `auth/` - Authentication pages (login, register, password reset)
  - `settings/` - User settings pages (profile, password, 2FA, appearance)
  - `Dashboard.vue` - Main dashboard
  - `Welcome.vue` - Welcome/onboarding page
- `/resources/js/layouts/` - Layout components (AppLayout, AuthLayout)
- `/resources/js/components/` - Reusable Vue components
- `/resources/js/composables/` - Vue composables for reusable logic
- `/resources/js/types/` - TypeScript type definitions

### UI/UX Architecture
- **shadcn-vue**: Modern component library with TailwindCSS variants
- **Design System**: Centralized design tokens in `/resources/js/lib/tailwind.ts`
- **Dark/Light Mode**: Built-in theme switching with CSS variables and `useTheme` composable
- **Responsive Design**: Mobile-first approach with TailwindCSS

### Authentication System
- **Laravel Fortify**: Headless authentication backend
- **Complete 2FA**: Two-factor authentication with backup codes
- **Profile Management**: User settings including appearance preferences
- **Session Management**: Proper session handling with Laravel

## Development Environment Setup

1. **Initial Setup:**
   ```bash
   composer setup  # One-command setup (installs deps, .env, migrate, build)
   ```

2. **Manual Setup (if needed):**
   ```bash
   composer install && npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   npm run build
   ```

3. **Start Development:**
   ```bash
   composer dev    # Recommended: Starts all services with hot reload
   # OR separate:
   php artisan serve    # Terminal 1: Laravel server
   npm run dev         # Terminal 2: Vite dev server
   ```

## Key Configuration Files

- `vite.config.ts` - Vite configuration with Laravel and Vue plugins
- `tsconfig.json` - TypeScript configuration for Vue 3
- `components.json` - shadcn-vue component configuration
- `.prettierrc` - Code formatting rules
- `eslint.config.js` - JavaScript/TypeScript linting
- `config/inertia.php` - Inertia.js specific settings
- `config/fortify.php` - Authentication configuration

## Development Features

- **Hot Module Replacement**: Instant frontend updates during development
- **Inertia DevTools**: Browser extension available for debugging Inertia requests
- **Pail Logging**: Real-time Laravel logging in development (`php artisan pail`)
- **Queue Monitoring**: Background job processing during development
- **SSR Hot Reload**: Server-side rendering updates in development mode
- **Type Safety**: Full TypeScript support with Vue 3

## Code Patterns & Conventions

- **Component Organization**: Pages use `definePageComponent` from Inertia
- **Type Safety**: TypeScript interfaces in `/resources/js/types/`
- **Styling**: Utility-first with TailwindCSS, component variants with CVAs
- **State Management**: Vue composables pattern for reusable logic
- **Form Handling**: Laravel Form Requests with Vue frontend validation
- **Error Handling**: Proper error pages and validation feedback

## Testing

- **PHP Tests**: Use Pest PHP framework in `/tests/`
- **Test Commands**: `composer test` or `php artisan test`
- **Test Organization**: Feature tests for full functionality, Unit tests for isolation

## Build Process

- **Development**: `npm run dev` for hot reload
- **Production**: `npm run build` for optimized assets
- **SSR**: `npm run build:ssr` for server-side rendering assets
- **Deployment**: Laravel Mix/Vite handles asset optimization automatically