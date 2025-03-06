<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventsResource\Pages;
use App\Filament\Resources\EventsResource\RelationManagers;
use App\Models\Events;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventsResource extends Resource
{
    protected static ?string $model = Events::class;

    protected static ?string $navigationLabel = "場次列表";
    protected static ?string $navigationGroup = "場次";

    protected static ?string $modelLabel = "場次";
    protected static ?string $navigationIcon = 'heroicon-s-rectangle-group';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        if(auth()->user()->user_role !== 'admin')
        {
            $query->where('owner_id', auth()->user()->id);
        }
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\TextInput::make('event_name')
                        ->label('場次名稱')
                        ->required()
                        ->maxLength(50),
                    Forms\Components\DatePicker::make('event_date')
                        ->label('場次時間')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('場次編號'),
                Tables\Columns\TextColumn::make('event_name')
                    ->label('場次名稱')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event_date')
                    ->label('場次時間')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('storeUrl')
                    ->label('庫存網址')
                    ->getStateUsing(function ($record): string{
                        $owner = User::find($record->owner_id);
                        return env('APP_URL').'cashier/show/'.$record->id.'/'.$owner->name;
                    })
                    ->url(
                        function ($record) :string{
                            $owner = User::find($record->owner_id);
                            return env('APP_URL').'cashier/show/'.$record->id.'/'.$owner->name;
                        }
                    ),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvents::route('/create'),
            'edit' => Pages\EditEvents::route('/{record}/edit'),
        ];
    }
}
