<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Điều phối & Quản lý Bảo trì Thiết bị, Cơ sở vật chất
 * View: views/staff/maintenance.php
 */
class MaintenanceController extends Controller
{
    private function getMaCum(): string
    {
        $user = Auth::user();
        if (!empty($user['MaCumQuanLy'])) {
            return $user['MaCumQuanLy'];
        }
        $firstCum = Database::fetch("SELECT MaCum FROM CumNha LIMIT 1");
        return $firstCum['MaCum'] ?? 'AG';
    }

    public function maintenance(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.maintenance', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }
}

