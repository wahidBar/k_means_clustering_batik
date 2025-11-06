<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyProduction extends Model
{
    use HasFactory;

    protected $table = 'monthly_production';
    protected $primaryKey = 'production_id';

    protected $fillable = [
        'partner_id',
        'product_id',
        'month',
        'total_quantity',
        'production_notes',
        'validation_status',
        'validated_by',
        'validation_date',
    ];

    // 🔗 Relasi ke Partner (UMKM)
    public function partner()
    {
        return $this->belongsTo(BatikUmkmPartner::class, 'partner_id');
    }

    // 🔗 Relasi ke Produk Batik
    public function product()
    {
        return $this->belongsTo(BatikProduct::class, 'product_id');
    }

    // 🔗 Relasi ke User sebagai validator (admin)
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
