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

## Architektura
* **RBAC:** Skastomizowany moduł ról wykorzystujący `RoleVerificationService` (zgodne z SRP i DIP).
* **BPMN & State Machine:** Czysta implementacja wzorca projektowego *State*. Każdy status dokumentu (`draft`, `pending`, `approved`, `rejected`) to osobna klasa z hermetyzowanymi regułami przejść. Zintegrowane z Eloquent przy użyciu mechanizmu *Custom Casts*.
* **Repository Pattern:** Abstrakcja bazy danych (`DocumentRepositoryInterface`), ułatwiająca testowanie (mockowanie) oraz zmiany silnika bazodanowego.
* **Audit Log:** Event-Driven Architecture gwarantuje automatyczny zapis każdego przejścia w cyklu życia dokumentu (Event `DocumentStateChanged`).

## Architektura UI (Frontend)
* **Stack:** Vue 3 (Composition API), Tailwind CSS.
* **Integracja:** Inertia.js (Nowoczesny Monolit) pozwala na korzystanie z Vue bez tworzenia oddzielnego API, renderując komponenty z poziomu kontrolerów Laravela.
* **Wzorce:** Komponenty podzielone na warstwy (Layout, Pages). Dynamiczne renderowanie akcji (przyciski Workflow) jest ściśle sprzężone z wyliczonymi uprawnieniami (RBAC) przekazywanymi przez klasę `HandleInertiaRequests`.
