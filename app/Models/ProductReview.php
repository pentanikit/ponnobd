<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use HasFactory;

public function product()
{
    return $this->belongsTo(\App\Models\Product::class, 'product_id');
}

public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'user_id');
}

protected $fillable = ['product_id','user_id', 'guest_name', 'rating','comment','status','viewed'];
}
