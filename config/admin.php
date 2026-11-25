<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Path Login Staf
    |--------------------------------------------------------------------------
    |
    | Halaman login tidak ditautkan di situs publik. Ubah nilai ini lewat
    | ADMIN_LOGIN_PATH di .env agar pintu masuk tidak mudah ditebak orang.
    |
    */

    'login_path' => trim((string) env('ADMIN_LOGIN_PATH', 'kelola-tikum-9f3a2c7d'), '/'),

];
