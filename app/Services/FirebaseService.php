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
        $factory = (new Factory)
            // Path to service account file
            ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
            // Change This to firebase realtime database path
            ->withDatabaseUri('https://product-inventory-29475-default-rtdb.firebaseio.com');

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
