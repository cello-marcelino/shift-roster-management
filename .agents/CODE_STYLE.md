---
tags: [rules, engineering, code-style, standards, naming]
aliases: [Code Style & Standards, Coding Guidelines, Clean Code Standard]
related: ["[[PRINCIPLES]]", "[[ARCHITECTURE]]", "[[AGENTS]]"]
---

# Code Style & Standards

Standar penulisan kode, penamaan, dan kebersihan fungsi. Berlaku untuk seluruh kode yang ditulis atau dimodifikasi pada client maupun server.

---

## 1. Naming Conventions

Gunakan nama yang **deskriptif**, **konsisten**, dan **domain-specific**.

| ❌ Hindari (Ambigu / Terlalu Singkat) | ✅ Gunakan (Deskriptif & Eksplisit) |
|---|---|
| `data`, `temp`, `obj`, `x`, `res` | `user_profile`, `score_result`, `auth_payload` |
| `process()`, `handle()`, `doTask()` | `calculate_score()`, `validate_order_input()` |
| `Manager`, `Helper`, `Utils` (God classes) | `RecommendationService`, `TaxCalculator` |

---

## 2. Functions (Kecil & Terfokus)

- **Satu Fungsi = Satu Tugas Utama**: Fungsi harus berfokus pada satu tanggung jawab (*Single Responsibility*).
- **Batasan Parameter**: Idealnya maksimal 3–4 parameter. Jika lebih, bungkus dalam satu objek DTO/interface.
- **Tanpa Efek Samping Tersembunyi (*No Hidden Side Effects*)**: Fungsi seperti `get_user()` tidak boleh diam-diam mengupdate database, memancarkan event, atau mengirim email notifikasi.

```python
# ❌ Terlalu banyak tanggung jawab dalam satu fungsi
def process_user(user_id):
    user = db.find(user_id)
    validate(user)
    score = calculate(user)
    send_email(user, score)
    log(score)
    return format_response(score)

# ✅ Dipisah per tanggung jawab jelas
user = find_user(user_id)
validate_user(user)
score = calculate_score(user)
```

---

## 3. Type Safety

- Tipe data, interface, dan DTO wajib didefinisikan secara **eksplisit**.
- **Hindari penggunaan `any`** (TypeScript) atau generic type longgar tanpa alasan kuat.

---

## 4. Magic Values

Ekstrak angka atau string konstan yang memiliki arti khusus ke konstanta bernama (`UPPER_SNAKE_CASE`):

```python
# ❌ Magic value membingungkan
if score > 0.75:
    promote_candidate()

# ✅ Konstanta deskriptif
PASSING_SCORE_THRESHOLD = 0.75
if score > PASSING_SCORE_THRESHOLD:
    promote_candidate()
```

---

## 5. Komentar Kode

Komentar ditulis untuk menjelaskan **"KENAPA"** (*rationale*, *trade-off*, keputusan arsitektural), bukan mengulang **"APA"** yang sudah jelas terbaca dari baris kode:

```python
# ❌ Mengulang apa yang dilakukan kode
# increment counter by 1
counter += 1

# ✅ Menjelaskan alasan & konteks
# Reset setiap 100 iterasi untuk mencegah memory leak pada pemrosesan batch besar
if counter >= MAX_BATCH_SIZE:
    counter = 0
```

---

## 6. Operasi Asinkron (Async Operations)

- Tangani operasi async dengan pola yang **konsisten** (`async`/`await`).
- Error propagation harus jelas dan terprediksi — **dilarang melakukan *silent-catch*** (menangkap exception tanpa logging atau penanganan).

---

## 7. Structured Logging

- Gunakan structured logging berkonteks, bukan `console.log`, `print()`, atau `var_dump()` ad-hoc yang tertinggal.
- **Proteksi Data Sensitif**: Dilarang keras mencatat password, hash token, API key rahasia, atau data pribadi (PII) ke dalam log (lihat [[API_RULES]]).

---

## 8. Secrets & API Keys (Zero Hardcoded Credentials)

> **ATURAN KERAS**: **JANGAN PERNAH** mencantumkan API key, token otentikasi, secret, atau kredensial langsung di dalam kode sumber (*hardcoded*).

```javascript
// ❌ DILARANG KERAS (Hardcoded Secret)
const OPENAI_API_KEY = "sk-proj-98f2b3c104e2...";
const STRIPE_SECRET = "sk_live_51Hz...";

// ✅ WAJIB: Baca selalu dari Environment Variables (.env)
const apiKey = process.env.OPENAI_API_KEY; // Node.js / Server
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL; // Client (Vite)
```

- **File `.env`**: Wajib masuk ke `.gitignore`, tidak pernah di-commit ke repositori.
- **File `.env.example`**: Wajib disediakan sebagai template variabel konfigurasi dengan nilai placeholder dummy.
