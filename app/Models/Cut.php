<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCut
 */
class Cut extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function arrears() {
        return $this->hasMany(Arrear::class);
    }

    public function customer() {
        return $this->hasOne(Customer::class);
    }

}
