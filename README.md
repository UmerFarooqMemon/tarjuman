<p align="center">
  <a href="https://tarjuman.ae" target="_blank" rel="noopener">
    <img src="public/assets/img/branding/default-logo.svg" width="280" alt="Tarjuman">
  </a>
</p>

<p align="center">
  <strong>Tarjuman</strong> — bilingual translation marketplace platform<br>
  Admin · Vendor portal · Customer API · CMS for the marketing site
</p>

---

## About Tarjuman

Tarjuman is a Laravel-based platform for certified / professional translation workflows:

- **Admin** — platform settings, vendors, pricing, orders (including manual assignment), CMS, notifications
- **Vendor portal** — discover/accept jobs (marketplace mode), confirm amounts, upload deliveries, complete orders
- **Customer API** — estimates, checkout, payments, enterprise auth (JWT)
- **Marketing CMS** — page sections served to a Next.js frontend

Locales: **English** and **Arabic** (admin/vendor UI via `mcamara/laravel-localization`).

---

## Requirements

| Tool | Version / notes |
|------|------------------|
| PHP | **8.3+** with extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `gd` (image watermarks), `zip` (DOCX) |
| Composer | 2.x |
| Database | MySQL / MariaDB (or SQLite for quick local) |
| Node.js | Optional (only if you build frontend assets beyond committed `public/assets`) |
| Tesseract OCR | Optional but recommended for PDF/image word counts (`ESTIMATION_OCR_ENABLED`) |

---

## Quick setup

```bash
# 1. Clone & install PHP dependencies
git clone <repo-url> tarjuman
cd tarjuman
composer install

# 2. Environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# 3. Configure .env (minimum)
# APP_NAME=Tarjuman
# APP_URL=http://tarjuman.test
# DB_* …
# API_TOKEN=<long-random-secret>
# CORS_ALLOWED_ORIGINS=http://localhost:3000
# FRONTEND_URL=http://localhost:3000
# JWT_SECRET is set by jwt:secret

# 4. Database
php artisan migrate --seed

# 5. Storage link (uploads / branding)
php artisan storage:link

# 6. Run
php artisan serve
# or use Laravel Herd / Valet (e.g. https://tarjuman.test)
```

### Useful URLs (local)

| App | URL pattern |
|-----|-------------|
| Admin | `{APP_URL}/en/admin` or `{APP_URL}/ar/admin` |
| Vendor | `{APP_URL}/en/vendor` or `{APP_URL}/ar/vendor` |
| API | `{APP_URL}/api/...` |

Default admin (local seeder):

| Email | Password |
|-------|----------|
| `admin@tarjuman.ae` | `admin@dmin123` |

Change these immediately outside local environments.

### OCR (optional)

Install [Tesseract](https://github.com/tesseract-ocr/tesseract) with language packs you need (`eng`, `ara`, …).

```env
ESTIMATION_OCR_ENABLED=true
TESSERACT_BINARY="C:/Program Files/Tesseract-OCR/tesseract.exe"   # Windows example
TESSERACT_ALWAYS_INCLUDE=eng
```

### Realtime notifications (optional)

Set Pusher (or compatible) keys in `.env` for admin/vendor notification bells:

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
```

---

## Project layout (high level)

```
app/
  Http/Controllers/Admin|Vendor|Api/
  Services/Orders|Estimation|Payments/
  Notifications/
resources/views/admin|vendor/
routes/admin.php, vendor.php, api.php
docs/cms-nextjs-contract.md     # CMS ↔ Next.js contract
docs/cms-nextjs-handoff.md
public/assets/img/branding/     # Default Tarjuman logos
```

---

## API reference

Base URL: `{APP_URL}/api`

### Authentication

Most endpoints require the **platform API token**:

| Header | Value |
|--------|--------|
| `X-API-Token` | `{API_TOKEN}` from `.env` |
| or `Authorization` | `Bearer {API_TOKEN}` |

Customer/enterprise endpoints additionally require a **JWT** from login/register:

| Header | Value |
|--------|--------|
| `Authorization` | `Bearer {jwt}` |

> Note: when both API token and JWT are required, send the **API token** as `X-API-Token` and the **JWT** as `Authorization: Bearer {jwt}` (or follow your client’s dual-header convention). Routes use middleware `api.token` + `auth:api`.

JSON responses typically wrap payloads in `{ "data": … }`. Validation errors use Laravel’s `{ "message", "errors" }` shape.

---

### Catalog & platform (API token)

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/languages` | Active languages |
| `GET` | `/api/document-types` | Active document types |
| `GET` | `/api/authorities` | Authorities |
| `GET` | `/api/add-ons` | Add-ons |
| `GET` | `/api/delivery-speeds` | Delivery speeds |
| `GET` | `/api/plans` | Enterprise plans |
| `GET` | `/api/platform-settings` | Public platform settings (currency, payment/assignment modes, fee %, etc.) |
| `GET` | `/api/cms/pages/{slug}` | CMS page + sections (e.g. `home`) |

Query params (where supported by controllers): locale / filters — see individual controller responses.

---

### Estimation & orders (API token)

#### `POST /api/estimate`

Multipart form upload to analyze documents and return a priced estimate.

| Field | Type | Notes |
|-------|------|--------|
| `documents[]` | file(s) | `pdf`, `docx`, `jpg`, `jpeg`, `png` (see `config/estimation.php`) |
| `document_type_id` | int | Required |
| `source_language_id` | int | Required |
| `target_language_id` | int | Required, ≠ source |
| `delivery_speed_id` | int | Optional |
| `add_on_ids[]` | int[] | Optional |

Returns estimate id/uuid, page/word metrics, line items, totals, currency.

#### `POST /api/orders`

Place an order from an estimate (guest or authenticated customer).

| Field | Type | Notes |
|-------|------|--------|
| `estimate_id` **or** `estimate_uuid` | int / uuid | One required |
| `session_id` | uuid | Optional |
| `first_name`, `last_name`, `email` | string | Required |
| `phone` | string | Optional |
| `pay_with_plan` | bool | Enterprise plan checkout |
| `customer_note` / `note` | string | Optional |
| `documents[]` | file(s) | Required source files at checkout |

Returns `201` with `{ order, payment }` (payment may include gateway redirect / link depending on mode).

#### `GET /api/orders/{orderId}`

Fetch order by public id (e.g. `TRJ-00016`). If a JWT customer is present, they may only view their own orders.

#### `POST /api/orders/payments/{driver}/callback`

Payment gateway return/webhook verification.

| `driver` | |
|----------|--|
| `paytabs` | |
| `tap` | |
| `noon` | |
| `amazon_ps` | |

---

### Auth (API token)

| Method | Path | Description |
|--------|------|-------------|
| `POST` | `/api/auth/register/individual` | Register individual user → JWT |
| `POST` | `/api/auth/register/enterprise` | Register enterprise user → JWT |
| `POST` | `/api/auth/login` | Login (both types) → JWT |

**Individual register body (JSON):** `first_name`, `last_name`, `email`, `phone` (unique), `password`, `password_confirmation`.

**Enterprise register body (JSON):** `first_name`, `last_name`, `email`, `phone` (unique), `company_name`, `expected_volume` (`"1-50"` \| `"51-100"` \| `"100-200"` \| `"200+"` — must be a string), `password`, `password_confirmation`.

CamelCase aliases are also accepted (`firstName`, `expectedVolume`, `companyName`, etc.).

Phone numbers are unique across all customer accounts (one phone → one user).

Example enterprise body:

```json
{
  "first_name": "John",
  "last_name": "Smith",
  "email": "john@company.com",
  "phone": "+971511111111",
  "company_name": "Acme Co",
  "expected_volume": "1-50",
  "password": "Password1!",
  "password_confirmation": "Password1!"
}
```


**Login body (JSON):** `email`, `password` (same endpoint for both account types).

Response includes `token`, `token_type: bearer`, `expires_in`, and `user`. Use `user.type` (`individual` \| `enterprise`) to branch the client UI. Enterprise users also include `company_name` and `expected_volume`.

---

### Authenticated customer (API token + JWT)

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/auth/me` | Current user profile |
| `POST` | `/api/auth/me` | Update profile |

---

### CMS preview (signed URL, no API token)

| Method | Path | Description |
|--------|------|-------------|
| `GET` | `/api/cms/preview/{slug}` | Signed bootstrap for admin iframe preview |

Marketing site integration details: [`docs/cms-nextjs-contract.md`](docs/cms-nextjs-contract.md), [`docs/cms-nextjs-handoff.md`](docs/cms-nextjs-handoff.md).

---

### Example: languages

```bash
curl -s "{APP_URL}/api/languages" \
  -H "X-API-Token: ${API_TOKEN}" \
  -H "Accept: application/json"
```

### Example: estimate

```bash
curl -s -X POST "{APP_URL}/api/estimate" \
  -H "X-API-Token: ${API_TOKEN}" \
  -H "Accept: application/json" \
  -F "documents[]=@./sample.pdf" \
  -F "document_type_id=1" \
  -F "source_language_id=1" \
  -F "target_language_id=2" \
  -F "delivery_speed_id=1"
```

### Example: individual register

```bash
curl -s -X POST "{APP_URL}/api/auth/register/individual" \
  -H "X-API-Token: ${API_TOKEN}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"first_name":"Jane","last_name":"Doe","email":"jane@example.com","phone":"+971500000000","password":"secret123","password_confirmation":"secret123"}'
```

### Example: login (individual or enterprise)

```bash
curl -s -X POST "{APP_URL}/api/auth/login" \
  -H "X-API-Token: ${API_TOKEN}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"jane@example.com","password":"secret123"}'
```

---

## Assignment & payment modes

Configured in **Admin → Platform settings**:

| Mode | Behavior |
|------|----------|
| Assignment **manual** | Admins assign vendors on the order screen / list |
| Assignment **open** (`open`) | Vendors discover & accept open jobs |
| Payment **quick** | Pay at checkout before work |
| Payment **later** | Vendor confirms amount, then customer pays |

Orders snapshot the modes at placement time.

---

## Tests

```bash
php artisan test
```

---

## License

Proprietary / project license — replace this section with your organization’s terms as needed.
