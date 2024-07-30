<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Review extends Model {
    use GlobalStatus;

    public function rental() {
        return $this->belongsTo(Rental::class);
    }
    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
