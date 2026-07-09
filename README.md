# Laravel Coding Test

Short setup and run guide for the backend (Laravel API) and frontend (Vue 3 SPA).

## Prerequisites

- Docker + Docker Compose

## Setup & Run

1. From project root, start all services:
   - `docker compose up -d --build`
2. Run the backend setup script inside the API container:
   - `docker exec -it api sh -lc "sh /var/www/.docker/setup.sh"`
2. Run DB migrations + seed demo data:
   - `docker exec -it api sh -lc "cd /var/www/backend && php artisan migrate --seed"`

## App URLs

- Frontend: http://localhost:5173
- Backend API base URL: http://localhost:14080/api

## Login (Seeded Test Users)

All seeded users use the same password:

- Password: `@Aa12345`
- Users:
  - `superuser@example.com` (admin)
  - `manager@example.com` (user)
  - `developer@example.com` (user)
  - `tester@example.com` (user)

## Assumptions / Simplifications

- Local development is Docker-first (API, MySQL, Redis, and frontend are run via `docker compose`).
- Seeder creates fixed demo users and sample tasks for quick manual testing.
- Authentication is token-based login (`/api/login`) using Laravel Sanctum; frontend stores the token in browser local storage.
