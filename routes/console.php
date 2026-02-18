<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('jwt:secret', function () {
    $key = 'base64:'.base64_encode(random_bytes(32));
    $path = $this->laravel->environmentFilePath();
    if (! file_exists($path)) {
        $this->error('.env dosyası bulunamadı.');
        return 1;
    }
    $content = file_get_contents($path);
    if (str_contains($content, 'JWT_SECRET=')) {
        $content = preg_replace('/JWT_SECRET=.*/', 'JWT_SECRET='.$key, $content);
    } else {
        $content .= "\nJWT_SECRET={$key}\n";
    }
    file_put_contents($path, $content);
    $this->info('JWT_SECRET .env dosyasına yazıldı.');
    return 0;
})->purpose('JWT imzalama için secret key üretir ve .env dosyasına ekler');
