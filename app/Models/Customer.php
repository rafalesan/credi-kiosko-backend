<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCustomer
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function cutoffs() {
        return $this->hasMany(Cut::class);
    }

    public function credits() {
        return $this->hasMany(Credit::class);
    }

    public function businesses() {
        return $this->belongsToMany(Business::class);
    }

}
