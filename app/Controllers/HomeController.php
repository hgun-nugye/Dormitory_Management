<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class HomeController extends Controller {
    public function index(): void {
        // Lấy thống kê cơ bản hiển thị trang giới thiệu KTX
        $stats = [
            'total_students' => Database::fetch("SELECT COUNT(*) AS total FROM SinhVien")['total'] ?? 0,
            'total_rooms' => Database::fetch("SELECT COUNT(*) AS total FROM Phong")['total'] ?? 0,
            'total_beds' => Database::fetch("SELECT COUNT(*) AS total FROM Giuong")['total'] ?? 0,
            'available_beds' => Database::fetch("SELECT COUNT(*) AS total FROM Giuong WHERE TrangThaiGiuong = 'Trống'")['total'] ?? 0,
        ];

        $clusters = Database::fetchAll("SELECT * FROM CumNha");

        $this->view('home', [
            'stats' => $stats,
            'clusters' => $clusters
        ]);
    }
}
