<?php

namespace Zernite\interfaces;

interface ISession {
    public function setSession(string $key, $value);
    public function getSession(string $key);
    public function removeSession(string $key);
    public function destroySession();
}