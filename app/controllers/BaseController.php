<?php
class BaseController {
    protected function render($view, $data = []) {
        extract($data);
        include BASE_PATH . '/views/' . $view . '.php';
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
} 