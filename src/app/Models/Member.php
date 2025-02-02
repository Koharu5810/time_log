<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
    ];
    protected $hidden = [
        'password',
    ];
    // UUIDを作自動成
    protected static function booted()
    {
        static::creating(function ($member) {
            if (empty($member->uuid)) {
                $member->uuid = (string) Str::uuid();
            }
        });
    }

    public function setPasswordAttribute($value)
    {
        if (Hash::needsRehash($value)) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'member_id');
    }
    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class, 'member_id');
    }
}
