<?php

namespace App\Traits;
use Spatie\Translatable\HasTranslations as BaseHasTranslations;

trait HasTranslations{
    use BaseHasTranslations;

    public function toArray()
    {
        $attributes =  $this->attributesToArray();
        $translatables = array_filter($this->getTranslatableAttributes(), function ($key) use ($attributes){
            return array_key_exists($key, $attributes);
        });
        foreach ($translatables as $field) {
            $attributes[$field] = $this->getTranslation($field, app()->getLocale());
        }
        return array_merge($attributes, $this->relationsToArray());
    }
}