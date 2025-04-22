<?php

namespace App\Tests\Mock;

use App\Tests\Mock\MojangSessionValidatorMock;
use PHPUnit\Framework\TestCase;

class MojangSessionValidatorMockTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        // Create an instance of the mocked validator
        $this->validator = new MojangSessionValidatorMock();
    }

    public function testValidSession(): void
    {
        // Test a valid UUID
        $sessionId = 'valid-session-id';
        $uuid = 'valid-uuid-12345';

        $isValid = $this->validator->validateSession($sessionId, $uuid);

        // Assert that the session is valid
        $this->assertTrue($isValid);
    }

    public function testInvalidSession(): void
    {
        // Test an invalid UUID
        $sessionId = 'invalid-session-id';
        $uuid = 'invalid-uuid-12345';

        $isValid = $this->validator->validateSession($sessionId, $uuid);

        // Assert that the session is invalid
        $this->assertFalse($isValid);
    }
}
