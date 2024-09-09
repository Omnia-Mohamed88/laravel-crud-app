<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use App\Utils\Constants;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['title'];

    // protected $appends = ["attachments_data","active_label"];

    protected $appends = ["attachments", "active_label"];

    protected function getAttachmentsAttribute(): ?Collection
    {
        return $this->attachments()?->get();
    }

    protected function getActiveLabelAttribute(): string
    {
        return $this->active == Constants::$CATEGORY_STATUS_ACTIVE ? Constants::$CATEGORY_STATUS_ACTIVE_LABEL : Constants::$CATEGORY_STATUS_INACTIVE_LABEL;
    }

    // protected function attachmentsData(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->attachments()?->get(),
    //     );
    // }

    // protected function activeLabel(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn () => $this->active == Constants::$CATEGORY_STATUS_ACTIVE ? Constants::$CATEGORY_STATUS_ACTIVE_LABEL : Constants::$CATEGORY_STATUS_INACTIVE_LABEL
    //     );
    // }

    /**
     * Get the products for the category.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the attachments for the category.
     *
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function boot(): void
    {
        parent::boot();
        self::created(function ($model) {
            if (request()->has("attachments")) {
                foreach (request()->attachments as $attachment) {
                    $model->attachments()->create($attachment);
                }
            }
        });
    }

    //Local

    public function scopeActive(Builder $query): void
    {
        $query->where('active', Constants::$CATEGORY_STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): void
    {
        $query->where('active', Constants::$CATEGORY_STATUS_INACTIVE);
    }

    //Global
    protected static function booted()
    {
        //This is just an example of making Global Scope
//        static::addGlobalScope(new ActiveScope);
    }
}


