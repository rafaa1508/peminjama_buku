<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('author')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('publisher')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('year')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(9999),
                TextInput::make('stock')
                    ->required()
                    ->integer()
                    ->minValue(0)
                    ->maxValue(2147483647)
                    ->default(0),
            ]);
    }
}
