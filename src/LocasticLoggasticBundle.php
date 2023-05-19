<?php

namespace Locastic\Loggastic;

use Locastic\Loggastic\DependencyInjection\LocasticActivityLogExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LocasticActivityLogsBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new LocasticActivityLogExtension();
    }
}
