<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'method',
        'amount',
        'reference',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function markAsSuccess(): void
    {
        $this->update(['status' => 'success']);
        $this->transaction->markAsPaid();
    }
}
