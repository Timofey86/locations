<?php

namespace App\Tests\Shared;

use Symfony\Bundle\FrameworkBundle\Test\WebTestAssertionsTrait;

class ApiTest extends UnitTest
{
    use WebTestAssertionsTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::getContainer()->get('test.client');
        $this->client->disableReboot();
    }

    protected function getHeaders(): array
    {
        return [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
    }
}
