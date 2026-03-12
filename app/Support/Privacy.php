<?php

namespace App\Support;

class Privacy
{
    public static function anonymize(string $namespace, string|int $id): string
    {
        $salt = (string) config('privacy.anonymization_salt');

        return hash_hmac('sha256', $namespace.'|'.$id, $salt);
    }
}

