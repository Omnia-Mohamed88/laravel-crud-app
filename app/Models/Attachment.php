<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = ['file_path'];

    /**
     * Get the parent model that owns the attachment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
