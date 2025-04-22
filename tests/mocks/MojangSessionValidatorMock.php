<?php

namespace App\Tests\Mock;

use App\Services\MojangSessionValidator;

class MojangSessionValidatorMock extends MojangSessionValidator
{
    // Simulate Mojang session validation
    public function validateSession(string $sessionId, string $uuid): bool
    {
        // Simulate a valid session if the UUID starts with 'valid-'
        if (strpos($uuid, 'valid-') === 0) {
            return true;
        }

        // Simulate an invalid session for any other UUID
        return false;
    }
}
?>
