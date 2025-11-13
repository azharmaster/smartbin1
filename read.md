composer require realrashid/sweet-alert
php artisan sweetalert:publish
php artisan make:view layouts.app

amek dist/pluggin adminlte masuk dalma public/adminlte

php artisan make:controller Auth/LoginController

php artisan db:seed //test data
php artisan make:view dashboard.index


php artisan make:component Admin/Aside

php artisan make:middleware Guest
php artisan make:middleware Auth 

php artisan make:model Kategori -cms //controller migrateion seeder
php artisan db:seed --class=KategoriSeeder

php artisan make:view kategori.index

php artisan make:component Kateori/FormatKategori