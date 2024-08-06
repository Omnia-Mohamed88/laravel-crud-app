<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;
    protected $fillable = ['file_path'];

    public function attachable() // this method defines that Attachement can belong to any model 
    {
        return $this->morphTo();
    }
    
}
