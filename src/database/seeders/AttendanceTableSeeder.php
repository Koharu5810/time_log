<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            for ($i = 0; $i < 2; $i++) { // 各メンバーに対して2日分のデータを作成
                $workDate = Carbon::now()->subDays($i);
                $clockIn = Carbon::createFromTime(rand(8, 10), rand(0, 59));
                $clockEnd = (clone $clockIn)->addHours(rand(7, 9))->addMinutes(rand(0, 59));

                // 初期値：勤務外
                $attendance = Attendance::create([
                    'member_id' => $member->id,
                    'work_date' => $workDate->format('Y-m-d'),
                    'clock_in' => $clockIn->format('H:i:s'),
                    'clock_end' => $clockEnd->format('H:i:s'),
                    'status' => '勤務外',
                    'remarks' => 'ダミーデータ',
                ]);

                if ($i === 0) {
                    // 1日目: 勤務前 → 出勤中 → 休憩中 → 出勤中 のシナリオ
                    $this->simulateWorkDay($attendance);
                } else {
                    // 2日目: 通常の勤務と休憩の生成
                    $breakCount = rand(1, 3);
                    $this->createBreakTimes($attendance, $breakCount);
                }
            }
        }
    }


    private function simulateWorkDay($attendance)
    {
        $workStart = Carbon::parse($attendance->clock_in);
        $workEnd = Carbon::parse($attendance->clock_end);

        // 勤務開始（ステータス変更: 出勤中）
        $attendance->update([
            'status' => '出勤中',
            'remarks' => '勤務開始',
        ]);

        // 休憩開始
        $breakStart = (clone $workStart)->addHours(3);
        $breakEnd = (clone $breakStart)->addMinutes(45); // 45分の休憩

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_start' => $breakStart->format('H:i:s'),
            'break_time_end' => $breakEnd->format('H:i:s'),
        ]);

        // 休憩中（ステータス変更）
        $attendance->update([
            'status' => '休憩中',
            'remarks' => '休憩中',
        ]);

        // 休憩終了後、再び勤務中へ
        $attendance->update([
            'status' => '出勤中',
            'remarks' => '休憩終了後の勤務中',
        ]);

        // 勤務終了（退勤済み）
        $attendance->update([
            'status' => '退勤済み',
            'remarks' => '勤務終了',
        ]);
    }

    private function createBreakTimes($attendance, $breakCount)
    {
        $workStart = Carbon::parse($attendance->clock_in);
        $workEnd = Carbon::parse($attendance->clock_end);

        for ($i = 0; $i < $breakCount; $i++) {
            $breakStart = (clone $workStart)->addHours(rand(2, 4))->addMinutes(rand(0, 30));
            $breakEnd = (clone $breakStart)->addMinutes(rand(15, 60)); // 15〜60分の休憩

            // 勤務時間内のみ休憩を作成
            if ($breakEnd->lessThan($workEnd)) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_time_start' => $breakStart->format('H:i:s'),
                    'break_time_end' => $breakEnd->format('H:i:s'),
                ]);

                // 休憩開始時にステータスを「休憩中」に変更
                $attendance->update(['status' => '休憩中']);
                // 休憩終了後に再度「出勤中」に戻す
                $attendance->update(['status' => '出勤中']);
            }
        }

        // 勤務終了時に「退勤済み」に更新
        $attendance->update([
            'status' => '退勤済み',
        ]);
    }
}
