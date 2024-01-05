<?php

namespace App;

use App\BrandPriceLinkProducts;
use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Products extends Model
{
    use LaravelVueDatatableTrait;

    protected $fillable = ['name', 'description', 'categories_id', 'brands_id', 'price', 'image_url', 'product_url', 'like', 'evaluation'];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => true,
        ],
        'name' => [
            'searchable' => true,
        ],
        'description' => [
            'searchable' => true,
        ],
        'created_at' => [
            'searchable' => true,
        ],
        'updated_at' => [
            'searchable' => true,
        ]
    ];

    public function categories()
    {
        return $this->hasMany('App\Categories');
    }

    public function brands()
    {
        return $this->hasMany('App\Brands');
    }

    public function brandPriceLinkProducts()
    {
        return $this->hasMany(BrandPriceLinkProducts::class);
    }

    public function comments()
    {
        return $this->hasMany('App\Comments');
    }
}
