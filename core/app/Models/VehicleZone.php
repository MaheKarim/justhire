<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class VehicleZone extends Model
{
    use GlobalStatus;

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }
}
