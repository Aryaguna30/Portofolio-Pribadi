# Dokumen Requirements

## Pendahuluan

Website Portofolio Profesional & Dinamis adalah aplikasi web berbasis SPA (Single Page Application) yang dibangun di atas Laravel 13 (backend), Vue.js 3 dengan Composition API (frontend), dan Inertia.js sebagai jembatan routing tanpa REST API manual. Website ini berfungsi sebagai alat personal branding untuk melamar pekerjaan dan menarik klien freelance, dilengkapi sistem Admin (CMS) untuk pembaruan konten secara dinamis. Styling menggunakan Tailwind CSS dengan animasi modern via GSAP/Framer Motion, mendukung multi-bahasa (Indonesia/Inggris) via vue-i18n, dan dioptimalkan untuk performa serta keamanan.

---

## Glosarium

- **Portfolio_App**: Keseluruhan aplikasi website portofolio (frontend publik + backend admin)
- **Public_Site**: Bagian frontend SPA yang dapat diakses publik tanpa autentikasi
- **Admin_Panel**: Bagian backend CMS yang hanya dapat diakses oleh pengguna terautentikasi
- **Admin**: Pengguna terautentikasi yang memiliki akses penuh ke Admin_Panel
- **Visitor**: Pengguna anonim yang mengakses Public_Site
- **Hero_Section**: Seksi pertama halaman beranda yang menampilkan perkenalan dan animasi
- **Project_Card**: Komponen kartu yang menampilkan ringkasan satu proyek portofolio
- **Project_Modal**: Pop-up/dialog yang menampilkan detail lengkap satu proyek portofolio
- **Timeline**: Komponen visual vertikal untuk menampilkan riwayat pendidikan atau pengalaman kerja
- **Contact_Form**: Formulir kontak yang dapat diisi oleh Visitor untuk mengirim pesan
- **Message**: Pesan yang dikirim oleh Visitor melalui Contact_Form
- **CV_File**: File PDF curriculum vitae yang dapat diunduh oleh Visitor
- **Coding_Stats**: Data statistik aktivitas coding real-time dari GitHub API atau WakaTime API
- **Language_Toggle**: Komponen UI untuk beralih antara bahasa Indonesia dan Inggris
- **Validator**: Komponen validasi input pada sisi backend (Laravel) maupun frontend (Vue.js)
- **Auth_Guard**: Middleware Laravel yang memproteksi rute Admin_Panel dari akses tanpa autentikasi
- **Thumbnail**: Gambar pratinjau proyek dalam format WebP yang ditampilkan pada Project_Card
- **i18n**: Sistem internasionalisasi multi-bahasa menggunakan vue-i18n
- **Meta_Manager**: Komponen/service Laravel yang mengatur meta tag HTML secara dinamis per halaman
- **OG_Tags**: Open Graph meta tags untuk mengontrol tampilan preview saat tautan dibagikan ke media sosial
- **Rich_Text_Editor**: Editor WYSIWYG (Quill.js atau Trix) yang diintegrasikan pada Admin_Panel untuk input konten berformat
- **Honeypot**: Field tersembunyi pada Contact_Form yang tidak terlihat oleh Visitor manusia, digunakan untuk mendeteksi bot
- **Rate_Limiter**: Mekanisme pembatasan jumlah request dari satu alamat IP dalam rentang waktu tertentu
- **Notification_Mailer**: Service pengiriman email otomatis menggunakan Laravel Mail/Queue
- **Error_Page**: Halaman khusus yang ditampilkan saat terjadi error HTTP (404, 500) dengan desain konsisten dengan website
- **DOMPurify**: Library sanitasi HTML di sisi klien (frontend) untuk mencegah eksekusi malicious script (XSS) saat merender konten HTML dari Rich_Text_Editor
- **Focus_Trap**: Mekanisme yang mengunci navigasi keyboard hanya di dalam elemen modal yang sedang aktif
- **Twitter_Card**: Meta tag khusus Twitter/X untuk mengontrol tampilan preview saat tautan dibagikan di platform tersebut
- **Theme_Toggle**: Tombol pada header untuk beralih antara mode terang (light) dan mode gelap (dark)
- **Orphan_File**: File yang tersimpan di storage server tetapi tidak lagi direferensikan oleh data apapun di database

---

## Requirements

### Requirement 1: Navigasi & Header Global

**User Story:** Sebagai Visitor, saya ingin melihat navigasi yang jelas dan konsisten di seluruh halaman, sehingga saya dapat berpindah antar seksi dengan mudah dan mengunduh CV.

#### Acceptance Criteria

1. THE Public_Site SHALL menampilkan header yang berisi logo/nama developer di sisi kiri, menu navigasi (Beranda, Tentang, Portofolio, Statistik, Kontak), Language_Toggle, dan tombol "Unduh CV" di setiap halaman.
2. WHEN Visitor mengklik salah satu item menu navigasi, THE Public_Site SHALL melakukan smooth scroll ke seksi yang sesuai dalam halaman yang sama tanpa reload halaman.
3. WHEN Visitor mengklik tombol "Unduh CV", THE Public_Site SHALL mengunduh CV_File terbaru dalam format PDF ke perangkat Visitor.
4. IF CV_File belum diunggah oleh Admin, THEN THE Public_Site SHALL menonaktifkan tombol "Unduh CV" dan menampilkan pesan "CV belum tersedia".
5. THE Public_Site SHALL menampilkan header dalam tampilan responsif yang sesuai pada lebar layar mobile (< 768px), tablet (768px–1023px), dan desktop (≥ 1024px).
6. WHILE Visitor menggulir halaman ke bawah melewati Hero_Section, THE Public_Site SHALL menampilkan header dengan latar belakang solid (tidak transparan) agar teks navigasi tetap terbaca.
7. THE Public_Site SHALL menyediakan tombol Theme_Toggle (Mode Terang/Gelap) pada Header.
8. WHEN Visitor mengklik Theme_Toggle, THE Public_Site SHALL mengubah tema visual seluruh halaman dan menyimpan preferensi tema Visitor di localStorage.
9. IF tidak ada preferensi tema di localStorage, THEN THE Public_Site SHALL menyesuaikan tema default berdasarkan preferensi sistem operasi Visitor (menggunakan `prefers-color-scheme`).

---

### Requirement 2: Multi-Bahasa (Internasionalisasi)

**User Story:** Sebagai Visitor, saya ingin beralih antara bahasa Indonesia dan Inggris, sehingga saya dapat membaca konten dalam bahasa yang saya pahami.

#### Acceptance Criteria

1. THE Public_Site SHALL mendukung dua bahasa: Indonesia (id) dan Inggris (en) menggunakan vue-i18n.
2. WHEN Visitor mengaktifkan Language_Toggle ke bahasa Inggris, THE Public_Site SHALL mengganti seluruh teks antarmuka dan konten statis ke bahasa Inggris tanpa reload halaman.
3. WHEN Visitor mengaktifkan Language_Toggle ke bahasa Indonesia, THE Public_Site SHALL mengganti seluruh teks antarmuka dan konten statis ke bahasa Indonesia tanpa reload halaman.
4. THE Public_Site SHALL menyimpan preferensi bahasa Visitor di localStorage sehingga preferensi tetap tersimpan saat Visitor membuka kembali halaman.
5. IF preferensi bahasa tidak ditemukan di localStorage, THEN THE Public_Site SHALL menggunakan bahasa Indonesia sebagai bahasa default.

---

### Requirement 3: Hero Section & Animasi Perkenalan

**User Story:** Sebagai Visitor, saya ingin melihat perkenalan yang menarik secara visual saat pertama kali membuka website, sehingga saya mendapat kesan profesional dan tertarik untuk menjelajahi lebih lanjut.

#### Acceptance Criteria

1. THE Hero_Section SHALL menampilkan nama developer, teks perkenalan profesi dengan efek typing animation, dan dua tombol CTA: "Lihat Portofolio" dan "Hubungi Saya".
2. WHEN halaman pertama kali dimuat, THE Hero_Section SHALL menampilkan efek fade-in pada seluruh elemen dalam durasi tidak lebih dari 800ms.
3. THE Hero_Section SHALL menggunakan efek typing animation yang menampilkan teks profesi secara berurutan dengan kecepatan yang dapat dikonfigurasi oleh Admin.
4. WHEN Visitor mengklik tombol "Lihat Portofolio", THE Public_Site SHALL melakukan smooth scroll ke seksi Portofolio.
5. WHEN Visitor mengklik tombol "Hubungi Saya", THE Public_Site SHALL melakukan smooth scroll ke seksi Kontak.
6. THE Hero_Section SHALL menampilkan konten yang dapat diperbarui oleh Admin melalui Admin_Panel (teks perkenalan, nama, profesi).
7. THE Hero_Section SHALL menggunakan animasi berbasis CSS transform dan opacity agar tidak mengurangi performa rendering.

---

### Requirement 4: Seksi Tentang Saya, Pendidikan & Pengalaman

**User Story:** Sebagai Visitor (HRD/Tech Lead), saya ingin melihat profil, riwayat pendidikan, pengalaman kerja, dan keahlian teknis developer, sehingga saya dapat menilai kualifikasi dan kesesuaian kandidat.

#### Acceptance Criteria

1. THE Public_Site SHALL menampilkan seksi "Tentang Saya" yang berisi teks profil singkat, Timeline pendidikan vertikal, Timeline pengalaman kerja/organisasi vertikal, dan daftar tech stack/keahlian dengan ikon.
2. THE Public_Site SHALL menampilkan setiap entri Timeline pendidikan dengan informasi: nama institusi, jurusan/program studi, tahun mulai, tahun selesai, dan deskripsi singkat.
3. THE Public_Site SHALL menampilkan setiap entri Timeline pengalaman dengan informasi: nama perusahaan/organisasi, posisi/jabatan, tahun mulai, tahun selesai, dan deskripsi singkat.
4. THE Public_Site SHALL menampilkan daftar keahlian teknis dengan ikon yang sesuai untuk setiap teknologi/bahasa pemrograman.
5. WHEN Visitor menggulir ke seksi Tentang Saya, THE Public_Site SHALL menampilkan animasi masuk (slide-in atau fade-in) pada elemen Timeline dan keahlian menggunakan Intersection Observer API.
6. THE Public_Site SHALL menampilkan data pendidikan, pengalaman, dan keahlian yang dikelola secara dinamis melalui Admin_Panel.

---

### Requirement 5: Seksi Portofolio (Showcase Proyek)

**User Story:** Sebagai Visitor (klien potensial/Tech Lead), saya ingin melihat daftar proyek yang pernah dikerjakan developer beserta detailnya, sehingga saya dapat menilai kualitas dan relevansi pekerjaan developer.

#### Acceptance Criteria

1. THE Public_Site SHALL menampilkan daftar proyek dalam grid layout dengan data yang diambil secara dinamis dari database.
2. THE Public_Site SHALL menampilkan setiap proyek sebagai Project_Card yang berisi Thumbnail, judul proyek, deskripsi singkat, dan daftar teknologi yang digunakan.
3. WHEN Visitor mengarahkan kursor ke Project_Card, THE Public_Site SHALL menampilkan efek hover berupa kartu yang sedikit terangkat (CSS transform: translateY) dalam durasi transisi tidak lebih dari 300ms.
4. WHEN Visitor mengklik Project_Card, THE Public_Site SHALL menampilkan Project_Modal yang berisi gambar proyek, deskripsi lengkap, daftar teknologi, link demo (jika tersedia), dan link repositori (jika tersedia).
5. WHEN Visitor mengklik di luar area Project_Modal atau tombol tutup, THE Public_Site SHALL menutup Project_Modal.
6. THE Public_Site SHALL menampilkan Thumbnail dalam format WebP untuk mengoptimalkan performa loading.
7. IF proyek tidak memiliki link demo, THEN THE Public_Site SHALL menyembunyikan tombol link demo pada Project_Modal.
8. IF tidak ada proyek yang tersimpan di database, THEN THE Public_Site SHALL menampilkan pesan "Belum ada proyek yang ditampilkan".

---

### Requirement 6: Seksi Statistik Aktivitas Coding

**User Story:** Sebagai Visitor (Tech Lead/Senior Developer), saya ingin melihat statistik aktivitas coding real-time developer, sehingga saya dapat menilai konsistensi dan keaktifan developer dalam menulis kode.

#### Acceptance Criteria

1. THE Public_Site SHALL menampilkan seksi Statistik yang berisi: daftar bahasa pemrograman paling sering digunakan dengan progress bar persentase, total commit GitHub, dan chart kontribusi GitHub.
2. WHEN Visitor membuka seksi Statistik, THE Public_Site SHALL mengambil data Coding_Stats dari GitHub API atau WakaTime API dan menampilkannya.
3. THE Public_Site SHALL menampilkan data bahasa pemrograman dalam bentuk progress bar dengan label nama bahasa dan persentase penggunaan.
4. THE Public_Site SHALL menyimpan cache data Coding_Stats selama 1 jam di sisi backend untuk mengurangi jumlah request ke API eksternal.
5. IF request ke GitHub API atau WakaTime API gagal, THEN THE Public_Site SHALL menampilkan pesan "Data statistik sementara tidak tersedia" tanpa mengganggu tampilan seksi lain.
6. THE Public_Site SHALL menampilkan indikator loading saat data Coding_Stats sedang diambil dari API.
7. THE Portfolio_App SHALL mengambil data Coding_Stats secara asinkron sehingga proses fetch tidak memblokir render komponen lain di halaman.
8. THE Portfolio_App SHALL menerapkan timeout maksimal 3 detik pada setiap request ke GitHub API atau WakaTime API.
9. IF request ke GitHub API atau WakaTime API melebihi timeout 3 detik atau mengembalikan error, THEN THE Public_Site SHALL menyembunyikan komponen Coding_Stats secara otomatis tanpa mengubah tata letak seksi lain di halaman.

---

### Requirement 7: Seksi Kontak & Formulir Pesan

**User Story:** Sebagai Visitor, saya ingin menghubungi developer melalui formulir kontak atau tautan media sosial, sehingga saya dapat memulai komunikasi untuk keperluan rekrutmen atau kolaborasi freelance.

#### Acceptance Criteria

1. THE Public_Site SHALL menampilkan Contact_Form dengan field: Nama (teks), Email (email), Subjek (teks), dan Pesan (textarea).
2. WHEN Visitor mengisi dan mengirim Contact_Form, THE Validator SHALL memvalidasi bahwa semua field terisi, format email valid, dan panjang pesan antara 10 hingga 2000 karakter sebelum data dikirim ke server.
3. IF Validator menemukan input tidak valid pada Contact_Form, THEN THE Public_Site SHALL menampilkan pesan error yang spesifik di bawah field yang bermasalah tanpa mengirim data ke server.
4. WHEN Contact_Form berhasil dikirim, THE Portfolio_App SHALL menyimpan Message ke database dan THE Public_Site SHALL menampilkan notifikasi sukses kepada Visitor.
5. THE Public_Site SHALL menampilkan tautan media sosial profesional: LinkedIn, GitHub, dan Email di seksi Kontak.
6. THE Validator SHALL melakukan sanitasi input pada sisi backend untuk mencegah serangan XSS sebelum menyimpan Message ke database.
7. THE Portfolio_App SHALL menggunakan CSRF token Laravel pada setiap pengiriman Contact_Form untuk mencegah serangan CSRF.
8. THE Contact_Form SHALL menyertakan Honeypot field tersembunyi yang tidak terlihat oleh Visitor manusia, dan THE Validator SHALL menolak pengiriman jika Honeypot field terisi.
9. THE Portfolio_App SHALL mengintegrasikan sistem anti-spam pasif (Cloudflare Turnstile atau Google reCAPTCHA v3) pada Contact_Form untuk memverifikasi bahwa pengirim adalah manusia.
10. THE Rate_Limiter SHALL membatasi pengiriman Contact_Form maksimal 3 kali per alamat IP dalam rentang waktu 1 jam.
11. IF Rate_Limiter mendeteksi pengiriman Contact_Form melebihi batas dari satu alamat IP, THEN THE Public_Site SHALL menampilkan pesan error "Terlalu banyak percobaan. Silakan coba lagi dalam 1 jam." dan menolak pengiriman.
12. WHEN Contact_Form berhasil dikirim dan Message tersimpan di database, THE Notification_Mailer SHALL secara asinkron mengirimkan notifikasi email ke alamat email Admin yang berisi detail pesan tersebut.

---

### Requirement 8: Autentikasi Admin

**User Story:** Sebagai Admin, saya ingin login ke Admin_Panel dengan kredensial yang aman, sehingga hanya saya yang dapat mengelola konten website.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menyediakan halaman login dengan field email dan password.
2. WHEN Admin memasukkan kredensial yang valid, THE Auth_Guard SHALL mengautentikasi Admin dan mengarahkan ke halaman Dashboard Admin.
3. IF Admin memasukkan kredensial yang tidak valid, THEN THE Auth_Guard SHALL menampilkan pesan error "Email atau password salah" dan tidak memberikan akses ke Admin_Panel.
4. THE Auth_Guard SHALL memproteksi semua rute Admin_Panel sehingga Visitor yang tidak terautentikasi diarahkan ke halaman login.
5. WHEN Admin mengklik tombol logout, THE Auth_Guard SHALL mengakhiri sesi Admin dan mengarahkan ke halaman login.
6. THE Auth_Guard SHALL membatasi percobaan login maksimal 5 kali dalam 1 menit per alamat IP untuk mencegah serangan brute force.

---

### Requirement 9: Dashboard Admin

**User Story:** Sebagai Admin, saya ingin melihat ringkasan statistik website di dashboard, sehingga saya dapat memantau kondisi konten dan pesan masuk dengan cepat.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menampilkan Dashboard yang berisi: total proyek yang dipublikasikan, total Message yang belum dibaca, dan total Message yang diterima.
2. THE Admin_Panel SHALL menampilkan data statistik Dashboard secara real-time setiap kali halaman Dashboard dimuat.
3. WHEN Admin mengklik widget total Message yang belum dibaca, THE Admin_Panel SHALL mengarahkan Admin ke halaman Manajemen Pesan.

---

### Requirement 10: Manajemen Konten Landing Page

**User Story:** Sebagai Admin, saya ingin memperbarui konten Hero Section, riwayat pendidikan, pengalaman kerja, dan file CV melalui panel admin, sehingga website selalu menampilkan informasi terkini tanpa perlu mengubah kode.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menyediakan form untuk mengedit teks perkenalan Hero_Section (nama, profesi, deskripsi singkat).
2. WHEN Admin menyimpan perubahan teks Hero_Section, THE Portfolio_App SHALL memperbarui data di database dan THE Public_Site SHALL menampilkan konten terbaru.
3. THE Admin_Panel SHALL menyediakan form untuk menambah, mengedit, dan menghapus entri Timeline pendidikan.
4. THE Admin_Panel SHALL menyediakan form untuk menambah, mengedit, dan menghapus entri Timeline pengalaman kerja/organisasi.
5. THE Admin_Panel SHALL menyediakan form upload untuk mengganti CV_File dengan file PDF baru.
6. WHEN Admin mengunggah CV_File baru, THE Validator SHALL memvalidasi bahwa file bertipe PDF dan berukuran tidak lebih dari 5MB sebelum disimpan.
7. IF Validator menemukan CV_File tidak valid, THEN THE Admin_Panel SHALL menampilkan pesan error yang spesifik dan tidak menyimpan file.
8. THE Admin_Panel SHALL menyediakan form untuk menambah, mengedit, dan menghapus entri keahlian teknis beserta ikon yang sesuai.
9. WHEN Admin mengunggah CV_File baru, THE Portfolio_App SHALL secara otomatis menghapus CV_File yang lama dari storage server sebelum menyimpan file yang baru.

---

### Requirement 11: Manajemen Portofolio (CRUD Proyek)

**User Story:** Sebagai Admin, saya ingin mengelola daftar proyek portofolio (tambah, lihat, edit, hapus) melalui panel admin, sehingga showcase proyek di website selalu relevan dan terkini.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menampilkan daftar semua proyek dalam format tabel dengan kolom: Thumbnail, Judul, Tech Stack, tanggal dibuat, dan aksi (Edit, Hapus).
2. THE Admin_Panel SHALL menyediakan form untuk membuat proyek baru dengan field: Judul (wajib), Deskripsi lengkap (wajib), Tech Stack (wajib, dapat lebih dari satu), URL Demo (opsional), URL Repositori (opsional), dan upload Thumbnail (wajib).
3. WHEN Admin mengunggah Thumbnail proyek, THE Validator SHALL memvalidasi bahwa file bertipe gambar (JPEG, PNG, atau WebP) dan berukuran tidak lebih dari 2MB.
4. WHEN Admin menyimpan proyek baru, THE Portfolio_App SHALL mengkonversi Thumbnail ke format WebP dan menyimpannya ke storage.
5. THE Admin_Panel SHALL menyediakan form edit yang menampilkan data proyek yang sudah ada untuk diperbarui.
6. WHEN Admin mengklik tombol Hapus pada sebuah proyek, THE Admin_Panel SHALL menampilkan konfirmasi penghapusan sebelum menghapus data.
7. WHEN Admin mengkonfirmasi penghapusan proyek, THE Portfolio_App SHALL menghapus data proyek beserta Thumbnail dari database dan storage.
8. IF Validator menemukan field wajib tidak terisi pada form proyek, THEN THE Admin_Panel SHALL menampilkan pesan error yang spesifik per field dan tidak menyimpan data.
9. THE Admin_Panel SHALL mengintegrasikan Rich_Text_Editor (Quill.js atau Trix) pada field Deskripsi lengkap proyek untuk mendukung format teks seperti paragraf, bullet points, huruf tebal, dan huruf miring.
10. WHEN Admin menyimpan proyek dengan konten Rich_Text_Editor, THE Portfolio_App SHALL menyimpan konten dalam format HTML yang telah disanitasi ke database.
11. WHEN Public_Site menampilkan deskripsi proyek pada Project_Modal, THE Public_Site SHALL merender konten HTML dari Rich_Text_Editor dengan format yang terjaga (paragraf, bullet points, huruf tebal, huruf miring).
12. WHEN THE Public_Site merender konten HTML dari Rich_Text_Editor pada Project_Modal, THE Public_Site SHALL memproses konten tersebut menggunakan library sanitasi frontend (DOMPurify) untuk mencegah eksekusi malicious script (XSS) di sisi klien.
13. WHEN Admin memperbarui proyek dengan mengunggah Thumbnail baru, THE Portfolio_App SHALL menghapus Thumbnail lama dari storage server secara otomatis.

---

### Requirement 12: Manajemen Pesan (Inbox)

**User Story:** Sebagai Admin, saya ingin membaca dan mengelola pesan masuk dari Visitor, sehingga saya dapat merespons pertanyaan atau tawaran kerja sama dengan tepat waktu.

#### Acceptance Criteria

1. THE Admin_Panel SHALL menampilkan daftar semua Message dalam format tabel dengan kolom: Nama pengirim, Email, Subjek, tanggal diterima, dan status (Dibaca/Belum Dibaca).
2. THE Admin_Panel SHALL menampilkan Message yang belum dibaca dengan penanda visual yang berbeda (misalnya teks tebal atau latar belakang berbeda).
3. WHEN Admin mengklik sebuah Message, THE Admin_Panel SHALL menampilkan detail lengkap Message dan mengubah status Message menjadi "Dibaca".
4. THE Admin_Panel SHALL menampilkan daftar Message diurutkan berdasarkan tanggal terbaru terlebih dahulu secara default.
5. WHEN Admin mengklik tombol Hapus pada sebuah Message, THE Admin_Panel SHALL menampilkan konfirmasi penghapusan sebelum menghapus data.

---

### Requirement 13: Performa & Optimasi Aset

**User Story:** Sebagai Visitor, saya ingin website dimuat dengan cepat, sehingga saya tidak perlu menunggu lama untuk mengakses informasi.

#### Acceptance Criteria

1. THE Public_Site SHALL mencapai waktu initial load (Time to Interactive) kurang dari 2 detik pada koneksi broadband standar (≥ 10 Mbps).
2. THE Portfolio_App SHALL menyimpan dan menyajikan semua gambar proyek dalam format WebP untuk mengurangi ukuran file.
3. THE Portfolio_App SHALL menggunakan lazy loading pada gambar yang berada di luar viewport saat halaman pertama dimuat.
4. THE Portfolio_App SHALL menggunakan animasi berbasis CSS transform dan opacity (bukan layout-triggering properties) untuk memastikan animasi berjalan pada 60fps.
5. THE Portfolio_App SHALL menggunakan Vite untuk bundling dan code splitting aset JavaScript dan CSS.

---

### Requirement 14: Keamanan Aplikasi

**User Story:** Sebagai Admin, saya ingin website terlindungi dari serangan umum, sehingga data dan konten website tetap aman.

#### Acceptance Criteria

1. THE Portfolio_App SHALL menyertakan CSRF token Laravel pada setiap form submission (Contact_Form dan semua form Admin_Panel).
2. THE Validator SHALL melakukan validasi dan sanitasi semua input pengguna di sisi backend menggunakan Laravel Form Request sebelum data diproses atau disimpan ke database.
3. THE Portfolio_App SHALL menggunakan Eloquent ORM dengan parameterized queries untuk semua operasi database guna mencegah SQL Injection.
4. THE Portfolio_App SHALL menerapkan Content Security Policy (CSP) header untuk mencegah serangan XSS.
5. THE Auth_Guard SHALL menggunakan Laravel session-based authentication dengan cookie HttpOnly dan Secure flag pada environment production.
6. THE Portfolio_App SHALL memvalidasi dan membatasi tipe file yang dapat diunggah (hanya PDF untuk CV, hanya gambar untuk Thumbnail) di sisi backend.
7. THE Portfolio_App SHALL menyimpan semua rahasia kredensial pihak ketiga (GitHub API Key, WakaTime API Key, SMTP Mail Password, dan Cloudflare Turnstile Secret Key) secara eksklusif di dalam file `.env` dan tidak boleh di-hardcode ke dalam source code.

---

### Requirement 15: Responsivitas (Mobile-First Design)

**User Story:** Sebagai Visitor, saya ingin mengakses website dari perangkat apapun (mobile, tablet, desktop) dengan tampilan yang optimal, sehingga pengalaman browsing tetap nyaman di semua ukuran layar.

#### Acceptance Criteria

1. THE Public_Site SHALL menerapkan pendekatan Mobile-First menggunakan breakpoint Tailwind CSS: mobile (default, < 768px), tablet (md: 768px–1023px), dan desktop (lg: ≥ 1024px).
2. THE Public_Site SHALL menampilkan menu navigasi sebagai hamburger menu yang dapat dibuka/tutup pada tampilan mobile.
3. THE Public_Site SHALL menampilkan grid proyek dalam 1 kolom pada mobile, 2 kolom pada tablet, dan 3 kolom pada desktop.
4. THE Public_Site SHALL menampilkan Timeline dalam layout single-column yang mudah dibaca pada semua ukuran layar.
5. THE Public_Site SHALL memastikan semua elemen interaktif (tombol, link, form field) memiliki ukuran tap target minimal 44x44px pada tampilan mobile.

---

### Requirement 16: SEO, Open Graph & Meta Tags

**User Story:** Sebagai Visitor dan Perekrut, saya ingin melihat pratinjau (preview) yang menarik saat tautan website dibagikan di media sosial, dan mudah menemukannya di mesin pencari.

#### Acceptance Criteria

1. THE Meta_Manager SHALL menghasilkan meta tag `title`, `description`, dan `keywords` secara dinamis untuk setiap halaman yang diakses.
2. THE Meta_Manager SHALL menyuntikkan OG_Tags (`og:title`, `og:description`, `og:image`, `og:url`) dan Twitter_Card ke dalam tag `<head>` HTML sebelum halaman dikirim ke klien (server-side rendering untuk tag head via Inertia).
3. WHEN tautan Public_Site dibagikan ke media sosial, THE OG_Tags SHALL menampilkan Thumbnail default website, kecuali jika tautan spesifik menuju detail proyek, maka akan menampilkan Thumbnail proyek tersebut.

---

### Requirement 17: Penanganan Error (Error Handling)

**User Story:** Sebagai Visitor, saya ingin melihat halaman error yang informatif dan tetap berada dalam desain website jika saya mengakses URL yang salah atau terjadi gangguan server.

#### Acceptance Criteria

1. IF Visitor mengakses rute/URL yang tidak terdaftar, THEN THE Portfolio_App SHALL menampilkan Error_Page (404 Not Found) yang memiliki desain konsisten dengan Public_Site dan tombol navigasi kembali ke Beranda.
2. IF terjadi internal server error pada backend, THEN THE Portfolio_App SHALL menangkap error tersebut dan menampilkan Error_Page (500 Server Error) yang ramah pengguna tanpa membocorkan stack trace error kepada Visitor.

---

### Requirement 18: Aksesibilitas (Accessibility / a11y)

**User Story:** Sebagai Visitor dengan kebutuhan khusus, saya ingin dapat menavigasi website menggunakan keyboard dan screen reader.

#### Acceptance Criteria

1. THE Public_Site SHALL menyediakan atribut `aria-label` pada semua tombol dan ikon yang tidak memiliki teks terlihat (misalnya: hamburger menu, tombol tutup Project_Modal).
2. WHEN Visitor menekan tombol `Tab` pada keyboard, THE Public_Site SHALL memindahkan fokus secara logis dan menampilkan indikator fokus visual (focus ring) pada elemen interaktif.
3. WHEN Project_Modal terbuka, THE Public_Site SHALL mengunci fokus keyboard hanya di dalam modal tersebut (Focus_Trap) dan memungkinkan Visitor menutup modal dengan menekan tombol `Escape`.
