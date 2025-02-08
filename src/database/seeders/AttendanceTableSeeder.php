<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Member;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $members = Member::all(); // 既存のメンバーを取得

        foreach ($members as $member) {
            // 各IDに対して異なる勤務状況を作成
            $this->createAttendance($member->id, 1, '勤務外', 0);         // ID1: 勤務前
            $this->createAttendance($member->id, 2, '出勤中', 180);      // ID2: 勤務中（休憩前）
            $this->createBreakTimeStatus($member->id, 3, '休憩中', 240); // ID3: 休憩中
            $this->createAttendance($member->id, 4, '出勤中', 300);      // ID4: 勤務中（休憩後）
            $this->createBreakTimeStatus($member->id, 5, '休憩中', 420); // ID5: 2回目の休憩中
            $this->createAttendance($member->id, 6, '退勤済', 480);      // ID6: 勤務外（勤務時間8時間）

            for ($i = 7; $i <= 10; $i++) {
                $this->createRandomAttendance($member->id, $i);
            }
        }
    }

    private function createAttendance($memberId, $id, $status, $workMinutes)
    {
        $workDate = Carbon::now()->subDays(1);
        $clockIn = $workMinutes > 0 ? Carbon::createFromTime(9, 0) : null;
        $clockEnd = $clockIn ? (clone $clockIn)->addMinutes($workMinutes) : null;

        $attendance = Attendance::create([
            'id' => $id,
            'member_id' => $memberId,
            'work_date' => $workDate->format('Y-m-d'),
            'clock_in' => $clockIn ? $clockIn->format('H:i:s') : null,
            'clock_end' => ($status === '退勤済' && $clockEnd) ? $clockEnd->format('H:i:s') : null,
            'status' => $status,
            'remarks' => $status . 'ダミーデータ',
        ]);

        return $attendance;
    }

    private function createBreakTimeStatus($memberId, $id, $status, $elapsedMinutes)
    {
        $attendance = $this->createAttendance($memberId, $id, $status, $elapsedMinutes);

        if ($attendance->clock_in) {
            $breakStart = Carbon::parse($attendance->clock_in)->addMinutes($elapsedMinutes - 30);
            $breakEnd = (clone $breakStart)->addMinutes(30); // 30分の休憩

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_time_start' => $breakStart->format('H:i:s'),
                'break_time_end' => $breakEnd->format('H:i:s'),
            ]);

            $attendance->update([
                'status' => $status,
                'remarks' => '休憩中ダミーデータ',
            ]);
        }
    }

    private function createRandomAttendance($memberId, $id)
    {
        $statuses = ['勤務外', '勤務中', '休憩中', '退勤済']; // ランダムなステータス
        $status = $statuses[array_rand($statuses)];
        $workMinutes = $status === '勤務外' ? 0 : rand(1, 480); // 勤務外は0分、それ以外は1〜480分

        $attendance = $this->createAttendance($memberId, $id, $status, $workMinutes);

        if ($status === '休憩中' && $attendance->clock_in) {
            // 休憩中の場合は休憩時間も作成
            $breakStart = Carbon::parse($attendance->clock_in)->addMinutes(rand(60, max(61, $workMinutes - 30)));
            $breakEnd = (clone $breakStart)->addMinutes(rand(15, 60)); // 15〜60分の休憩

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_time_start' => $breakStart->format('H:i:s'),
                'break_time_end' => $breakEnd->format('H:i:s'),
            ]);

            $attendance->update([
                'status' => '休憩中',
                'remarks' => '休憩中ダミーデータ',
            ]);
        }
    }
}
