<?php

namespace Kasi\Tests\Notifications;

use Kasi\Notifications\Action;
use PHPUnit\Framework\TestCase;

class NotificationActionTest extends TestCase
{
    public function testActionIsCreatedProperly()
    {
        $action = new Action('Text', 'url');

        $this->assertSame('Text', $action->text);
        $this->assertSame('url', $action->url);
    }
}
