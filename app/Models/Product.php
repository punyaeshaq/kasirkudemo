<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Get the product's image URL.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function image(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn($value) => $value ? url($value) : null,
        );
    }

    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'price',
        'stock',
        'image'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->barcode)) {
                $product->barcode = self::generateBarcode();
            }
        });
    }

    /**
     * Generate a unique barcode (EAN-13 format without check digit)
     */
    public static function generateBarcode(): string
    {
        do {
            // Generate 12-digit barcode starting with 200 (internal use prefix)
            $barcode = '200' . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function decrementStock(int $quantity): void
    {
        $this->decrement('stock', $quantity);
    }

    public function incrementStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }
}
