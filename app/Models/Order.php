<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sebdesign\VivaPayments\Facades\Viva;
use Sebdesign\VivaPayments\Requests\CreatePaymentOrder;
use Sebdesign\VivaPayments\Requests\Customer;
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'address_id',
        'driver_id',
        'status',
        'payment_id',
        'payment_method',
        'payment_status',
        'shipping_method',
        'shipping_status',
        'delivery_time',
        'products_price',
        'shipping_price',
        'coupon_code',
        'discount',
        'tip',
        'total_price',
        'note',
    ];

    protected $casts = [
        'total_price' => 'float',
        'products_price' => 'float',
        'shipping_price' => 'float',
        'discount' => 'float',
        'tip' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_code', 'code');
    }

    public function createVivaCode(): void{
        $this->payment_id = Viva::orders()->create(new CreatePaymentOrder(
            amount: abs($this->total_price * 100),
            sourceCode: config('services.viva.source_code'),
            customer: new Customer(
                email: $this->user->email,
                fullName: $this->user->name,
                countryCode: 'GR',
                requestLang: 'el-GR',
            ),
            customerTrns: "Παραγγελία #{$this->id} από το {$this->store->name}",
            merchantTrns: "order:{$this->id}",
        ));
        $this->save();
    }

    public function getVivaUrl(): string
    {
        $redirectUrl = Viva::orders()->redirectUrl(
            ref: $this->payment_id,
            color: 'ef000d'
        );

        return $redirectUrl;
    }
}
