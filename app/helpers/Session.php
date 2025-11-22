<?php
// FILE: /app/helpers/Session.php

class Session {
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        session_destroy();
        $_SESSION = [];
    }

    public static function flash($key, $value = null) {
        if ($value === null) {
            // Get and remove flash message
            $value = self::get($key);
            self::remove($key);
            return $value;
        } else {
            // Set flash message
            self::set($key, $value);
        }
    }

    public static function setFlash($key, $value) {
        self::set($key, $value);
    }

    public static function getFlash($key) {
        $value = self::get($key);
        self::remove($key);
        return $value;
    }
}
