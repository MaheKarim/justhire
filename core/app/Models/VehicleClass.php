<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class VehicleClass extends Model
{
    use GlobalStatus;

    public function vehicleType() {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }
}
