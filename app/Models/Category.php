<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $translatable = ['name'];

    protected $fillable = ['name'];

    protected $appends = ['icon'];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class);
    }

    public function getIconAttribute(){
        $icon = $this->getFirstMediaUrl('icon');
        return $icon ?? 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }
}
