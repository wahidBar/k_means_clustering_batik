<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatikProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_id',
        'type_id',
        'product_name',
        'description',
        'price',
        'image'
    ];

    public function partner()
    {
        return $this->belongsTo(BatikUmkmPartner::class, 'partner_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function monthlyProductions()
    {
        return $this->hasMany(MonthlyProduction::class, 'product_id');
    }
}
