<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailToken extends Model
{
    use HasFactory;
    
    protected $table = "emails_tokens";

    protected $fillable = [
        "email",
        "token"
    ];
}
