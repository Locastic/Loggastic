<?php

namespace Locastic\Loggastic\Loggable;

interface LoggableChildInterface
{
    public function logTo(): ?object;
}
