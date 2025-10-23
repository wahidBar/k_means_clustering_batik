<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatikUmkmPartner extends Model
{
    use HasFactory;

    protected $table = 'batik_umkm_partner';
    protected $primaryKey = 'partner_id';

    protected $fillable = [
        'user_id',
        'business_name',
        'owner_name',
        'address',
        'contact',
        'description',
        'latitude',
        'longitude',
        'validation_status',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
