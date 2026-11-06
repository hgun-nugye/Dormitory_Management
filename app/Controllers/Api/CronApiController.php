<?php

namespace App\Controllers\Api;

use App\Services\AttendanceDisciplineService;
use App\Services\AutoReleaseService;
use Throwable;

class CronApiController extends BaseApiController
{
    /**
     * Cron Job: Quét tự động thu hồi giường quá hạn 72h
     * Route: GET/POST /api/cron/release-beds hoặc GET /cron/trigger_release
     */
    public function triggerRelease(): void
    {
        try {
            $result = AutoReleaseService::scanAndReleaseExpiredBeds();
            $this->json([
                'status' => 'success',
                'action' => 'Cron Job 72h Auto Release Beds',
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            $this->error('Lỗi khi thực thi Cron Release Beds: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cron Job: Quét phát hiện sinh viên vắng mặt kéo dài quá 30 ngày
     * Route: GET/POST /api/cron/check-absence hoặc GET /cron/trigger_absence
     */
    public function triggerAbsence(): void
    {
        try {
            $result = AttendanceDisciplineService::detectLongTermAbsence(30);
            $this->json([
                'status' => 'success',
                'action' => 'Cron Job 30-Day Absence Detection',
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            $this->error('Lỗi khi thực thi Cron Absence Detection: ' . $e->getMessage(), 500);
        }
    }
}

