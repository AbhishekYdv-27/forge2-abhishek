<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

$users = [
    ['email' => 'admin@acme.test', 'name' => 'Admin User', 'password' => 'password'],
    ['email' => 'agent@acme.test', 'name' => 'Agent User', 'password' => 'password'],
    ['email' => 'customer@acme.test', 'name' => 'Customer User', 'password' => 'password'],
];

foreach ($users as $user) {
    \App\Models\User::firstOrCreate(
        ['email' => $user['email']],
        ['name' => $user['name'], 'password' => bcrypt($user['password'])]
    );
}
echo "Users created!\n";
