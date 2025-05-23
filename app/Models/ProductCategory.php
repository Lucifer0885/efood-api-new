<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'store_id',
        'sort',
    ];

    protected $translatable = ['name'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products(): hasMany
    {
        return $this->hasMany(Product::class);
    }
}
