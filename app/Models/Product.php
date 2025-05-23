<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'description',
        'price',
        'store_id',
        'product_category_id',
        'active',
        'sort',
    ];

    protected $translatable = ['name', 'description'];

    protected $casts = [
        'active' => 'boolean',
        'sort'=> 'integer',
        'price' => 'float',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

}
