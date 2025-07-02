<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model{
    protected $guarded=["id"];

    public function client(){
        return $this->belongsTo(Client::class);
    }

    public function details(){
        return $this->hasMany(Detail::class);
    }
}
