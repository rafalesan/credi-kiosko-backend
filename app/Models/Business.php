<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperBusiness
 */
class Business extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function credits() {
        return $this->hasMany(Credit::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function customers() : BelongsToMany {
        return $this->belongsToMany(Customer::class);
    }

    public function customersWithPivot() : BelongsToMany {
        return $this->belongsToMany(Customer::class)
                    ->whereNull('business_customer.deleted_at')
                    ->withPivot(['id',
                                 'business_customer_name',
                                 'business_customer_nickname'])
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    public function customersWithPivotWithTrashed() : BelongsToMany {
        return $this->belongsToMany(Customer::class)
                    ->withPivot(['id',
                                 'business_customer_name',
                                 'business_customer_nickname'])
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

}
