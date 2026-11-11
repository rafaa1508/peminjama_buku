<?php

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function check(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

DB::beginTransaction();
try {
    $user = User::create(['name' => 'Loan check', 'email' => uniqid().'@example.test', 'password' => 'password', 'role' => 'siswa']);
    $book = Book::create(['title' => 'Loan check', 'author' => 'Check', 'publisher' => 'Check', 'year' => 2026, 'stock' => 1]);
    $loan = Loan::borrow($user, $book);
    check($book->fresh()->stock === 0, 'Stock must decrease once');
    check($loan->due_date->isSameDay(today()->addDays(7)), 'Due date must be seven days later');
    try {
        Loan::borrow($user, $book);
        throw new RuntimeException('Empty stock accepted');
    } catch (ValidationException) {
        check($book->fresh()->stock === 0, 'Rejected borrow changed stock');
    }
    $loan->update(['loan_date' => today()->subDays(10), 'due_date' => today()->subDays(3)]);
    $loan->returnBook();
    $loan->returnBook();
    check($book->fresh()->stock === 1, 'Repeated return changed stock');
    check($loan->fresh()->fine === 3000, 'Three late days must cost 3000');
    check($loan->fresh()->returned_at->isToday(), 'Return date missing');
    $book->update(['stock' => 4]);
    for ($i = 0; $i < 3; $i++) {
        Loan::borrow($user, $book);
    }
    try {
        Loan::borrow($user, $book);
        throw new RuntimeException('Fourth active loan accepted');
    } catch (ValidationException) {
        check($book->fresh()->stock === 1, 'Rejected fourth loan changed stock');
    }
    echo "Loan invariants passed; changes rolled back.\n";
} finally {
    DB::rollBack();
}
