<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'sale_price',
        'stock',
        'is_active',
        'image',
        'attributes'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'attributes' => 'json',
    ];

    /**
     * Get the product that owns the variant.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get current price (either sale price or regular price).
     *
     * @return float
     */
    public function getCurrentPriceAttribute()
    {
        return $this->sale_price > 0 ? $this->sale_price : $this->price;
    }

    /**
     * Check if the variant is on sale.
     *
     * @return bool
     */
    public function getIsOnSaleAttribute()
    {
        return $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    /**
     * Get the discount percentage.
     *
     * @return float
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->is_on_sale) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        
        return 0;
    }

    /**
     * Format the price with currency symbol.
     *
     * @param  float  $price
     * @return string
     */
    public function formatPrice($price)
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Get the formatted price.
     *
     * @return string
     */
    public function getFormattedPriceAttribute()
    {
        return $this->formatPrice($this->price);
    }

    /**
     * Get the formatted sale price.
     *
     * @return string
     */
    public function getFormattedSalePriceAttribute()
    {
        return $this->formatPrice($this->sale_price);
    }

    /**
     * Get the formatted current price.
     *
     * @return string
     */
    public function getFormattedCurrentPriceAttribute()
    {
        return $this->formatPrice($this->current_price);
    }

    /**
     * Get a specific attribute value.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // Check if key exists in attributes JSON
        if ($value === null && $this->attributes !== null) {
            $attributesArray = $this->attributes;
            if (is_array($attributesArray) && array_key_exists($key, $attributesArray)) {
                return $attributesArray[$key];
            }
        }
        
        return $value;
    }

    /**
     * Get variant attribute options as a comma-separated string.
     *
     * @return string
     */
    public function getAttributeOptionsAttribute()
    {
        if (empty($this->attributes)) {
            return '';
        }
        
        $attributesArray = $this->attributes;
        $options = [];
        
        foreach ($attributesArray as $name => $value) {
            $options[] = $name . ': ' . $value;
        }
        
        return implode(', ', $options);
    }

    /**
     * Check if variant is in stock.
     *
     * @return bool
     */
    public function getInStockAttribute()
    {
        return $this->stock > 0;
    }

    /**
     * Get stock status text.
     *
     * @return string
     */
    public function getStockStatusAttribute()
    {
        return $this->stock > 0 ? 'In Stock' : 'Out of Stock';
    }

    /**
     * Scope a query to only include active variants.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include variants with stock.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}