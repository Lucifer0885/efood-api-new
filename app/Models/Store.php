<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'delivery_range', //km
        'active'
    ];

    protected $translatable = ['name', 'address'];

    protected $casts = [
        'working_hours' => 'array',
        'active' => 'boolean',
    ];

    protected $hidden = ['pivot'];

    // protected $appends = ['logo', 'cover'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getLogoAttribute(){
        return 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }

    public function getCoverAttribute(){
        return 'https://png.pngtree.com/png-clipart/20190614/original/pngtree-vector-list-icon-png-image_3785548.jpg';
    }

        public function getWorkingHoursAttribute($value)
    {
        $hours = json_decode($value, true);
        // Reorder each day's hours
        foreach ($hours as $day => $timeSlot) {
            // Create a new array with keys in desired order
            $orderedTimeSlot = [
                'start' => $timeSlot['start'],
                'end' => $timeSlot['end']
            ];
            $hours[$day] = $orderedTimeSlot;
        }
        return $hours;
    }

    public function setWorkingHoursAttribute($value)
    {
        // If $value is already an array (when setting via $store->working_hours = $array)
        if (is_array($value)) {
            $orderedHours = [];
            foreach ($value as $day => $timeSlot) {
                // Create a new ordered array for each day
                $orderedHours[$day] = [
                    'start' => $timeSlot['start'],
                    'end' => $timeSlot['end']
                ];
            }
            // Convert to JSON with options to preserve the order
            $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
            $orderedJson = json_encode($orderedHours, $jsonOptions);
            // Set the attribute value to use our custom JSON
            $this->attributes['working_hours'] = $orderedJson;
        } else {
            // If it's already a JSON string, just set it
            $this->attributes['working_hours'] = $value;
        }
    }
}
