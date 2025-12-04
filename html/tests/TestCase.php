<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Str;

class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function newUser(): User
    {
        return User::create([
            'email' => Str::random(10).'@gmail.com',
            'password' => bcrypt('password'),
            'unique_id' => Str::random(8),
            'username' => Str::random(10),
            'created_ip' => '192.168.127.27',
            'used_ip' => '192.168.127.27',
            'referral_use' => false,
        ]);

    }
}