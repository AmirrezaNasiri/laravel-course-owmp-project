<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $dates = ['deadline'];
    protected $casts = [
        'status' => TaskStatus::class
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
