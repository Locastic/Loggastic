<?php

namespace Locastic\Loggastic;

use Locastic\Loggastic\DependencyInjection\LocasticLoggasticExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class LocasticLoggasticBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new LocasticLoggasticExtension();
    }
}
