<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = new App\Models\User();
    $user->name = 'Paciente Prueba';
    $user->email = 'paciente@test.com';
    $user->password = bcrypt('12345678');
    $user->rol_id = 3; // Asumiendo que el ID 3 es paciente
    $user->save();

    echo "Usuario creado con ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password: 12345678\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}