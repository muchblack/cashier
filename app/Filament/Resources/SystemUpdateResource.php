<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemUpdateResource\Pages;
use App\Filament\Resources\SystemUpdateResource\RelationManagers;
use App\Models\SystemUpdate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SystemUpdateResource extends Resource
{
    protected static ?string $model = SystemUpdate::class;

    protected static ?string $navigationLabel = "更新公告";
    protected static ?string $navigationGroup = "管理";
    protected static ?string $modelLabel = "更新公告";

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()->user_role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('說明')
                        ->required()
                        ->maxLength(50),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('說明')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemUpdates::route('/'),
            'create' => Pages\CreateSystemUpdate::route('/create'),
            'edit' => Pages\EditSystemUpdate::route('/{record}/edit'),
        ];
    }
}
