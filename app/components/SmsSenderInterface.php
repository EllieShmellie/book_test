<?php

namespace app\components;

interface SmsSenderInterface
{
    /**
     * @param array<int, array{to: string, text: string}> $messages
     */
    public function sendBatch(array $messages, array $additionalParams = []): ?array;
}
