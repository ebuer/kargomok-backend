<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Test kullanıcıları oluşturur.
     * Tüm hesapların şifresi: password
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Test Kullanıcı',
                'email' => 'test@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Ahmet Yılmaz',
                'email' => 'ahmet@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Ayşe Demir',
                'email' => 'ayse@example.com',
                'password' => 'password',
            ],
            [
                'name' => 'Kargo Operatörü',
                'email' => 'operator@example.com',
                'password' => 'password',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                ]
            );
        }
    }
}
