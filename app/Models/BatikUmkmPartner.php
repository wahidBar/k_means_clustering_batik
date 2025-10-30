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

    protected $casts = [
        'pemasaran' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'business_name',
        'owner_name',
        'pemasaran',
        'address',
        'contact',
        'nib',
        'description',
        'images_partner',
        'latitude',
        'longitude',
        'validation_status',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function validationHistories()
    {
        return $this->hasMany(ValidationHistory::class, 'partner_id', 'partner_id');
    }

    public function latestValidation()
    {
        return $this->hasOne(ValidationHistory::class, 'partner_id')->latestOfMany();
    }
}
