<?php

namespace Locastic\Loggastic\Message;

interface CreateActivityLogMessageInterface extends ActivityLogMessageInterface
{
    public function getItem(): object;
}
