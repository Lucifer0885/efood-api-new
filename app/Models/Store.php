<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Store extends Model
{
    use HasTranslations;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'minimum_cart_value',
        'latitude',
        'longitude',
        'working_hours',
        'delivery_range',
        'active'
    ];

    protected $translatable = ['name', 'address'];

    protected $casts = [
        'working_hours' => 'array',
        'active' => 'boolean',
    ];

    protected $appends = ['logo', 'cover'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function getLogoAttribute(){
        return 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }

    public function getCoverAttribute(){
        return 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }
}
