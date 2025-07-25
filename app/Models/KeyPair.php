<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KeyPair extends Model
{
    use HasFactory;

    protected $fillable = ['public_key', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}