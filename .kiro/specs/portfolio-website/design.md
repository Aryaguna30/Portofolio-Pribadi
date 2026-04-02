# Dokumen Desain Teknis: Portfolio Website

## Overview

Website Portofolio Profesional & Dinamis adalah SPA (Single Page Application) yang dibangun dengan arsitektur **Laravel 13 + Inertia.js + Vue.js 3**. Inertia.js berperan sebagai jembatan yang menghilangkan kebutuhan REST API manual — Laravel merender halaman Inertia yang kemudian di-hydrate oleh Vue.js di sisi klien.

Aplikasi terbagi menjadi dua area utama:
- **Public Site**: Halaman portofolio yang dapat diakses publik (single-page dengan smooth scroll)
- **Admin Panel**: CMS berbasis Inertia untuk mengelola seluruh konten secara dinamis

### Tujuan Desain
- Performa tinggi: TTI < 2 detik, gambar WebP, lazy loading, code splitting via Vite
- Keamanan berlapis: CSRF, CSP, session HttpOnly, rate limiting, honeypot, sanitasi input
- Aksesibilitas: ARIA labels, focus ring, Focus_Trap, keyboard navigation
- SEO-ready: Meta tags & OG tags di-inject server-side via Inertia shared data
- Multi-bahasa: vue-i18n dengan fallback ke Bahasa Indonesia; konten dinamis dari DB menggunakan `spatie/laravel-translatable`

---

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser (Client)                        │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Vue.js 3 SPA (Inertia.js Client)                        │   │
│  │  ┌─────────────┐  ┌──────────────┐  ┌────────────────┐  │   │
│  │  │ Public Site │  │ Admin Panel  │  │ Shared Layout  │  │   │
│  │  │ (1 page,    │  │ (multi-page  │  │ (Header, Meta) │  │   │
│  │  │ smooth      │  │  Inertia)    │  │                │  │   │
│  │  │ scroll)     │  │              │  │                │  │   │
│  │  └─────────────┘  └──────────────┘  └────────────────┘  │   │
│  │  vue-i18n | Tailwind CSS | GSAP | DOMPurify | Quill.js   │   │
│  └──────────────────────────────────────────────────────────┘   │
└──────────────────────────┬──────────────────────────────────────┘
                           │ Inertia.js (XHR + JSON)
                           │ CSRF Token pada setiap request
┌──────────────────────────▼──────────────────────────────────────┐
│                    Laravel 13 (Backend)                         │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────┐    │
│  │   Routes     │  │ Controllers  │  │  Form Requests     │    │
│  │  web.php     │  │  (Public +   │  │  (Validation &     │    │
│  │              │  │   Admin)     │  │   Sanitization)    │    │
│  └──────────────┘  └──────────────┘  └────────────────────┘    │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────┐    │
│  │   Models     │  │  Middleware  │  │  Jobs / Queue      │    │
│  │  (Eloquent)  │  │  (Auth,CSRF, │  │  (Notification     │    │
│  │              │  │   CSP, Rate) │  │   Mailer)          │    │
│  └──────────────┘  └──────────────┘  └────────────────────┘    │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────────┐    │
│  │  Laravel     │  │  File        │  │  External APIs     │    │
│  │  Cache       │  │  Storage     │  │  (GitHub/WakaTime) │    │
│  │  (1 jam TTL) │  │  (WebP imgs) │  │  (timeout 3s)      │    │
│  └──────────────┘  └──────────────┘  └────────────────────┘    │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                    Database (MySQL/PostgreSQL)                   │
│  users | projects | messages | timeline_entries | skills        │
│  hero_settings | site_settings                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Request Flow (Inertia.js)

```
Browser                    Laravel                     Vue.js
  │                           │                           │
  │── GET / (full page) ──────▶│                           │
  │                           │── render app.blade.php ──▶│
  │                           │   (dengan initial props)  │
  │◀──────────────────────────│                           │
  │                           │                    hydrate│
  │                           │                    Vue SPA│
  │                           │                           │
  │── navigate (Inertia XHR) ─▶│                           │
  │                           │── return JSON props ──────▶│
  │                           │                    update │
  │                           │                    page   │
```

### Coding Stats Data Flow

```
Vue Component (CodingStats.vue)
  │
  │── mounted() → axios.get('/api/coding-stats')
  │                           │
  │                    Laravel CodingStatsController
  │                           │── Cache::get('coding_stats')
  │                           │   HIT → return cached data
  │                           │   MISS → fetch GitHub/WakaTime API
  │                           │          (timeout: 3s)
  │                           │          store in cache (1 jam)
  │                           │          return data
  │                           │   ERROR → return null/error flag
  │◀── JSON response ─────────│
  │
  │── if error/null → hide component
  │── if data → render progress bars & charts
```

### Queue / Async Mail Flow

```
Contact Form Submit
  │
  │── POST /contact (Inertia)
  │                    Laravel ContactController
  │                           │── validate (FormRequest)
  │                           │── check honeypot
  │                           │── check rate limit
  │                           │── check CAPTCHA
  │                           │── save Message to DB
  │                           │── dispatch(SendContactNotification::class)
  │                           │   (Laravel Queue → async)
  │◀── Inertia redirect ──────│
  │    (success flash)
  │
  │   [Queue Worker]
  │       │── SendContactNotification job
  │       │── Mail::to(admin_email)->send(...)
```

---

## Components and Interfaces

### Struktur Direktori Utama

```
resources/
├── js/
│   ├── app.js                    # Entry point, Inertia setup, i18n, plugins
│   ├── bootstrap.js
│   ├── i18n/
│   │   ├── index.js              # vue-i18n setup
│   │   ├── locales/
│   │   │   ├── id.json           # Teks Bahasa Indonesia
│   │   │   └── en.json           # Teks Bahasa Inggris
│   ├── composables/
│   │   ├── useTheme.js           # Dark/light mode logic + localStorage
│   │   ├── useLanguage.js        # Language toggle + localStorage
│   │   ├── useFocusTrap.js       # Focus trap untuk modal
│   │   └── useIntersectionObserver.js  # Scroll animations
│   ├── layouts/
│   │   ├── PublicLayout.vue      # Layout untuk Public Site
│   │   └── AdminLayout.vue       # Layout untuk Admin Panel
│   ├── pages/
│   │   ├── Home.vue              # Single-page public site
│   │   ├── Auth/
│   │   │   └── Login.vue
│   │   ├── Admin/
│   │   │   ├── Dashboard.vue
│   │   │   ├── LandingPage.vue   # Edit Hero, Timeline, CV, Skills
│   │   │   ├── Projects/
│   │   │   │   ├── Index.vue
│   │   │   │   ├── Create.vue
│   │   │   │   └── Edit.vue
│   │   │   └── Messages/
│   │   │       ├── Index.vue
│   │   │       └── Show.vue
│   │   └── Errors/
│   │       ├── 404.vue
│   │       └── 500.vue
│   └── components/
│       ├── public/
│       │   ├── Header.vue            # Navigasi, Theme/Lang toggle, CV download
│       │   ├── HeroSection.vue       # Typing animation, fade-in, CTA buttons
│       │   ├── AboutSection.vue      # Profil, Timeline, Skills
│       │   ├── PortfolioSection.vue  # Grid proyek
│       │   ├── ProjectCard.vue       # Kartu proyek dengan hover effect
│       │   ├── ProjectModal.vue      # Detail proyek, Focus_Trap
│       │   ├── CodingStats.vue       # GitHub/WakaTime stats
│       │   ├── ContactSection.vue    # Form kontak + social links
│       │   └── Footer.vue
│       ├── admin/
│       │   ├── RichTextEditor.vue    # Wrapper Quill.js
│       │   ├── ThumbnailUpload.vue   # Upload + preview gambar
│       │   └── ConfirmDialog.vue     # Dialog konfirmasi hapus
│       └── shared/
│           ├── MetaManager.vue       # Head meta tags via @inertiajs/vue3 Head
│           ├── ThemeToggle.vue
│           ├── LanguageToggle.vue
│           └── LoadingSpinner.vue

app/
├── Http/
│   ├── Controllers/
│   │   ├── HomeController.php        # Public site pages
│   │   ├── ContactController.php     # Form kontak
│   │   ├── CodingStatsController.php # GitHub/WakaTime proxy + cache
│   │   └── Admin/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── LandingPageController.php
│   │       ├── ProjectController.php
│   │       └── MessageController.php
│   ├── Requests/
│   │   ├── ContactFormRequest.php
│   │   ├── Admin/
│   │   │   ├── ProjectRequest.php
│   │   │   ├── TimelineEntryRequest.php
│   │   │   ├── SkillRequest.php
│   │   │   └── CvUploadRequest.php
│   └── Middleware/
│       ├── HandleInertiaRequests.php  # Share global props (auth, flash, meta)
│       ├── ContentSecurityPolicy.php
│       └── EnsureAdminAuthenticated.php
├── Models/
│   ├── User.php
│   ├── Project.php
│   ├── Message.php
│   ├── TimelineEntry.php
│   ├── Skill.php
│   └── SiteSetting.php
├── Jobs/
│   └── SendContactNotification.php
└── Services/
    ├── CodingStatsService.php    # Fetch & cache GitHub/WakaTime
    ├── FileStorageService.php    # Upload, convert WebP, hapus orphan
    └── MetaTagService.php        # Generate meta/OG tags per halaman
```

### Komponen Vue.js Utama — Interface

#### `Header.vue`
```
Props: { cvUrl: String|null, currentLocale: String, currentTheme: String }
Emits: (tidak ada — menggunakan composables)
Composables: useTheme(), useLanguage()
```

#### `HeroSection.vue`
```
Props: { name: String, professions: Array<String>, description: String, typingSpeed: Number }
Behavior: GSAP fade-in on mount, typing animation loop via setInterval
```

#### `ProjectModal.vue`
```
Props: { project: Project|null, isOpen: Boolean }
Emits: close
Composables: useFocusTrap()
Behavior: DOMPurify.sanitize(project.description) sebelum v-html
Note: project.title dan project.description diterima sebagai string yang sudah di-resolve
      sesuai locale aktif oleh controller (menggunakan HasTranslations dari spatie/laravel-translatable).
      Komponen menggunakan locale aktif dari useLanguage() saat merender props yang diterima.
```

#### `CodingStats.vue`
```
Props: { initialData: Object|null }  # dari server-side props jika tersedia
Data: { stats, loading, error }
Lifecycle: onMounted → fetch /coding-stats (async, timeout 3s)
Behavior: hide component jika error/timeout
```

#### `AboutSection.vue` (Timeline & Skills)
```
Props: { timelineEntries: Array<TimelineEntry>, skills: Array<Skill> }
Note: Field institution, role, dan description pada setiap TimelineEntry diterima sebagai
      string yang sudah di-resolve sesuai locale aktif oleh controller
      (menggunakan HasTranslations dari spatie/laravel-translatable).
      Komponen menggunakan locale aktif dari useLanguage() saat merender props.
```

#### `ContactSection.vue`
```
Props: { socialLinks: Object }
Data: { form, errors, honeypot, submitting, success }
Methods: submit() → Inertia.post('/contact', form)
```

#### `MetaManager.vue` (shared)
```
Props: { title, description, ogImage, ogUrl, keywords }
Uses: <Head> dari @inertiajs/vue3
Renders: <title>, <meta name="description">, OG tags, Twitter Card
```

---

## Data Models

### Skema Database

#### Tabel `users` (sudah ada, diperluas)
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(255)
email           VARCHAR(255) UNIQUE
password        VARCHAR(255)
remember_token  VARCHAR(100) NULLABLE
email_verified_at TIMESTAMP NULLABLE
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Tabel `projects`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
title           JSON NOT NULL              -- {"id": "Aplikasi Kasir", "en": "POS Application"}
description     LONGTEXT NOT NULL          -- JSON: {"id": "<p>...</p>", "en": "<p>...</p>"} (HTML dari Rich Text Editor, sudah disanitasi)
tech_stack      JSON NOT NULL              -- ["Laravel", "Vue.js", "MySQL"]
demo_url        VARCHAR(500) NULLABLE
repo_url        VARCHAR(500) NULLABLE
thumbnail_path  VARCHAR(500) NOT NULL      -- path relatif ke storage (WebP)
is_published    BOOLEAN DEFAULT TRUE
sort_order      INT DEFAULT 0
deleted_at      TIMESTAMP NULLABLE         -- soft delete
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Tabel `messages`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(255) NOT NULL
email           VARCHAR(255) NOT NULL
subject         VARCHAR(255) NOT NULL
body            TEXT NOT NULL              -- sudah disanitasi backend
ip_address      VARCHAR(45) NULLABLE
is_read         BOOLEAN DEFAULT FALSE
read_at         TIMESTAMP NULLABLE
deleted_at      TIMESTAMP NULLABLE         -- soft delete
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Tabel `timeline_entries`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
type            ENUM('education', 'experience') NOT NULL
institution     JSON NOT NULL              -- {"id": "Universitas X", "en": "University X"}
role            JSON NOT NULL              -- {"id": "Teknik Informatika", "en": "Informatics Engineering"}
start_year      YEAR NOT NULL
end_year        YEAR NULLABLE              -- NULL = "sekarang"
description     JSON NULLABLE              -- {"id": "...", "en": "..."}
sort_order      INT DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Tabel `skills`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name            VARCHAR(100) NOT NULL
icon_class      VARCHAR(100) NULLABLE      -- e.g. "devicon-laravel-plain"
category        VARCHAR(100) NULLABLE      -- e.g. "Backend", "Frontend"
sort_order      INT DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### Tabel `site_settings`
```sql
id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
key             VARCHAR(100) UNIQUE NOT NULL
value           LONGTEXT NULLABLE          -- LONGTEXT untuk mendukung JSON dan konten panjang
created_at      TIMESTAMP
updated_at      TIMESTAMP
```
*Digunakan untuk menyimpan: `hero_name`, `hero_professions` (JSON), `hero_description`, `cv_file_path`, `typing_speed`, `og_default_image`, `admin_email`, `social_linkedin`, `social_github`, `social_email`*

*Catatan: Model `SiteSetting` menggunakan accessor/mutator untuk auto-detect dan parse nilai JSON secara otomatis — jika `value` adalah JSON valid, accessor akan mengembalikan array/object; jika bukan, mengembalikan string mentah.*

### Eloquent Models & Relasi

```
User          (tidak ada relasi ke domain lain — single admin)
Project       (standalone; menggunakan HasTranslations dari spatie/laravel-translatable
               untuk kolom: title, description; menggunakan SoftDeletes)
Message       (standalone; menggunakan SoftDeletes)
TimelineEntry (standalone, type enum; menggunakan HasTranslations dari spatie/laravel-translatable
               untuk kolom: institution, role, description)
Skill         (standalone)
SiteSetting   (key-value store; accessor/mutator untuk auto-parse JSON values)
```

### Laravel Routes

```php
// routes/web.php

// ── Public Routes ──────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');
Route::get('/coding-stats', [CodingStatsController::class, 'index'])
    ->middleware('throttle:coding-stats')
    ->name('coding-stats');
Route::get('/cv/download', [HomeController::class, 'downloadCv'])
    ->middleware('throttle:cv-download')
    ->name('cv.download');

// ── Auth Routes ────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'showLogin'])
        ->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');
});
Route::post('/admin/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('admin.logout');

// ── Admin Routes (protected) ───────────────────────────────────
Route::middleware(['auth', EnsureAdminAuthenticated::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Landing Page Management
        Route::get('/landing', [LandingPageController::class, 'index'])
            ->name('landing.index');
        Route::put('/landing/hero', [LandingPageController::class, 'updateHero'])
            ->name('landing.hero.update');
        Route::post('/landing/cv', [LandingPageController::class, 'uploadCv'])
            ->name('landing.cv.upload');
        Route::delete('/landing/cv', [LandingPageController::class, 'deleteCv'])
            ->name('landing.cv.delete');

        // Timeline
        Route::apiResource('timeline', TimelineEntryController::class);

        // Skills
        Route::apiResource('skills', SkillController::class);

        // Projects CRUD
        Route::resource('projects', ProjectController::class);

        // Messages
        Route::get('/messages', [MessageController::class, 'index'])
            ->name('messages.index');
        Route::get('/messages/{message}', [MessageController::class, 'show'])
            ->name('messages.show');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])
            ->name('messages.destroy');
    });
```

### Rate Limiter Configuration

```php
// app/Providers/AppServiceProvider.php
RateLimiter::for('contact', function (Request $request) {
    return Limit::perHour(3)->by($request->ip())
        ->response(fn() => back()->withErrors([
            'rate_limit' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 1 jam.'
        ]));
});

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

RateLimiter::for('coding-stats', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});

RateLimiter::for('cv-download', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*


### Property 1: CV Button State Reflects Storage

*For any* site settings state, if `cv_file_path` is null or empty, then the CV download button rendered in the Header must be disabled and display "CV belum tersedia"; if `cv_file_path` is a non-empty string, the button must be enabled.

**Validates: Requirements 1.4**

---

### Property 2: Theme Preference Round Trip

*For any* theme value (light or dark) set via Theme_Toggle, the value stored in localStorage must equal the theme currently applied to the page, and reading it back must produce the same value.

**Validates: Requirements 1.8**

---

### Property 3: Language Toggle Completeness

*For any* locale key defined in the i18n locale files (id.json or en.json), after switching to that locale, every translated string rendered in the UI must use the corresponding value from that locale file — no key should fall back to a raw key string.

**Validates: Requirements 2.2, 2.3**

---

### Property 4: Language Preference Round Trip

*For any* locale value ('id' or 'en') set via Language_Toggle, the value stored in localStorage must equal the active locale, and reloading the composable state from localStorage must restore the same locale.

**Validates: Requirements 2.4**

---

### Property 5: Typing Animation Sequence Preservation

*For any* non-empty array of profession strings passed to HeroSection, the typing animation must display each string in the same order as the input array, cycling back to the first item after the last.

**Validates: Requirements 3.3**

---

### Property 6: Hero Content Round Trip

*For any* hero settings object (name, professions, description) saved by Admin via the landing page form, the data returned by `HomeController::index` as Inertia props must be equal to the saved values.

**Validates: Requirements 3.6, 10.2**

---

### Property 7: Demo URL Button Visibility

*For any* project object where `demo_url` is null or an empty string, the Project_Modal rendered for that project must not contain a demo link button.

**Validates: Requirements 5.7**

---

### Property 8: Coding Stats Cache Consistency

*For any* successful API response cached under the `coding_stats` key, all subsequent requests within the 1-hour TTL window must return data identical to the cached response without making a new external API call.

**Validates: Requirements 6.4**

---

### Property 9: Graceful Degradation on API Failure or Timeout

*For any* external API call (GitHub or WakaTime) that results in an error (network failure, 4xx, 5xx) or exceeds the 3-second timeout, the CodingStats component must hide itself and must not throw an unhandled exception or alter the layout of other page sections.

**Validates: Requirements 6.5, 6.8, 6.9**

---

### Property 10: Contact Form Validation Rejects Invalid Input

*For any* contact form submission where at least one field is invalid (empty required field, malformed email, message length outside 10–2000 characters), the backend validator must reject the request and return field-specific error messages without persisting any data to the database.

**Validates: Requirements 7.2, 7.3**

---

### Property 11: Contact Form Message Persistence Round Trip

*For any* valid contact form submission (all fields valid, honeypot empty, CAPTCHA passed, rate limit not exceeded), a Message record must be created in the database with field values equal to the submitted form data.

**Validates: Requirements 7.4**

---

### Property 12: Honeypot Rejects Bot Submissions

*For any* contact form request where the honeypot field contains a non-empty value, the system must reject the submission with a validation error and must not persist any data to the database.

**Validates: Requirements 7.8**

---

### Property 13: Rate Limiting Enforced on Contact Form

*For any* IP address that has successfully submitted the contact form 3 times within a 1-hour window, the next submission from that same IP must be rejected with the rate limit error message and must not persist data to the database.

**Validates: Requirements 7.10, 7.11**

---

### Property 14: Async Notification Dispatched After Message Save

*For any* valid contact form submission that results in a Message being saved to the database, a `SendContactNotification` job must be dispatched to the queue (verifiable via `Queue::fake()`).

**Validates: Requirements 7.12**

---

### Property 15: Invalid Login Credentials Rejected

*For any* login attempt where the email does not exist in the database or the password does not match the stored hash, the authentication must fail and the session must not be authenticated.

**Validates: Requirements 8.3**

---

### Property 16: Admin Routes Protected by Auth Guard

*For any* HTTP request to a route under the `/admin` prefix made without an authenticated session, the response must redirect to the login page (HTTP 302 to `/admin/login`).

**Validates: Requirements 8.4**

---

### Property 17: Brute Force Protection on Login

*For any* IP address that has made 5 failed login attempts within 1 minute, the next login attempt from that IP must be rejected with a rate limit response (HTTP 429) without checking credentials.

**Validates: Requirements 8.6**

---

### Property 18: Dashboard Statistics Match Database State

*For any* database state, the statistics displayed on the Admin Dashboard (total published projects, total messages, unread messages count) must equal the actual counts returned by the corresponding Eloquent queries.

**Validates: Requirements 9.1, 9.2**

---

### Property 19: CV File Validation Rejects Invalid Files

*For any* file upload to the CV upload endpoint where the file MIME type is not `application/pdf` or the file size exceeds 5MB, the validator must reject the upload and must not store any file to disk.

**Validates: Requirements 10.6**

---

### Property 20: Orphan File Cleanup on File Replacement

*For any* file replacement operation (CV upload or project thumbnail update) where a previous file path exists in the database, after the new file is saved, the old file path must no longer exist in storage.

**Validates: Requirements 10.9, 11.13**

---

### Property 21: Thumbnail Validation Rejects Invalid Files

*For any* file upload to the project thumbnail endpoint where the file MIME type is not `image/jpeg`, `image/png`, or `image/webp`, or the file size exceeds 2MB, the validator must reject the upload and must not store any file to disk.

**Validates: Requirements 11.3**

---

### Property 22: Thumbnail Converted to WebP on Storage

*For any* valid image file (JPEG or PNG) uploaded as a project thumbnail, the file stored in the storage directory must be in WebP format (verifiable by checking the stored file's MIME type or extension).

**Validates: Requirements 11.4**

---

### Property 23: Backend HTML Sanitization Removes XSS Payloads

*For any* HTML string containing XSS payloads (e.g., `<script>alert(1)</script>`, `<img onerror="...">`) submitted as project description via the Rich Text Editor, the value stored in the database must not contain executable script elements.

**Validates: Requirements 11.10**

---

### Property 24: Frontend DOMPurify Sanitization

*For any* HTML string containing XSS payloads passed to the `ProjectModal` component, the output of `DOMPurify.sanitize()` applied before `v-html` rendering must not contain `<script>` tags or inline event handlers.

**Validates: Requirements 11.12**

---

### Property 25: Message Read Status Update

*For any* Message record with `is_read = false`, after Admin accesses the message detail endpoint, the `is_read` field in the database must be updated to `true` and `read_at` must be set to the current timestamp.

**Validates: Requirements 12.3**

---

### Property 26: CSRF Protection Rejects Requests Without Valid Token

*For any* POST/PUT/DELETE request to a form submission endpoint that does not include a valid CSRF token, the application must respond with HTTP 419 (Page Expired) and must not process the request.

**Validates: Requirements 14.1**

---

### Property 27: Meta Tags Present in Every Page Response

*For any* public page route accessed via a full-page request, the HTML response must contain `<title>`, `<meta name="description">`, `<meta property="og:title">`, `<meta property="og:description">`, and `<meta property="og:url">` tags with non-empty values.

**Validates: Requirements 16.1, 16.2**

---

### Property 28: 500 Error Page Does Not Expose Stack Trace

*For any* internal server error triggered in the production environment, the HTTP response body must not contain PHP stack trace information, file paths, or exception class names.

**Validates: Requirements 17.2**

---

### Property 29: Interactive Elements Have aria-label

*For any* button or icon element in the Public Site that does not contain visible text content, the rendered HTML element must have a non-empty `aria-label` attribute.

**Validates: Requirements 18.1**

---

### Property 30: Focus Trap Constrains Keyboard Navigation Inside Modal

*For any* open Project_Modal, pressing the Tab key must cycle focus only among focusable elements within the modal container, and pressing Escape must close the modal.

**Validates: Requirements 18.3**

---

## Error Handling

### Strategi Error Handling

#### Frontend (Vue.js)

```
1. API Errors (Coding Stats)
   - Gunakan try/catch di dalam onMounted dengan AbortController (timeout 3s)
   - Set error state → sembunyikan komponen CodingStats
   - Jangan propagate error ke parent

2. Form Validation Errors (Inertia)
   - Inertia secara otomatis mengisi usePage().props.errors
   - Tampilkan error per field menggunakan computed dari usePage().props.errors

3. Inertia Navigation Errors
   - Gunakan Inertia.on('error', ...) untuk handle 500 errors
   - Redirect ke halaman Error 500 yang sesuai

4. Modal / Component Errors
   - Gunakan Vue Error Boundary (onErrorCaptured) di layout utama
   - Log error tanpa crash seluruh aplikasi
```

#### Backend (Laravel)

```
1. Form Request Validation Failures
   - Laravel otomatis return 422 dengan errors JSON untuk Inertia
   - Semua validasi di FormRequest classes

2. File Upload Errors
   - Validasi MIME type dan ukuran di FormRequest
   - Rollback file jika DB save gagal (try/catch + Storage::delete)

3. External API Errors (CodingStatsService)
   - Http::timeout(3)->get(...) dengan try/catch
   - Return null jika gagal, controller return error flag ke frontend

4. Queue Job Failures
   - SendContactNotification: retry 3x dengan exponential backoff
   - Failed jobs masuk ke failed_jobs table

5. HTTP Error Pages
   - 404: Render Inertia page Errors/404.vue
   - 500: Render Inertia page Errors/500.vue (tanpa stack trace di production)
   - Konfigurasi di app/Exceptions/Handler.php

6. Soft Delete & Data Recovery
   - Model Project dan Message menggunakan SoftDeletes trait
   - Penghapusan via Admin Panel bersifat soft delete (mengisi deleted_at, data tidak hilang permanen)
   - Admin Panel dapat menampilkan "Trash" view untuk melihat dan me-restore data yang terhapus (opsional)
   - Hard delete permanen hanya dilakukan secara eksplisit jika diperlukan
```

#### Exception Handler Configuration

```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->renderable(function (NotFoundHttpException $e, Request $request) {
        return Inertia::render('Errors/404')->toResponse($request)->setStatusCode(404);
    });

    $this->renderable(function (Throwable $e, Request $request) {
        if ($this->isHttpException($e)) return;
        return Inertia::render('Errors/500')->toResponse($request)->setStatusCode(500);
    });
}
```

---

## Testing Strategy

### Pendekatan Dual Testing

Strategi pengujian menggunakan dua pendekatan yang saling melengkapi:

1. **Unit/Feature Tests** (PHPUnit + Laravel Testing Helpers): Menguji contoh spesifik, edge cases, dan kondisi error di sisi backend.
2. **Property-Based Tests** (menggunakan [eris/eris](https://github.com/giorgiosironi/eris) untuk PHP atau [fast-check](https://github.com/dubzzz/fast-check) untuk JavaScript/Vue): Menguji properti universal yang harus berlaku untuk semua input yang valid.

Kedua pendekatan bersifat komplementer — unit tests menangkap bug konkret, property tests memverifikasi kebenaran umum.

### Unit & Feature Tests (PHPUnit)

Fokus pada:
- Contoh spesifik yang mendemonstrasikan perilaku benar (login sukses, form valid)
- Integration points antar komponen (controller → model → database)
- Edge cases dan kondisi error (file tidak valid, API timeout)

Contoh test cases:
```php
// tests/Feature/ContactFormTest.php
it('stores message and dispatches notification on valid submission')
it('rejects submission when honeypot field is filled')
it('returns 429 after 3 submissions from same IP within 1 hour')
it('rejects invalid email format')
it('rejects message shorter than 10 characters')

// tests/Feature/Admin/AuthTest.php
it('redirects to dashboard on valid credentials')
it('rejects invalid credentials with error message')
it('rate limits login after 5 failed attempts')
it('redirects unauthenticated requests to login page')

// tests/Feature/Admin/ProjectTest.php
it('converts uploaded thumbnail to webp format')
it('deletes old thumbnail when new one is uploaded')
it('sanitizes html in project description before saving')
it('rejects thumbnail larger than 2mb')
```

### Property-Based Tests

Library yang digunakan:
- **PHP Backend**: [eris/eris](https://github.com/giorgiosironi/eris) — property-based testing untuk PHPUnit
- **PHP Translatable**: [spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) — translatable model attributes via JSON columns
- **JavaScript Frontend**: [fast-check](https://github.com/dubzzz/fast-check) — property-based testing untuk Vitest/Jest

Konfigurasi minimum: **100 iterasi per property test**.

Setiap property test harus diberi tag komentar dengan format:
`// Feature: portfolio-website, Property {N}: {property_text}`

#### Contoh Property Tests (PHP — eris)

```php
// tests/Property/ContactFormPropertyTest.php

/**
 * Feature: portfolio-website, Property 10: Contact Form Validation Rejects Invalid Input
 */
public function testContactFormRejectsInvalidEmail(): void
{
    $this->forAll(
        Generator\string(), // random invalid email strings
        Generator\string()->between(10, 2000) // valid message length
    )->then(function ($invalidEmail, $message) {
        if (filter_var($invalidEmail, FILTER_VALIDATE_EMAIL)) return; // skip valid emails
        $response = $this->post('/contact', [
            'name' => 'Test User',
            'email' => $invalidEmail,
            'subject' => 'Test',
            'body' => $message,
            'honeypot' => '',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('messages', 0);
    });
}

/**
 * Feature: portfolio-website, Property 12: Honeypot Rejects Bot Submissions
 */
public function testHoneypotRejectsNonEmptyValue(): void
{
    $this->forAll(
        Generator\string()->between(1, 100) // any non-empty honeypot value
    )->then(function ($honeypotValue) {
        $response = $this->post('/contact', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Test',
            'body' => 'Valid message body here',
            'honeypot' => $honeypotValue,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseCount('messages', 0);
    });
}

/**
 * Feature: portfolio-website, Property 16: Admin Routes Protected by Auth Guard
 */
public function testAdminRoutesRequireAuthentication(): void
{
    $adminRoutes = [
        '/admin/dashboard', '/admin/projects', '/admin/messages',
        '/admin/landing', '/admin/skills', '/admin/timeline',
    ];
    $this->forAll(
        Generator\elements(...$adminRoutes)
    )->then(function ($route) {
        $response = $this->get($route);
        $response->assertRedirect('/admin/login');
    });
}
```

#### Contoh Property Tests (JavaScript — fast-check + Vitest)

```javascript
// tests/js/properties/ContactForm.property.test.js

import fc from 'fast-check';
import { describe, it, expect } from 'vitest';
import { validateContactForm } from '@/utils/validators';

describe('portfolio-website contact form properties', () => {
  /**
   * Feature: portfolio-website, Property 10: Contact Form Validation Rejects Invalid Input
   */
  it('rejects any message shorter than 10 characters', () => {
    fc.assert(fc.property(
      fc.string({ maxLength: 9 }),
      (shortMessage) => {
        const result = validateContactForm({ body: shortMessage });
        expect(result.errors.body).toBeDefined();
      }
    ), { numRuns: 100 });
  });

  /**
   * Feature: portfolio-website, Property 24: Frontend DOMPurify Sanitization
   */
  it('DOMPurify removes script tags from any HTML input', () => {
    fc.assert(fc.property(
      fc.string(),
      (randomHtml) => {
        const withScript = `<script>alert(1)</script>${randomHtml}`;
        const sanitized = DOMPurify.sanitize(withScript);
        expect(sanitized).not.toContain('<script>');
        expect(sanitized).not.toContain('onerror=');
      }
    ), { numRuns: 100 });
  });

  /**
   * Feature: portfolio-website, Property 5: Typing Animation Sequence Preservation
   */
  it('typing animation cycles through professions in order', () => {
    fc.assert(fc.property(
      fc.array(fc.string({ minLength: 1 }), { minLength: 1, maxLength: 10 }),
      (professions) => {
        const animator = new TypingAnimator(professions);
        const sequence = animator.getSequence(professions.length * 2);
        // Verify order is preserved in each cycle
        for (let i = 0; i < professions.length; i++) {
          expect(sequence[i]).toBe(professions[i]);
        }
      }
    ), { numRuns: 100 });
  });
});
```

### Cakupan Test per Layer

| Layer | Tool | Fokus |
|-------|------|-------|
| Backend Unit | PHPUnit | Models, Services, FormRequests |
| Backend Feature | PHPUnit + Laravel HTTP | Controllers, Routes, Middleware |
| Backend Property | eris/eris | Validasi, Rate Limiting, Auth Guard |
| Frontend Unit | Vitest | Composables, Utils, Validators |
| Frontend Component | Vitest + Vue Test Utils | Komponen Vue, Props, Events |
| Frontend Property | fast-check + Vitest | DOMPurify, i18n, Typing Animation |
| E2E (opsional) | Playwright | Critical user flows |
