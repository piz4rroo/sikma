<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'image',
        'title',
        'alt',
        'position',
        'is_primary'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'position' => 'integer',
        'is_primary' => 'boolean'
    ];

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the image URL.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/no-image.png');
        }
        
        return asset($this->image);
    }

    /**
     * Get the thumbnail URL.
     *
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/no-image.png');
        }
        
        $path_parts = pathinfo($this->image);
        $thumbnailPath = $path_parts['dirname'] . '/thumbnails/' . $path_parts['basename'];
        
        if (file_exists(public_path($thumbnailPath))) {
            return asset($thumbnailPath);
        }
        
        return asset($this->image);
    }

    /**
     * Set the image as primary.
     *
     * @return void
     */
    public function setPrimary()
    {
        // First, set all images for this product as not primary
        self::where('product_id', $this->product_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
            
        // Then set this one as primary
        $this->is_primary = true;
        $this->save();
    }

    /**
     * Scope a query to only include primary images.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', 1);
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        // Set position automatically when creating a new image
        static::creating(function ($image) {
            if (empty($image->position)) {
                $lastPosition = self::where('product_id', $image->product_id)
                    ->max('position');
                $image->position = $lastPosition ? $lastPosition + 1 : 1;
            }
            
            // If this is the first image for the product, set it as primary
            if (!self::where('product_id', $image->product_id)->exists()) {
                $image->is_primary = true;
            }
        });
        
        // When deleting an image that is primary, set another one as primary
        static::deleted(function ($image) {
            if ($image->is_primary) {
                $newPrimaryImage = self::where('product_id', $image->product_id)
                    ->where('id', '!=', $image->id)
                    ->orderBy('position')
                    ->first();
                    
                if ($newPrimaryImage) {
                    $newPrimaryImage->is_primary = true;
                    $newPrimaryImage->save();
                }
            }
        });
    }
}