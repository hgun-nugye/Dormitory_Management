<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Tiếp nhận & Xử lý Yêu cầu / Đơn một cửa của Sinh viên
 * View: views/staff/requests.php
 */
class RequestController extends Controller
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

    public function requests(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.requests', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }
}

