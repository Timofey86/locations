<?php

namespace App\Tests\Shared\Trait;

trait AssertionsTrait
{
    public function assertMessageDispatched(string $fqcn, int $count)
    {
        $this->assertEquals($count, $this->dispatchedMessagesLogger->get($fqcn));
    }

    public function assertMessageNotDispatched(string $fqcn)
    {
        $this->assertFalse($this->dispatchedMessagesLogger->has($fqcn));
    }
}
