<?php

namespace App\Filament\Merchant\Resources\OrderResource\Pages;

use App\Filament\Merchant\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
