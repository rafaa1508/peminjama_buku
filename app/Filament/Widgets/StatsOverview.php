<?php

namespace App\Filament\Widgets;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Buku', Book::count()),
            Stat::make('Total Peminjam', User::where('role', 'siswa')->count()),
            Stat::make('Total Users', User::count()),
            Stat::make('Sedang Dipinjam', Loan::where('status', 'dipinjam')->count()),
        ];
    }
}
