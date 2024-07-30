<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model {
    use GlobalStatus;

    public function vehicleZone() {
        return $this->hasMany(VehicleZone::class);
    }

    public function locations() {
        return $this->hasManyThrough(Location::class, User::class, 'zone_id', 'user_id', 'id', 'id');
    }
}
