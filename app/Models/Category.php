<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['title'];
    protected $appends = ["attachments_data"];

    protected function attachmentsData(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attachments()?->get(),
        );
    }

    /**
     * Get the products for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the attachments for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
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


