<?php

namespace Zernite\util;

class Identifier {
    private static ImplDatabase $db;
    private static ImplSession $session;

    public static function initDatabase(): ImplDatabase {
        return self::$db ??= new ImplDatabase();
    }

    public static function initSession(): ImplSession {
        return self::$session ??= new ImplSession();
    }

    public static function initAll(): void{
        self::initDatabase();
        self::initSession();
    }
}