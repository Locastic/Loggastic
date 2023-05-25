<?php

namespace Locastic\Loggastic\Identifier;

interface LogIdentifierExtractorInterface
{
    public function getIdentifierValue(object $object): int|string;
}
