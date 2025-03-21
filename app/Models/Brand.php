<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str; // Tambahkan ini

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'website',
        'is_featured',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'position'
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer'
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    public function getActiveProductCountAttribute()
    {
        return $this->products()->where('is_active', 1)->count();
    }

    public function getLogoUrlAttribute()
    {
        if (!$this->logo) {
            return asset('images/no-image.png');
        }
        
        return asset($this->logo);
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating a new brand
        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name); // Gunakan Str::slug()
            }
        });

        // Auto-update slug when updating brand name
        static::updating(function ($brand) {
            if ($brand->isDirty('name') && !$brand->isDirty('slug')) {
                $brand->slug = Str::slug($brand->name); // Gunakan Str::slug()
            }
        });
    }
}
