<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'price', 'category_id'];

    protected $appends = ["attachments_data"];

    protected function attachmentsData(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attachments()?->get(),
        );
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the attachments for the product.
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
            if(request()->has("attachments")){
                foreach(request()->attachments as $attachment)
                {
                    $model->attachments()->create($attachment);
                }
            }
        });
    }
}
