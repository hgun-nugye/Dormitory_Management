<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Quản lý Điện nước & Dịch vụ tiện ích
 * View: views/staff/utilities.php
 */
class UtilityController extends Controller
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

    public function utilities(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.utilities', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }
}

