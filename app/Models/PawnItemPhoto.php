<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PawnItemPhoto extends Model
{
    protected $fillable = ['pawn_item_id', 'path'];
}