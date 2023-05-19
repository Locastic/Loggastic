<?php

namespace Locastic\ActivityLogs\Identifier;

interface LogIdentifierExtractorInterface
{
    public function getIdentifierValue(object $object): int|string;
}
