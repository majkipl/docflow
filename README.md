# DocFlow - Business Process & Document Approval System

## Wymagania
* Docker & Docker Compose
* Node.js (opcjonalnie do dewelopmentu lokalnego poza kontenerem)

## Setup (Instalacja)
1. Klonowanie repozytorium
2. `cp .env.example .env` (upewnij się, że używasz bazy `pgsql` i kolejki `redis`)
3. `docker-compose up -d --build`
4. Wewnątrz kontenera aplikacji (`docker-compose exec app bash`):
    * `composer install`
    * `php artisan key:generate`
    * `php artisan migrate --seed` (zainicjuje tabele i doda użytkowników testowych)
    * `npm install && npm run build` (w celu kompilacji Vue+Tailwind)

## Logowanie testowe (Hasło: `password`)
* admin@docflow.local
* manager@docflow.local
* employee@docflow.local

## Architektura (do tej pory)
* **RBAC:** Skastomizowany moduł ról wykorzystujący `RoleVerificationService` (zgodne z SRP i DIP).
* **Frontend:** Vue 3, Tailwind CSS, Inertia.js.
