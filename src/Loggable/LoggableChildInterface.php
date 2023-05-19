<?php

namespace Locastic\ActivityLogs\Loggable;

interface LoggableChildInterface
{
    public function logTo(): ?object;
}
