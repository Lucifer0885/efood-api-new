<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StoreResource\Pages;
use App\Filament\Admin\Resources\StoreResource\RelationManagers;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use ArberMustafa\FilamentLocationPickrField\Forms\Components\LocationPickr;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

//import relation managers


class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Stores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Store Information')
                    ->aside()
                    ->description('Fill in the details of your store.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Store Name')
                            ->required()
                            ->maxLength(255)
                            ->translatable(),
                        Forms\Components\TextInput::make('address')
                            ->label('Store Address')
                            ->required()
                            ->maxLength(255)
                            ->translatable(),
                        Forms\Components\Select::make('categories')
                            ->label('Store Categories')
                            ->multiple()
                            ->relationship('categories', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Store Phone')
                            ->tel()
                            ->maxLength(15),
                    ]),
                Forms\Components\Section::make('Operating Information')
                    ->aside()
                    ->description('Set the operational parameters of your store.')
                    ->schema([
                        Forms\Components\TextInput::make('minimum_cart_value')
                            ->label('Minimum Cart Value')
                            ->suffix('€')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('delivery_range')
                            ->label('Delivery Range')
                            ->suffix('km')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('working_hours')
                            ->label('Working Hours'),
                        // Forms\Components\Toggle::make('active')
                        //     ->label('Store Active Status')
                        //     ->default(true),
                    ]),
                Forms\Components\Section::make('Location')
                    ->aside()
                    ->description('Set the geographical location of your store.')
                    ->schema([
                        LocationPickr::make('location')
                            ->height(config('filament-locationpickr-field.default_height'))
                            ->defaultLocation(config('filament-locationpickr-field.default_location'))
                            ->defaultZoom(config('filament-locationpickr-field.default_zoom'))
                            ->draggable()
                    ]),
                Forms\Components\Section::make('Photos')
                    ->aside()
                    ->description('Upload images for your store.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->collection('logo')
                            ->label('Store Logo'),
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                            ->label('Store Cover Image'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->collection('logo')
                    ->label('Logo')
                    ->circular()
                    ->size(50),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->description(fn (Store $record) => $record->user->email)
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Store Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Store Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->label('Categories')
                    ->searchable(),
                Tables\Columns\TextColumn::make('minimum_cart_value')
                    ->label('Minimum Cart Value')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_range')
                    ->label('Delivery Range')
                    ->suffix(' km')
                    ->sortable(),
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
            RelationManagers\ProductCategoriesRelationManager::class,
            RelationManagers\ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
