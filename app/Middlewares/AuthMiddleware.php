<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;

class AuthMiddleware {
    public function handle(Request $request): void {
        if (!Auth::check()) {
            redirect('login');
        }
    }
}
