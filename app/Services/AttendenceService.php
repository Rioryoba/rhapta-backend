<?php

namespace App\Services;

use App\Models\Attendence;
use Carbon\Carbon;

class AttendenceService
{
    /**
     * Handle employee check-in for today.
     * Throws exception if already checked in.
     */
    public function checkIn($employeeId, $time = null)
    {
        $date = Carbon::today()->toDateString();
        $attendence = Attendence::where('employee_id', $employeeId)->where('date', $date)->first();
        if ($attendence && $attendence->check_in) {
            throw new \Exception('Already checked in for today.');
        }
        if (!$attendence) {
            $attendence = Attendence::create([
                'employee_id' => $employeeId,
                'date' => $date,
                'check_in' => $time ?? Carbon::now()->toTimeString(),
                'status' => 'present',
            ]);
        } else {
            $attendence->check_in = $time ?? Carbon::now()->toTimeString();
            $attendence->save();
        }
        return $attendence;
    }

    /**
     * Handle employee check-out for today.
     * Throws exception if not checked in or already checked out.
     */
    public function checkOut($employeeId, $time = null)
    {
        $date = Carbon::today()->toDateString();
        $attendence = Attendence::where('employee_id', $employeeId)->where('date', $date)->first();
        if (!$attendence || !$attendence->check_in) {
            throw new \Exception('Check-in required before check-out.');
        }
        if ($attendence->check_out) {
            throw new \Exception('Already checked out for today.');
        }
        $attendence->check_out = $time ?? Carbon::now()->toTimeString();
        $attendence->save();
        return $attendence;
    }
}
