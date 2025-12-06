<?php
// Simple script to render a Blade view for a given LoanRequest ID
// Usage: php scripts/debug/render_loan_request.php <id>

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';

$app->make('Illuminate\\Foundation\\Console\\Kernel')->bootstrap();

$loanId = isset($argv[1]) ? (int) $argv[1] : 25;
// Optional: login as a specific user by email (argv[2])
use Illuminate\Support\Facades\Auth;
use App\Models\User;
if (isset($argv[2]) && filter_var($argv[2], FILTER_VALIDATE_EMAIL)) {
    $u = User::where('email', $argv[2])->first();
    if ($u) {
        Auth::login($u);
    }
}
// fwrite(STDERR, 'Rendering loan_requests.show for ID ' . $loanId . PHP_EOL);

use App\Models\LoanRequest;
$lr = LoanRequest::find($loanId);
if (!$lr) {
    echo "LoanRequest not found: ID={$loanId}\n";
    exit(1);
}

echo view('loan_requests.show', ['loanRequest' => $lr])->render();