<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'attendance_id',
        'request_type',
        'requested_clock_in',
        'requested_clock_out',
        'requested_remarks',
        'status',
        'admin_id',
        'approved_at',
    ];
    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    // 勤務時間の計算
    public function getDurationInMinutesAttribute()
    {
        if ($this->requested_clock_in && $this->requested_clock_out) {
            $start = Carbon::parse($this->requested_clock_in);
            $end = Carbon::parse($this->requested_clock_out);
            return $start->diffInMinutes($end);
        }
        return 0; // 両方の値が揃っていない場合は0を返す
    }
}
