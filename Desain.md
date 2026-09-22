# Product Manager — Technical Blueprint

Dokumen ini merupakan **technical blueprint** yang menjadi **single source of truth** dan acuan teknis pengembangan **Product Manager**. Tujuannya adalah memastikan arsitektur sistem tetap modular, memiliki **separation of concerns** yang tegas, performa **lean**, serta menerapkan standar **security-by-default** tanpa **boilerplate** atau **over-engineering**.

---

## 1. Context & Engineering Value

### 1.1 Problem Statement
Aplikasi inventaris internal sering kali terjebak di dua kutub ekstrem:
- **Under-engineered**: Terlalu sederhana hingga mengabaikan aspek keamanan fundamental (tanpa validasi, rentan SQL Injection, XSS, dan CSRF).
- **Over-engineered & Bloated**: Terlalu banyak memasang **third-party dependencies** dan framework raksasa yang tidak esensial, memicu **performance overhead** serta memperbesar **attack surface**.
- **Environment Discrepancy ("Works on My Machine Syndrome")**: Aplikasi berjalan normal di satu laptop pengembang, tetapi mengalami **breaking errors** saat dideploy di mesin lain akibat perbedaan versi PHP, ekstensi database, atau konfigurasi lokal OS (misal: Fedora vs Ubuntu vs Windows).

### 1.2 Solution Approach
Product Manager menerapkan pendekatan rekayasa yang pragmatis, modern, dan terukur:
1. **Full Environment Parity**: Sistem dikontainerisasi penuh menggunakan Docker. Runtime environment (PHP 8.2 Apache + MySQL 8.0) dipastikan identik di berbagai OS tanpa perlu setup web server lokal manual.
2. **Lean & High-Performance Architecture**: Mengoptimalkan kemampuan PHP 8.2 native dengan **clean pattern** tanpa framework berat. Ringan, eksekusi instan, dan mudah di-audit.
3. **Modern Visual Hierarchy**: Desain **dark mode** elegan dengan Bootstrap 5.3 yang di-enhance melalui lapisan **surgical CSS** untuk menghindari fenomena **utility class soup**.
4. **Security by Default**: Pertahanan berlapis terhadap ancaman umum diintegrasikan langsung pada arsitektur inti.

---

## 2. Software Engineering Principles

| Principle | Technical Implementation |
|---|---|
| **KISS (Keep It Simple, Stupid)** | Menghindari **premature abstraction**. Kombinasi native PHP 8.2 dan PDO sudah sangat optimal untuk menangani **domain logic** CRUD inventaris tanpa memerlukan layer ORM yang kompleks. |
| **YAGNI (You Aren't Gonna Need It)** | Mengimplementasikan fitur dan modul yang secara nyata dibutuhkan saat ini. Menolak **boilerplate code** untuk use-case hipotetis yang belum tentu ada. |
| **DRY (Don't Repeat Yourself)** | **Bootstrapping** sesi, **database connection singleton**, registrasi **security helpers**, serta **shared layout** dipusatkan pada satu titik masuk untuk mengeliminasi duplikasi kode. |
| **Separation of Concerns (SoC / SRP)** | **Single Responsibility Principle**: Controller murni menangani **request orchestration** dan validasi; View murni menangani **HTML presentation**; Helper menangani **pure utility functions**. |

---

## 3. Front Controller Architecture & Request Lifecycle

Aplikasi meninggalkan pola **scattered multi-page routing** dan beralih ke arsitektur **Front Controller (Single Entry Point)**.

### 3.1 Request-Response Lifecycle
Satu-satunya **public entry point** yang terekspos ke klien adalah `src/index.php`. Berkas controller di dalam direktori `src/controllers/` diisolasi di balik mekanisme **whitelist routing** untuk mencegah akses rute ilegal (**arbitrary file execution**).

```text
Client Browser
     │
     ▼ [HTTP Request: index.php?action=...]
┌────────────────────────────────────────────────────────┐
│ src/index.php (Front Controller / Router)              │
│  ├─ Session Bootstrap (session_start)                  │
│  ├─ Database Connection (PDO Singleton)                │
│  ├─ Security & Utility Helpers (h, csrf, validation)   │
│  └─ Route Dispatcher (Strict Whitelist Check)          │
└──────────────────────────┬─────────────────────────────┘
                           │
       ┌───────────────────┼───────────────────┐
       ▼                   ▼                   ▼
[controllers/read]   [controllers/create]  [controllers/edit & delete]
       │                   │                   │
       ▼                   ▼                   ▼
[views/read.view]    [views/create.view]   [views/edit.view] / PRG Redirect
```

### 3.2 Post-Redirect-Get (PRG) Pattern
Untuk mengeliminasi **duplicate form submission** saat pengguna melakukan **accidental page refresh** setelah mutasi data:
1. Klien mengirim data formulir melalui HTTP request `POST`.
2. Controller memvalidasi token CSRF dan melakukan **server-side validation**.
3. Setelah operasi persistensi database selesai, status notifikasi disimpan ke dalam **session flash storage**.
4. Controller menerbitkan response `HTTP 302 Found` **Redirect** kembali ke `index.php`.
5. Browser klien mengeksekusi request `GET` yang aman **idempotent** untuk membaca ulang data katalog beserta **consumed flash message**.

---

## 4. Data Modeling & Business Rules

### 4.1 Relational Database Schema (MySQL)
Tabel inventaris dirancang ramping dengan kendala integritas data **data integrity constraints** yang ditegakkan langsung di level **database engine**:

```sql
CREATE TABLE IF NOT EXISTS products (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(23)      NOT NULL,
    category   VARCHAR(100)     NOT NULL,
    price      DECIMAL(12, 2)   NOT NULL CHECK (price > 0),
    stock      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_name  UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 Server-Side Validation Matrix
Semua input divalidasi secara komprehensif pada controller sebelum menyentuh layer **persistence*:

| Parameter | Validation Constraints | Error Handling & UX Behavior |
|---|---|---|
| **Product Name** | Wajib diisi, panjang 3–23 karakter, unik di seluruh basis data. | Melempar pesan error deskriptif, mempertahankan input sebelumnya **sticky form**. |
| **Category** | Wajib diisi, maksimal 100 karakter, non-empty whitespace. | Menolak input kosong atau yang hanya memuat spasi. |
| **Price** | Tipe data numerik dan strictly bernilai lebih besar dari 0 (`price > 0`). | Menolak nilai negatif, angka nol, dan format non-numerik. |
| **Stock** | Integer non-negatif (`ctype_digit`) dalam rentang 0 hingga 20 unit. | Menolak nilai desimal, nilai minus, atau angka yang melebihi batas kapasitas 20 unit. |

---

## 5. Layered Security Strategy (Defense-in-Depth)

Arsitektur keamanan dirancang secara proaktif pada setiap **layer** interaksi:

1. **SQL Injection Mitigation**:
   Semua query dieksekusi menggunakan **PDO Prepared Statements** dengan **parameter binding** terikat (`:name`, `:price`, `:id`). Emulasi prepared statement dimatikan secara eksplisit (`PDO::ATTR_EMULATE_PREPARES => false`) agar parsing SQL sepenuhnya ditangani oleh database engine secara **native**.
2. **Context-Aware XSS Prevention**:
   Tidak ada data dinamis yang dirender langsung ke browser tanpa sanitasi. Fungsi helper `h($value)` menerapkan `htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')` pada seluruh tag template view.
3. **CSRF Protection (Synchronizer Token Pattern)**:
   Setiap sesi pengguna menghasilkan **cryptographically secure token** 32-byte (`random_bytes`). Setiap mutasi data berbasis `POST` (termasuk penghapusan) wajib menyertakan token yang divalidasi menggunakan `hash_equals()` untuk mencegah **timing attacks**.
4. **Routing Whitelist & Path Traversal Prevention**:
   Front Controller memvalidasi parameter rute terhadap **whitelist array**. Rute yang tidak terdaftar otomatis memicu **fallback** response **HTTP 404** tanpa mengekspos struktur direktori internal.

---

## 6. Containerization & Deployment Runtime

Lingkungan aplikasi dikelola secara deklaratif melalui Docker Compose untuk memastikan proses **onboarding** dan **deployment** berlangsung instan:
- **Web Runtime (`app`)**: Menjalankan Apache 2.4 dan PHP 8.2 dengan ekstensi `pdo_mysql` aktif.
- **Database Runtime (`db`)**: Menjalankan MySQL 8.0 dengan **named volume persistence** (`db_data`) agar data tidak hilang saat kontainer di-**recreate**.
- **Zero-Friction Startup**: Pengembang cukup mengeksekusi `docker compose up -d` untuk menjalankan seluruh **stack** aplikasi di port `8080` tanpa ketergantungan konfigurasi lokal pada OS host.
