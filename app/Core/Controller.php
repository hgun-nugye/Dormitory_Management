<?php

namespace App\Core;

abstract class Controller {
    protected function view(string $viewName, array $data = []): void {
        view($viewName, $data);
    }

    protected function json($data, int $status = 200): void {
        json_response($data, $status);
    }

    protected function redirect(string $url): void {
        redirect($url);
    }
}
