# User Manual SmartBin

## 1. Pengenalan

SmartBin ialah sistem pemantauan tong pintar untuk memantau aset, sensor, kutipan, aduan, laporan, dan notifikasi.

Manual ini telah disusun semula mengikut dua peranan pengguna sahaja:

- `Admin`
- `Supervisor`

## 2. Peranan Pengguna

### 2.1 Admin

Admin mempunyai `full control` ke atas sistem.

Admin boleh mengakses dan mengurus semua modul utama, termasuk:

- Dashboard
- Summary
- Collection Trips
- Assets
- Sensors
- Users
- Holidays and Events
- Capacity Settings
- Notifications
- Complaints
- Schedule
- Attendance
- Leave Management
- Profile

### 2.2 Supervisor

Supervisor hanya menerima dan memantau `notifikasi sahaja`.

Skop penggunaan supervisor dalam manual ini adalah:

- menerima notifikasi sistem
- melihat makluman berkaitan tong, sensor, atau operasi jika notifikasi diaktifkan

Supervisor tidak diberi kuasa untuk:

- menambah data
- mengubah data
- memadam data
- mengurus pengguna
- mengubah tetapan sistem

Nota: berdasarkan kod semasa, akaun supervisor wujud dalam sistem tetapi aliran login supervisor sedang disekat. Oleh itu, akses supervisor mungkin memerlukan pelarasan sistem sebelum digunakan sepenuhnya.

## 3. Akses Sistem

### 3.1 Login

1. Buka halaman login sistem.
2. Masukkan email.
3. Masukkan kata laluan.
4. Klik butang login.

Jika maklumat log masuk betul:

- Admin akan dibawa ke dashboard utama.
- Supervisor hanya menggunakan akses yang berkaitan dengan notifikasi, tertakluk kepada konfigurasi sistem semasa.

Jika maklumat salah, sistem akan memaparkan mesej ralat.

### 3.2 Lupa Kata Laluan

Sistem menyediakan fungsi:

- `Forgot Password`
- `Reset Password`

Pengguna boleh meminta pautan reset kata laluan melalui halaman lupa kata laluan.

### 3.3 Logout

Pengguna boleh klik menu `Logout` untuk keluar daripada sistem dengan selamat.

## 4. Modul Admin

### 4.1 Dashboard

Dashboard digunakan untuk melihat gambaran keseluruhan operasi sistem.

Maklumat yang boleh dipantau termasuk:

- status tong
- bacaan sensor
- ringkasan aktiviti semasa
- maklumat aset yang perlu diberi perhatian

### 4.2 Summary

Modul ini digunakan untuk melihat ringkasan prestasi dan aktiviti operasi secara keseluruhan.

### 4.3 Collection Trips

Modul ini digunakan untuk melihat rekod kutipan tong.

Fungsi utama:

- tapis mengikut tarikh
- tapis mengikut aset
- lihat jumlah trip
- lihat jumlah tong yang dikutip
- eksport CSV
- jana PDF ringkasan

### 4.4 Assets

Modul `Assets` digunakan untuk mengurus semua tong atau aset yang direkodkan.

Maklumat yang dipaparkan biasanya termasuk:

- nama aset
- floor
- nombor siri
- lokasi
- model
- status aktif atau tidak aktif
- latitude dan longitude
- gambar aset
- QR code

Fungsi utama:

- tambah aset
- lihat butiran aset
- padam aset
- semak QR code aset

### 4.5 Sensors

Modul `Sensors` digunakan untuk melihat maklumat sensor dan prestasinya.

Fungsi utama:

- lihat senarai sensor
- lihat perincian sensor
- semak bacaan mengikut device atau aset
- eksport laporan jika disediakan

### 4.6 Users

Modul `Users` digunakan untuk mengurus akaun pengguna sistem.

Fungsi utama:

- tambah pengguna
- kemas kini pengguna
- lihat butiran pengguna
- padam pengguna
- reset kata laluan pengguna
- hidup atau matikan notifikasi WhatsApp pengguna
- lihat status `last active`

Dalam manual ini, pengguna yang diurus hanya:

- Admin
- Supervisor

### 4.7 Holidays & Events

Modul ini digunakan untuk mengurus hari cuti dan acara operasi.

Fungsi utama:

- tambah hari cuti
- edit hari cuti
- padam hari cuti
- aktif atau nyahaktif hari cuti
- tambah dan urus event

### 4.8 Capacity Settings

Modul ini digunakan untuk menetapkan ambang kapasiti tong.

Contoh kategori bacaan:

- Empty
- Half Full
- Full

### 4.9 Notifications

Modul ini digunakan untuk mengurus notifikasi WhatsApp atau notifikasi berkaitan sistem.

Fungsi utama:

- hidup atau matikan notifikasi umum
- hidup atau matikan notifikasi mengikut bin
- hidup atau matikan notifikasi mengikut device
- hidup atau matikan notifikasi pada pengguna tertentu

### 4.10 Complaints

Sistem mempunyai modul aduan untuk pengguna dalaman dan juga pelawat.

Fungsi utama:

- lihat semua aduan
- tambah aduan
- kemas kini aduan
- padam aduan

Sistem juga menyokong borang aduan awam tanpa login.

### 4.11 Schedule

Modul jadual digunakan untuk mengurus perancangan operasi.

Fungsi utama:

- lihat jadual
- tambah jadual
- kemas kini jadual
- padam jadual

### 4.12 Attendance

Modul attendance digunakan untuk semakan kehadiran jika organisasi menggunakannya dalam operasi.

### 4.13 Leave Management

Modul ini digunakan untuk pengurusan cuti.

Fungsi utama admin:

- lihat permohonan cuti
- lulus atau tolak cuti
- urus kuota cuti
- lihat butiran cuti

Jenis cuti yang digunakan dalam sistem termasuk:

- `mc`
- `annual_leave`
- `emergency_leave`
- `hospitality`

### 4.14 Profile

Pengguna boleh:

- mengemas kini profil
- muat naik gambar profil
- menukar kata laluan

## 5. Modul Supervisor

### 5.1 Notifikasi Sahaja

Supervisor hanya terlibat dalam penerimaan notifikasi.

Antara kegunaan utama:

- menerima makluman apabila status tong memerlukan perhatian
- menerima notifikasi berkaitan sensor atau device jika diaktifkan
- menerima makluman operasi yang dihantar melalui sistem notifikasi

Supervisor tidak mengurus data utama sistem.

## 6. Aduan Awam

Sistem menyediakan borang aduan awam tanpa login.

Aliran asas:

1. Pelawat buka borang aduan awam.
2. Pilih aset atau tong berkaitan.
3. Isi tajuk aduan.
4. Isi penerangan aduan.
5. Hantar aduan.

Status aduan baharu akan direkodkan sebagai `pending`.

## 7. QR dan Paparan Aset

Sistem menyediakan QR code untuk aset tertentu.

Kegunaan:

- mengenal pasti aset dengan cepat
- membuka maklumat aset tertentu
- membantu semakan lokasi atau rujukan aset

## 8. Status Penting Dalam Sistem

### 8.1 Status Aduan

- `pending`
- `assigned`

### 8.2 Status Aset

- `active`
- `inactive`

## 9. Cadangan Aliran Kerja Harian

### 9.1 Untuk Admin

1. Login ke sistem.
2. Semak Dashboard.
3. Semak status tong melalui Assets atau Sensors.
4. Semak aduan baharu.
5. Semak Summary dan Collection Trips.
6. Pantau tetapan notifikasi.
7. Kemas kini data sistem jika perlu.

### 9.2 Untuk Supervisor

1. Terima notifikasi sistem.
2. Semak makluman yang diterima.
3. Maklumkan kepada pihak admin atau operasi jika tindakan lanjut diperlukan.

## 10. Troubleshooting Ringkas

### 10.1 Tidak boleh login

Semak:

- email betul
- kata laluan betul
- akaun mempunyai peranan yang dibenarkan

Nota: akaun supervisor dalam kod semasa masih disekat pada bahagian login.

### 10.2 Aduan tidak muncul

Semak:

- aduan berjaya dihantar
- aset dipilih dengan betul
- status data dalam senarai aduan

### 10.3 Notifikasi WhatsApp tidak aktif

Semak:

- nombor telefon pengguna telah diisi
- tetapan notifikasi di modul `Notifications`
- status toggle WhatsApp pada pengguna, bin, atau device

## 11. Penutup

Manual ini telah disesuaikan kepada struktur pengguna berikut:

- `Admin` dengan kawalan penuh
- `Supervisor` untuk notifikasi sahaja

Jika anda mahu, manual ini boleh disambung lagi kepada versi rasmi syarikat dengan:

- logo dan identiti organisasi
- tangkapan skrin setiap modul
- format SOP
- versi PDF
