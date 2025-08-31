<?php

namespace App\Infrastructure\Logger;

use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\RequestStack;

class ExtraRequestDataProcessor
{
    public function __construct(private readonly ?RequestStack $requestStack)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if (!$this->requestStack) {
            return $record;
        }
        $request = $this->requestStack->getCurrentRequest();

        $record->extra['endpoint'] = $request->getRequestUri();
        $record->extra['data'] = [
            'query' => $request->query->all(),
            'payload' => $request->getPayload()->all(),
        ];
        $record->extra['headers'] = $request->headers->all();
        $record->extra['method'] = $request->getMethod();
        $record->extra['cookies'] = $request->cookies->all();

        if ($request->hasSession()) {
            $record->extra['session_data'] = $request->getSession()->all();
        }

        return $record;
    }
}
