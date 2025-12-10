<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockImport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'import_date',
        'product_id',
        'quantity',
        'cost_price',
        'total_cost',
        'importer_user_id',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'import_date' => 'date',
        'cost_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    /**
     * Get the product that was imported.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who imported the stock.
     */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'importer_user_id');
    }
}
