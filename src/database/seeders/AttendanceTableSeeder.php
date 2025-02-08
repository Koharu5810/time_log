<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Member;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statusMap = [
            1 => '勤務外',
            2 => '出勤中',              // 通常の出勤
            3 => '休憩中',              // 1回目の休憩中
            4 => '出勤中',              // 1回目の休憩後の出勤中
            5 => '休憩中',              // 2回目の休憩中
            6 => '出勤中',              // 2回目の休憩後の出勤中
            7 => '退勤済',              // 退勤済み
        ];

        foreach ($statusMap as $memberId => $status) {
            $workDate = Carbon::now()->subDays(rand(0, 3))->format('Y-m-d');  // 過去3日間のランダムな日付

            // 勤務外データ
            if ($status === '勤務外') {
                Attendance::create([
                    'member_id' => $memberId,
                    'work_date' => $workDate,
                    'clock_in' => null,
                    'clock_end' => null,
                    'status' => $status,
                    'remarks' => '勤務外ダミーデータ',
                ]);
                continue;
            }

            // 出勤データの作成
            $clockIn = Carbon::createFromTime(rand(7, 12), rand(0, 59)); // 出勤時間
            $attendance = Attendance::create([
                'member_id' => $memberId,
                'work_date' => $workDate,
                'clock_in' => $clockIn->format('H:i:s'),
                'clock_end' => null,
                'status' => '出勤中',
                'remarks' => '出勤中ダミーデータ',
            ]);

            // ステータスごとの処理
            switch ($memberId) {
                case 2:
                    // 通常の出勤中
                    break;

                case 3:
                    // 1回目の休憩中
                    $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    break;

                case 4:
                    // 1回目の休憩後の出勤中
                    $break = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break);
                    $attendance->update([
                        'status' => '出勤中',
                        'remarks' => '1回目の休憩後出勤ダミーデータ'
                    ]);
                    break;

                case 5:
                    // 2回目の休憩中
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);
                    $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    break;

                case 6:
                    // 2回目の休憩後の出勤中
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);
                    $break2 = $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    $this->endBreakTime($attendance, $break2);
                    break;

                case 7:
                    // 退勤済
                    $workDuration = rand(120, 300); // 2〜5時間
                    $this->safeClockOut($attendance, $clockIn, $workDuration);
                    break;

                case 8:
                    // 1回の休憩を取り退勤
                    $break = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break);

                    $attendance->update([
                        'status' => '出勤中',
                        'remarks' => '1回目の休憩後出勤中ダミーデータ'
                    ]);

                    $workDuration = rand(300, 540); // 5〜9時間
                    $this->safeClockOut($attendance, $clockIn, $workDuration);
                    break;

                case 9:
                    // 2回の休憩を取り退勤
                    $break1 = $this->startBreakTime($attendance, $clockIn, '1回目の休憩中');
                    $this->endBreakTime($attendance, $break1);

                    $attendance->update([
                        'status' => '出勤中',
                        'remarks' => '1回目の休憩後出勤ダミーデータ'
                    ]);

                    $break2 = $this->startBreakTime($attendance, $break1->break_time_end, '2回目の休憩中');
                    $this->endBreakTime($attendance, $break2);

                    $attendance->update([
                        'status' => '出勤中',
                        'remarks' => '2回目の休憩後出勤ダミーデータ'
                    ]);

                    $workDuration = rand(480, 600); // 8〜10時間
                    $this->safeClockOut($attendance, $clockIn, $workDuration);
                    break;
            }
        }
    }

    // 休憩開始
    private function startBreakTime($attendance, $start, $breakLabel)
    {
        $breakStart = is_string($start)
            ? Carbon::parse($start)->addMinutes(rand(120, 180))
            : (clone $start)->addMinutes(rand(120, 180));

        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_start' => $breakStart->format('H:i:s'),
            'break_time_end' => null,
        ]);

        // 休憩中に更新
        $attendance->update([
            'status' => '休憩中',
            'remarks' => $breakLabel . 'ダミーデータ'
        ]);

        return $break; // 休憩終了時間を返す
    }

    // 休憩終了
    private function endBreakTime($attendance, $break)
    {
        $breakEnd = Carbon::parse($break->break_time_start)->addMinutes(rand(15, 60)); // 15〜60分後に終了

        $break->update([
            'break_time_end' => $breakEnd->format('H:i:s')
        ]);

        $attendance->update([
            'status' => '出勤中',
            'remarks' => '休憩後出勤ダミーデータ'
        ]);
    }

    // 出勤中のときのみ退勤可
    private function safeClockOut($attendance, $clockIn, $workDuration)
    {
        if ($attendance->status !== '出勤中') {
            // 出勤中以外の場合は退勤できない
            throw new Exception("メンバーID {$attendance->member_id} は「出勤中」ではないため退勤できません。");
        }

        $clockOut = (clone $clockIn)->addMinutes($workDuration);
        $attendance->update([
            'clock_end' => $clockOut->format('H:i:s'),
            'status' => '退勤済',
            'remarks' => '退勤ダミーデータ'
        ]);
    }
}
