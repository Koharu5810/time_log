<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_time_start',
        'break_time_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // 休憩時間の計算
    public function getDurationInMinutesAttribute()
    {
        $start = Carbon::parse($this->break_time_start);
        $end = Carbon::parse($this->break_time_end);
        return $start->diffInMinutes($end);
    }
}
