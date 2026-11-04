<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: An ninh, Kỷ luật & Đánh giá Rèn luyện Sinh viên
 * View: views/staff/discipline.php
 */
class DisciplineController extends Controller
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

    public function discipline(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.discipline', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }
}

