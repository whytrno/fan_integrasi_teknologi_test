<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Epresence extends Model
{
    public $fillable = ['user_id', 'type', 'waktu', 'is_approve'];

    public function getWaktuDateAttribute()
    {
        return Carbon::parse($this->waktu)->toDateString();
    }

    public function getWaktuTimeAttribute()
    {
        return Carbon::parse($this->waktu)->toTimeString();
    }

    public function getStatusAttribute()
    {
        return $this->is_approve ? 'APPROVE' : 'REJECT';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
