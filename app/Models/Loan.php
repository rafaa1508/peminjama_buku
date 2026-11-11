<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Loan extends Model
{
    public static function borrow(User $user, Book $book): self
    {
        return DB::transaction(function () use ($user, $book) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $book = Book::whereKey($book->id)->lockForUpdate()->firstOrFail();
            if ($book->stock < 1) {
                throw ValidationException::withMessages(['stock' => 'Stok buku habis.']);
            }
            if (self::where('user_id', $user->id)->where('status', 'dipinjam')->count() >= 3) {
                throw ValidationException::withMessages(['loans' => 'Maksimal pinjam 3 buku.']);
            }
            $loan = self::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'loan_date' => today(),
                'due_date' => today()->addDays(7),
            ]);
            $book->decrement('stock');

            return $loan;
        });
    }

    public function returnBook(): void
    {
        DB::transaction(function () {
            $loan = self::whereKey($this->id)->lockForUpdate()->firstOrFail();
            if ($loan->status !== 'dipinjam') {
                return;
            }
            $loan->update([
                'returned_at' => today(),
                'status' => 'dikembalikan',
                'fine' => max(0, (int) $loan->due_date->diffInDays(today(), false)) * 1000,
            ]);
            $loan->book()->increment('stock');
        });
    }

    protected $fillable = ['user_id', 'book_id', 'loan_date', 'due_date', 'returned_at', 'status', 'fine'];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'returned_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
