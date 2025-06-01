<?php

namespace App\Filament\Admin\Resources\StoreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;
use App\Models\Product;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->aside()
                    ->description('Name and other details of the product.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->translatable(),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(255)
                            ->translatable(),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->suffix('€')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Forms\Components\Select::make('product_category_id')
                            ->label('Category')
                            ->relationship(
                                name: 'productCategory',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->where("store_id", $this->getOwnerRecord()->id),
                            )
                            ->native(false)
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Photos')
                    ->aside()
                    ->description('Add photos.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->collection('gallery')
                            ->multiple()
                            ->reorderable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->reorderable('sort')
            ->defaultGroup('productCategory.name')
            ->groups([
                Group::make('productCategory.name')
                    ->titlePrefixedWithLabel(false)
                // ->orderQueryUsing(fn (Builder $query, string $direction) => $query
                //     ->join('product_categories', 'product_categories.id', '=', 'products.id')
                //     ->orderBy('product_categories.sort')
                // ),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('mainImage')
                    ->label('Photos')
                    ->circular()
                    ->rounded()
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn(Product $record) => $record->description),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('EUR')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
