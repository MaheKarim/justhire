<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use GlobalStatus;

    public function vehicleClass() {
        return $this->hasMany(VehicleClass::class);
    }
    public function vehicles() {
        return $this->hasMany(Vehicle::class);
    }
}
