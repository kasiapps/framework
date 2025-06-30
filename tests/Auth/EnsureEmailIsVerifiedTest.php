<?php

namespace Kasi\Tests\Auth;

use Kasi\Auth\Middleware\EnsureEmailIsVerified;
use PHPUnit\Framework\TestCase;

class EnsureEmailIsVerifiedTest extends TestCase
{
    public function testItCanGenerateDefinitionViaStaticMethod()
    {
        $signature = (string) EnsureEmailIsVerified::redirectTo('route.name');
        $this->assertSame('Kasi\Auth\Middleware\EnsureEmailIsVerified:route.name', $signature);
    }
}
