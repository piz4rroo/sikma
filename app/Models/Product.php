<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock',
        'sku',
        'category_id',
        'brand_id',
        'is_featured',
        'is_active',
        'weight',
        'dimensions',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'sale_price' => 'float',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'weight' => 'float',
        'dimensions' => 'json',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the product's images.
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the product's reviews.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the product's variants.
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * The tags that belong to the product.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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
     * Check if the product is on sale.
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
     * Scope a query to only include active products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to only include featured products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    /**
     * Scope a query to only include products with stock.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include sale products.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnSale($query)
    {
        return $query->where('sale_price', '>', 0)
                    ->whereRaw('sale_price < price');
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
     * Get the formatted current price.
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
     * Check if product is in stock.
     *
     * @return bool
     */
    public function getStockStatusAttribute()
    {
        return $this->stock > 0 ? 'In Stock' : 'Out of Stock';
    }
}