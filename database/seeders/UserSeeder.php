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
                'name' => 'Test',
                'surname' => 'Kullanıcı',
                'email' => 'test@example.com',
                'phone' => '05551234567',
                'password' => 'password',
            ],
            [
                'name' => 'Ahmet',
                'surname' => 'Yılmaz',
                'email' => 'ahmet@example.com',
                'phone' => '05551234568',
                'password' => 'password',
            ],
            [
                'name' => 'Ayşe',
                'surname' => 'Demir',
                'email' => 'ayse@example.com',
                'phone' => '05551234569',
                'password' => 'password',
            ],
            [
                'name' => 'Kargo',
                'surname' => 'Operatörü',
                'email' => 'operator@example.com',
                'phone' => '05551234570',
                'password' => 'password',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'phone' => $data['phone'],
                    'password' => $data['password'],
                ]
            );
        }
    }
}
