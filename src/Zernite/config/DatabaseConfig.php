<?php

namespace Zernite\config;

class DatabaseConfig {
    private static $host = 'localhost';
    private static $db = 'zaphire';
    private static $user = 'valady';
    private static $pwd = 'worldismine';
    public static function getHost(): string {
        return self::$host;
    }
    public static function getDatabase(): string {
        return self::$db;
    }
    public static function getUser(): string {
        return self::$user;
    }
    public static function getPassword(): string {
        return self::$pwd;
    }
}