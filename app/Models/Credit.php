<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function customer() {
        return $this->hasOne(Customer::class);
    }

    public function user() {
        return $this->hasOne(User::class);
    }

    public function cutoff() {
        return $this->hasOne(Cut::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class)
                    ->withPivot(['product_name',
                                 'price',
                                 'quantity',
                                 'total']);
    }

}
