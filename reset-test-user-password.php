<?php
/**
 * Script to reset a test user's password
 * Usage: php reset-test-user-password.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

// Reset password for hr@example.com
$email = 'hr@example.com';
$newPassword = 'htpass'; // Change this to your desired password

$user = User::where('email', $email)->first();

if ($user) {
    $user->password = Hash::make($newPassword);
    $user->save();
    echo "Password reset successfully for {$email}\n";
    echo "New password: {$newPassword}\n";
} else {
    echo "User with email {$email} not found.\n";
}

// Also reset for accountant
$email2 = 'accountant@example.com';
$newPassword2 = 'acctpass123';

$user2 = User::where('email', $email2)->first();

if ($user2) {
    $user2->password = Hash::make($newPassword2);
    $user2->save();
    echo "Password reset successfully for {$email2}\n";
    echo "New password: {$newPassword2}\n";
} else {
    echo "User with email {$email2} not found.\n";
}

