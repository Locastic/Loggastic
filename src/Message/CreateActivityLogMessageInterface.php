<?php

namespace Locastic\ActivityLogs\Message;

interface CreateActivityLogMessageInterface extends ActivityLogMessageInterface
{
    public function getItem(): object;
}
