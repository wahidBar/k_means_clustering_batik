<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationHistory extends Model
{
    use HasFactory;

    protected $table = 'validation_history';
    protected $primaryKey = 'validation_id';

    protected $fillable = [
        'partner_id',
        'user_id',
        'validation_date',
        'status',
        'note',
    ];

    // Relasi ke partner
    public function partner()
    {
        return $this->belongsTo(BatikUmkmPartner::class, 'partner_id');
    }

    // Relasi ke user (admin yang memverifikasi)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
