<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasTranslations;

    protected $translatable = ['name'];

    protected $fillable = ['name'];

    protected $appends = ['icon'];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class);
    }

    public function getIconAttribute(){
        return 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }
}
