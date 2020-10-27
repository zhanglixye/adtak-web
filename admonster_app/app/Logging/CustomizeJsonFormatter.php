<?php


namespace App\Logging;

use Monolog\Formatter\JsonFormatter;

class CustomizeJsonFormatter extends JsonFormatter
{
    // override
    public function format(array $record): string
    {
        $newRecord = [
            'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            'message' => $record['message'],
        ];

        if (!empty($record['context'])) {
            $newRecord = array_merge($newRecord, $record['context']);
        }
        $json = $this->toJson($this->normalize($newRecord), true) . ($this->appendNewline ? "\n" : '');

        return $json;
    }
}
