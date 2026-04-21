<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdultHomeSection extends Model
{
    protected $fillable = ['title', 'type', 'order', 'is_active', 'limit'];
}
