# DocFlow - Business Process & Document Approval System

DocFlow to nowoczesna aplikacja webowa typu monolit (Laravel + Vue 3 + Inertia.js), służąca do elektronicznego obiegu dokumentów (EOD). System został zaprojektowany z myślą o skalowalności i architekturze zorientowanej na domeny (DDD), z rygorystycznym przestrzeganiem zasad SOLID.

## Wymagania
- Docker & Docker Compose v2 (`docker compose`)
- PHP 8.2+ (do lokalnego testowania, opcjonalnie)
- Node.js (do modyfikacji frontendu poza kontenerem, opcjonalnie)

## Szybki start (Środowisko Docker)
1. Klonowanie repozytorium: `git clone <url> && cd docflow`
2. Przygotowanie konfiguracji: `cp .env.example .env` (upewnij się, że używasz połączeń z Dockera: `DB_HOST=db`, `REDIS_HOST=redis`).
3. Uruchomienie kontenerów: `docker compose up -d --build`
4. Inicjalizacja aplikacji (wewnątrz kontenera lub bezpośrednio przez compose):
   ```bash
   docker compose exec app composer install
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate
   docker compose exec app php artisan db:seed
   docker compose exec app npm install
   docker compose exec app npm run build
   docker compose exec app chown -R www-data:www-data storage bootstrap/cache
   docker compose exec app chmod -R 777 storage bootstrap/cache
   ```
   *Lub wejdź do powłoki kontenera:*
   ```bash
   docker compose exec app bash
   ```
5. Aplikacja jest dostępna pod adresem: `http://localhost:8000`

### Dane dostępowe (Użytkownicy z seedera, hasło: `password`)

Aby zasilić bazę kontami demonstracyjnymi oraz rolami (lub je uzupełnić):
```bash
docker compose exec app php artisan db:seed
```
> [!TIP]
> Jeśli chcesz całkowicie wyczyścić i ponownie zainicjalizować bazę danych od zera wraz z seedami, użyj:
> `docker compose exec app php artisan migrate:fresh --seed`

Dostępne konta testowe:
- **Admin:** `admin@docflow.local` (Dostęp globalny)
- **Manager:** `manager@docflow.local` (Zatwierdzanie dokumentów)
- **Employee:** `employee@docflow.local` (Wgrywanie dokumentów)

---

## BPMN Workflow & State Machine
System oparty jest na klasycznym wzorcu projektowym **State**. Cykl życia dokumentu przechodzi przez rygorystycznie zdefiniowane ścieżki. Zmiana stanu z pominięciem reguł rzuca wyjątek `InvalidArgumentException`.

### Dostępne Stany i Przejścia:
1. **Draft (Szkic)**
    - *Początkowy stan po wgraniu dokumentu.*
    - **Akcje:** `submit(?User $actor = null)` -> Przejście w **Pending**.
2. **Pending (Oczekujący)**
    - *Stan weryfikacji. Oczekuje na decyzję Managera.*
    - **Akcje:**
        - `approve(?User $actor = null)` -> Przejście w **Approved**.
        - `reject(?User $actor = null)` -> Przejście w **Rejected**.
3. **Approved (Zaakceptowany)** / **Rejected (Odrzucony)**
    - *Stany końcowe. Brak dalszych dozwolonych akcji.*

*(Każda transformacja automatycznie tworzy wpis w tabeli `document_audit_logs` dzięki Architekturze Event-Driven. Logika domenowa jest całkowicie odseparowana od globalnej sesji HTTP i przyjmuje jawny kontekst aktora).*

---

## Architektura i Wzorce (SOLID)
- **SRP & DIP:** Walidacja ról w `RoleVerificationService`. Enkapsulacja reguł walidacji wejściowej i autoryzacji w klasach Form Request (`StoreDocumentRequest`). Operacje dyskowe za interfejsem `FileUploaderInterface`.
- **Domain Decoupling:** Warstwa domenowa (`DocumentState`) jest niezależna od protokołu HTTP i globalnych sesji – przyjmuje opcjonalną instancję aktora (`?User $actor`), co umożliwia uruchamianie tranzycji z poziomu CLI, Jobów czy Seederów.
- **Repository Pattern:** Komunikacja z bazą danych abstrahowana za pomocą `DocumentRepositoryInterface`.
- **RBAC & Bezpieczeństwo (Anti-IDOR):** Autoryzacja zasobowa oparta na `DocumentPolicy` oraz `CheckRole` Middleware. Pracownicy mają dostęp wyłącznie do własnych dokumentów oraz ich załączników (`/documents/{document}/download`), natomiast Managerowie i Administratorzy uzyskują wgląd do dokumentów wymagających akceptacji.
- **Asynchroniczność:** Powiadomienia email (Joby `SendDocumentStatusNotification`) procesowane w tle dzięki Redis Queues oraz rejestrowanie historii zmian w `DocumentAuditLog`.
- **API First (Ingestion):** Zewnętrzny interfejs API (`/api/documents`) chroniony przez Laravel Sanctum, korzystający ze wspólnych serwisów domenowych i repozytorium.

---

## REST API (Sanctum)

Aplikacja udostępnia zabezpieczone punkty końcowe REST API (`auth:sanctum`):

| Metoda | Endpoint | Opis |
|---|---|---|
| `GET` | `/api/user` | Pobiera dane zalogowanego użytkownika / tokena |
| `POST` | `/api/documents` | Importuje plik i tworzy dokument ze statusem `draft` (multipart/form-data: `title`, `file`) |

---

## Testy i Jakość Kodu

Uruchamianie testów w kontenerze Docker:
```bash
docker compose exec app php artisan test
```

Uruchamianie analizy statycznej PHPStan (Level 8):
```bash
docker compose exec app ./vendor/bin/phpstan analyse --level=8 --memory-limit=512M
```

Projekt posiada zintegrowany potok GitHub Actions analizujący kod za pomocą **PHPStan (Level 8)** oraz weryfikujący poprawność maszyny stanów, reguł Policy, asynchronicznych Jobów/Mailingów, logów audytowych oraz API za pomocą testów PHPUnit przy każdym Push/PR.
