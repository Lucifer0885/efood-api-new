<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

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

    public function getMainImageAttribute(){
        $image = $this->getMedia('gallery')
            ->sortByDesc('order_column')
            ->first();
        return $image?->getUrl() ?? 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }

    public function getGalleryAttribute(){
        $gallery = $this->getMedia('gallery')
            ->sortByDesc('order_column');
        return $gallery ?? ['https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg'];
    }

}
