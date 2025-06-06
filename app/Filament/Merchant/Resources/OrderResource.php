<?php

namespace App\Filament\Merchant\Resources;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Filament\Merchant\Resources\OrderResource\Pages;
use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Infolists\Components\OrderNoteEntry;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'store',
                'user',
                'address',
            ])
            ->whereRelation('store', 'user_id', '=', auth()->id());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
            Section::make('Information')
                ->description('Basic information for the order')
                ->columns(2)
                ->schema([
                    Group::make([
                        TextEntry::make('id')
                            ->columnSpan(1)
                            ->label('Order ID')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->formatStateUsing(fn ($state): string => "#$state"),
                        TextEntry::make('store')
                            ->columnSpan(1)
                            ->label('Store')
                            ->html()
                            ->formatStateUsing(function ($state) {
                                return new HtmlString(
                                    "<div class='flex items-center gap-2'>
                                    <img src='" . $state->logo . "' alt='Store Logo' class='w-8 h-8 rounded-full mr-2'>
                                    <span class='font-semibold'>" . $state->name . "</span>
                                </div>"
                                );
                            }),
                        TextEntry::make('user')
                            ->columnSpan(1)
                            ->label('User')
                            ->html()
                            ->formatStateUsing(function ($state) {
                                return new HtmlString(
                                    "<div class='flex items-center gap-2'>
                                            <img src='" . $state->avatar . "' alt='User avatar' class='w-8 h-8 rounded-full mr-2'>
                                            <span class='font-semibold'>".$state->name." - ".$state->email."</span>
                                        </div>"
                                );
                            }),
                        ])
                        ->columnSpan(2)
                        ->columns(3),
                    OrderNoteEntry::make("note")
                        ->hidden(fn ($record): bool => !$record->note)
                        ->columnSpan(2),
                    TextEntry::make('address.full_address')
                        ->columnSpan(1)
                        ->label('Address'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(function ($state) {
                            return OrderStatus::from($state)->color();
                        }),
                ]),
            Section::make('Payment')
                ->collapsible()
                ->collapsed()
                ->description('Payment method, payment status, product price, total price and tip')
                ->columns(2)
                ->schema([
                    TextEntry::make('payment_method')
                        ->columnSpan(1)
                        ->formatStateUsing(function ($state) {
                            return match ($state) {
                                'card' => 'Credit Card',
                                'cod' => 'Cash on Delivery',
                                default => 'Unknown',
                            };
                        }),
                    TextEntry::make('payment_status')
                        ->columnSpan(1)
                        ->badge()
                        ->color(function ($state) {
                            return PaymentStatus::from($state)->color();
                        }),

                    TextEntry::make('total_price')
                        ->columnSpan(1)
                        ->money('EUR'),
                    TextEntry::make('products_price')
                        ->columnSpan(1)
                        ->money('EUR'),

                    TextEntry::make('discount')
                        ->columnSpan(1)
                        ->money('EUR'),
                    TextEntry::make('tip')
                        ->columnSpan(1)
                        ->money('EUR'),
                ]),
            Section::make('Shipping')
                ->collapsible()
                ->collapsed()
                ->description('Shipping method, shipping status, shipping price and delivery time')
                ->columns(2)
                ->schema([
                    TextEntry::make('shipping_method')
                        ->columnSpan(1)
                        ->formatStateUsing(function ($state) {
                            return match ($state) {
                                'delivery' => 'Delivery',
                                'takeaway' => 'Takeaway',
                                default => 'Unknown',
                            };
                        }),
                    TextEntry::make('shipping_status')
                        ->columnSpan(1)
                        ->badge()
                        ->color(function ($state) {
                            return ShippingStatus::from($state)->color();
                        }),

                    TextEntry::make('shipping_price')
                        ->columnSpan(1)
                        ->money('EUR'),
                    TextEntry::make('delivery_time')
                        ->columnSpan(1)
                        ->formatStateUsing(function ($state) {
                            return "$state minutes";
                        }),
                ]),
            Section::make('Products')
                ->columns(1)
                ->schema([
                    RepeatableEntry::make('products')
                        ->schema([
                            ImageEntry::make('product.main_image')
                                ->width("100%"),
                            TextEntry::make('product_name'),
                            TextEntry::make('quantity')
                                ->formatStateUsing(fn ($state): string => "x $state"),
                            TextEntry::make('price')
                                ->label("Unit price")
                                ->money('EUR'),
                            TextEntry::make('total_price')
                                ->money('EUR'),

                            OrderNoteEntry::make('note')
                                ->hidden(fn ($record): bool => !$record->note)
                                ->columnSpanFull(),
                        ])
                        ->columns(5)
                ]),
            Actions::make([
                Action::make('status')
                    ->color("info")
                    ->label("Start processing")
                    ->visible(fn (Model $record): bool => $record->status === OrderStatus::pending->value)
                    ->icon('heroicon-s-play')
                    ->action(function (Model $record) {
                        $record->status = OrderStatus::processing->value;
                        $record->save();
                    }),
                Action::make('status')
                    ->color("success")
                    ->label("Out for delivery")
                    ->visible(fn (Model $record): bool => $record->status === OrderStatus::processing->value)
                    ->icon('heroicon-s-arrow-up-right')
                    ->action(function (Model $record) {
                        $record->status = OrderStatus::outForDelivery->value;
                        $record->save();
                    }),
                Action::make('status')
                    ->color("danger")
                    ->label("Cancel order")
                    ->icon('heroicon-o-x-mark')
                    ->hidden(fn (Model $record): bool => $record->status === OrderStatus::completed->value || $record->status === OrderStatus::cancelled->value)
                    ->action(function (Model $record) {
                        $record->status = OrderStatus::cancelled->value;
                        $record->save();
                    }),
            ])
            ->columnSpanFull()
            ->alignEnd(),
        ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('store.name')
                    ->label('Store Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address.full_address'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->since(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Order Status')
                    ->badge()
                    ->color(function ($state) {
                        $enum = OrderStatus::from($state);
                        return $enum->color();
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('payment_method')
                    ->label('Payment Method')
                    ->icon(function ($state) {
                        $enum = PaymentMethod::from($state);
                        return $enum->icon();
                    })
                    ->color(function ($state) {
                        $enum = PaymentMethod::from($state);
                        return $enum->color();
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(function ($state) {
                        $enum = PaymentStatus::from($state);
                        return $enum->color();
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->label('Shipping Method')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('shipping_status')
                    ->label('Shipping Status')
                    ->badge()
                    ->color(function ($state) {
                        $enum = ShippingStatus::from($state);
                        return $enum->color();
                    })
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Amount')
                    ->sortable()
                    ->searchable()
                    ->money('EUR', true),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(
                        collect(OrderStatus::cases())
                            ->mapWithKeys(fn($status) => [$status->value => $status->name])
                            ->toArray()
                    )
                    ->label('Order Status')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(
                        collect(PaymentStatus::cases())
                            ->mapWithKeys(fn($status) => [$status->value => $status->name])
                            ->toArray()
                    )
                    ->label('Payment Status')
                    ->searchable(),
                Tables\Filters\Filter::make('total_price')
                    ->form([
                        Forms\Components\TextInput::make('min')
                            ->label('Minimum Amount')
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $max = $get('max');
                                $min = $get('min');
                                if ($min <= 0) {
                                    $set('min', null);
                                }
                                if ($max !== null && $min > $max && $min !== null) {
                                    $set('max', $min);
                                }
                            })
                            ->live(onBlur: true)
                            ->minValue(0)
                            ->suffix('€')
                            ->numeric(),
                        Forms\Components\TextInput::make('max')
                            ->label('Maximum Amount')
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $min = $get('min');
                                $max = $get('max');
                                if ($max <= 0) {
                                    $set('max', null);
                                }
                                if ($min > $max) {
                                    $set('max', null);
                                }
                            })
                            ->live(onBlur: true)
                            ->minValue(0)
                            ->suffix('€')
                            ->numeric(),
                    ])
                    ->indicateUsing(function ($data) {
                        if (isset($data['min']) && isset($data['max'])) {
                            return 'Total amount between €' . $data['min'] . ' and €' . $data['max'];
                        } elseif (isset($data['min'])) {
                            return 'Total amount greater than or equal to €' . $data['min'];
                        } elseif (isset($data['max'])) {
                            return 'Total amount less than or equal to €' . $data['max'];
                        }
                        return null;
                    })
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['min'])) {
                            $query->where('total_price', '>=', $data['min']);
                        }
                        if (isset($data['max'])) {
                            $query->where('total_price', '<=', $data['max']);
                        }
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
