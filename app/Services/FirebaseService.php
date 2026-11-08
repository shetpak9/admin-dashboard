<?php

namespace App\Services;

use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

class FirebaseService
{
    protected Database $database;

    protected Auth $auth;

    protected Messaging $messaging;

    public function __construct()
    {
        $credentials = (string) config('services.firebase.credentials');

        if ($credentials !== '' && ! preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $credentials)) {
            $credentials = base_path($credentials);
        }

        $factory = (new Factory)
            ->withServiceAccount($credentials)
            ->withDatabaseUri((string) config('services.firebase.database_url'));

        $this->auth = $factory->createAuth();
        $this->database = $factory->createDatabase();
        $this->messaging = $factory->createMessaging();
    }

    public function getAuth(): Auth
    {
        return $this->auth;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getMessaging(): Messaging
    {
        return $this->messaging;
    }
}
