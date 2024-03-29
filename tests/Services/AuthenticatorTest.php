<?php
namespace BlueFission\Tests\Services;

use BlueFission\Services\Authenticator;
use PHPUnit\Framework\TestCase;

class AuthenticatorTest extends TestCase {
    private $authenticator;

    public function setUp(): void
    {
        $datasource = $this->createMock(Storage::class);
        $config = null;
        $this->authenticator = new Authenticator($datasource, $config);
    }

    public function testAuthenticateReturnsFalseForEmptyUsernameOrPassword()
    {
        $username = "";
        $password = "password";

        $this->assertFalse($this->authenticator->authenticate($username, $password));

        $username = "username";
        $password = "";

        $this->assertFalse($this->authenticator->authenticate($username, $password));
    }

    public function testIsAuthenticatedReturnsTrueForValidCookie()
    {
        $_COOKIE[$this->authenticator->config('session')] = json_encode([
            'username' => 'username',
            'id' => 1
        ]);

        $this->assertTrue($this->authenticator->isAuthenticated());
    }

    public function testIsAuthenticatedReturnsFalseForInvalidCookie()
    {
        $_COOKIE[$this->authenticator->config('session')] = json_encode([
            'username' => '',
            'id' => ''
        ]);

        $this->assertFalse($this->authenticator->isAuthenticated());
    }
}
