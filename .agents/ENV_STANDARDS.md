---
tags: [rules, environment-variables, fullstack, client, server, security]
aliases: [Environment Standards, Env Variables Specification, Env Item Standards]
related: ["[[AGENTS]]", "[[API_RULES]]", "[[CODE_STYLE]]"]
---

# Environment Variables Standards (.env)

Standar baku tata kelola, penamaan, dan daftar item variabel lingkungan (*environment variables*) untuk proyek **Fullstack**, **Frontend (`client/`)**, dan **Backend (`server/`)**.

---

## 1. Aturan Mutlak Pengelolaan `.env` (P0)

1. **Format Penamaan**: Semua nama variabel **WAJIB** menggunakan format **`UPPER_SNAKE_CASE`** (contoh: `DB_PASSWORD`, `VITE_API_BASE_URL`).
2. **Dilarang Commit**: File `.env`, `.env.local`, `.env.production` **DILARANG KERAS** di-commit ke Git. Wajib masuk ke `.gitignore`.
3. **Wajib `.env.example`**: Setiap modul wajib menyediakan file `.env.example` yang mencantumkan seluruh variabel yang dibutuhkan dengan **nilai placeholder dummy**, bukan nilai rahasia asli.
4. **Isolasi Client vs Server**:
   - Variabel di `client/` hanya bersifat publik.
   - **DILARANG KERAS** menaruh secret server (Stripe Secret Key, Database Password, Private API Keys) di `client/` karena seluruh bundle frontend dapat dibaca publik melalui *inspect element* browser.

---

## 2. Standard Item Frontend (`client/.env.example`)

Khusus frontend yang menggunakan **Vite**, seluruh variabel yang boleh diakses oleh kode antarmuka **WAJIB** menggunakan prefix **`VITE_`**:

```bash
# ==============================================================================
# CLIENT ENVIRONMENT VARIABLES (Vite Frontend)
# File: client/.env.example
# ==============================================================================

# Application Meta
VITE_APP_NAME="Antigravity App"
VITE_APP_ENV="development"              # development | staging | production
VITE_APP_VERSION="1.0.0"

# Backend API Configuration
VITE_API_BASE_URL="http://localhost:5000/api/v1"
VITE_API_TIMEOUT_MS=15000               # Batas waktu timeout fetch (milidetik)

# Public Third-Party Services (Client-Safe ONLY)
VITE_SENTRY_DSN=""                      # Monitoring error client
VITE_GA_TRACKING_ID=""                  # Google Analytics ID
```

> **Cara Memanggil di Client**:
> ```typescript
> const apiBaseUrl = import.meta.env.VITE_API_BASE_URL;
> ```

---

## 3. Standard Item Backend (`server/.env.example`)

Daftar baku konfigurasi server, basis data, autentikasi, dan integrasi eksternal:

```bash
# ==============================================================================
# SERVER ENVIRONMENT VARIABLES (Backend API)
# File: server/.env.example
# ==============================================================================

# --- [1. Application & Server Runtime] ---
NODE_ENV="development"                  # development | staging | production
PORT=5000
HOST="0.0.0.0"
APP_NAME="Core API Server"
APP_URL="http://localhost:5000"

# --- [2. Security & CORS] ---
# URL client yang diizinkan (pisahkan dengan koma jika multiple)
CORS_ORIGIN="http://localhost:5173"
RATE_LIMIT_WINDOW_MS=900000             # Jendela waktu rate limit (15 menit = 900.000 ms)
RATE_LIMIT_MAX_REQUESTS=100             # Maksimum request per window per IP

# --- [3. Authentication & JWT] ---
# Buat string acak kuat minimal 32 karakter
JWT_SECRET="your-super-secret-jwt-key-min-32-chars"
JWT_EXPIRES_IN="1d"                     # Durasi akses token (misal: 15m, 1h, 1d)
JWT_REFRESH_SECRET="your-super-secret-refresh-key-min-32-chars"
JWT_REFRESH_EXPIRES_IN="7d"             # Durasi refresh token (misal: 7d, 30d)

# --- [4. Database Persistence] ---
DB_CONNECTION="postgresql"              # postgresql | mysql | sqlite
DB_HOST="localhost"
DB_PORT=5432
DB_DATABASE="app_db"
DB_USERNAME="postgres"
DB_PASSWORD="your_secure_password"
DB_SSL=false                            # true di staging/production

# --- [5. Cache & In-Memory Store (Redis)] ---
REDIS_HOST="localhost"
REDIS_PORT=6379
REDIS_PASSWORD=""
REDIS_DB=0

# --- [6. AI / Machine Learning & External APIs] ---
# API key pihak ketiga yang hanya boleh dipanggil dari server
OPENAI_API_KEY=""
GEMINI_API_KEY=""
AI_MODEL_NAME="gemini-1.5-flash"
AI_REQUEST_TIMEOUT_MS=30000

# --- [7. Logging & Observability] ---
LOG_LEVEL="debug"                       # debug | info | warn | error
```

> **Cara Memanggil di Server**:
> ```typescript
> const port = process.env.PORT || 5000;
> const jwtSecret = process.env.JWT_SECRET;
> ```

---

## 4. Standard Item Fullstack / Root Monorepo (`.env.example`)

Untuk proyek monorepo yang dijalankan bersamaan menggunakan Docker Compose atau container orchestration:

```bash
# ==============================================================================
# FULLSTACK ROOT ORCHESTRATION
# File: .env.example (Root)
# ==============================================================================

PROJECT_NAME="my-fullstack-app"
ENVIRONMENT="development"

# Port Mapping (Host Machine)
CLIENT_PORT=5173
SERVER_PORT=5000
DATABASE_PORT=5432
REDIS_PORT=6379

# Shared Database Credentials for Docker
POSTGRES_DB="app_db"
POSTGRES_USER="postgres"
POSTGRES_PASSWORD="secure_root_password"
```

---

## 5. Validasi Skema Environment Saat Server Start

Untuk mencegah error runtime di tengah jalan (*silent failures*), **WAJIB** memvalidasi keberadaan seluruh variabel lingkungan saat server pertama kali dinyalakan (misal menggunakan library Zod, Joi, atau Envalid):

```typescript
// Contoh Validasi Server Environment Menggunakan Zod
import { z } from 'zod';

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'staging', 'production']).default('development'),
  PORT: z.coerce.number().default(5000),
  JWT_SECRET: z.string().min(32, "JWT_SECRET minimal 32 karakter!"),
  DB_HOST: z.string(),
  DB_PASSWORD: z.string(),
  CORS_ORIGIN: z.string(),
});

export const env = envSchema.parse(process.env);
```
> **Aturan**: Jika salah satu variabel wajib tidak ditemukan di file `.env`, aplikasi **harus langsung berhenti (crash immediately)** disertai pesan error yang jelas, bukan berjalan dengan nilai `undefined`.

---

## 6. Checklist Verifikasi Environment

- [ ] File `.env` sudah masuk ke `.gitignore` di root, `client/`, dan `server/`.
- [ ] File `.env.example` tersedia dan selalu diperbarui saat menambah variabel baru.
- [ ] Tidak ada prefix `VITE_` yang menyimpan secret sensitif server.
- [ ] Variabel wajib di server memiliki skema validasi saat startup.
- [ ] Nilai secret produksi (*production keys*) tidak pernah menggunakan nilai default/dummy lokal.
