<?php

namespace Zernite\util;

use Zernite\interfaces\ISession;

class ImplSession implements ISession {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
    }
    public function setSession(string $key, $value): void {
        $_SESSION[$key] = $value;
    }
    public function getSession(string $key) {
        return $_SESSION[$key] ?? null;
    }
    public function removeSession(string $key): void {
        if (isset($_SESSION[$key]))
            unset($_SESSION[$key]);
    }
    public function destroySession(): void {
        session_unset();
        session_destroy();
    }
}