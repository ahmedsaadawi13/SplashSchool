<?php
// FILE: /app/core/View.php

class View {
    public static function render($view, $data = []) {
        extract($data);

        $viewFile = APP . '/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            ob_start();
            require_once $viewFile;
            $content = ob_get_clean();
            return $content;
        } else {
            die("View not found: $view");
        }
    }

    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function asset($path) {
        return BASE_URL . '/assets/' . ltrim($path, '/');
    }

    public static function url($path = '') {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}
