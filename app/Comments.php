<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use JamesDordoy\LaravelVueDatatable\Traits\LaravelVueDatatableTrait;

class Comments extends Model
{
    use LaravelVueDatatableTrait;

    protected $fillable = ['user_id', 'products_id', 'title', 'description', 'status', 'rate'];

    protected $dataTableColumns = [
        'id' => [
            'searchable' => true,
        ],
        'user_id' => [
            'searchable' => true,
        ],
        'products_id' => [
            'searchable' => true,
        ],
        'title' => [
            'searchable' => true,
        ],
        'rate' => [
            'searchable' => true,
        ],
        'status' => [
            'searchable' => true,
        ],
        'created_at' => [
            'searchable' => true,
        ],
        'updated_at' => [
            'searchable' => true,
        ]
    ];

    public function products()
    {
        return $this->belongsTo('App\Products');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

}