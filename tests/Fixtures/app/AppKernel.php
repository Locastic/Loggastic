<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Locastic\Loggastic\LocasticLoggasticBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        // patch for behat/symfony2-extension not supporting %env(APP_ENV)%
        $this->environment = $_SERVER['APP_ENV'] ?? $environment;
    }

    public function registerBundles(): array
    {
        return [
            new DoctrineBundle(),
            new FrameworkBundle(),
            new SecurityBundle(),
            new LocasticLoggasticBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $configDir = $this->getConfigDir();

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import($configDir.'/*.yaml');

        // DoctrineBundle 3 (Symfony 8 stacks) removed this option in favor of
        // PHP 8.4 native lazy objects; DoctrineBundle 2 still needs it
        if (\Composer\InstalledVersions::satisfies(new \Composer\Semver\VersionParser(), 'doctrine/doctrine-bundle', '^2.0')) {
            $container->extension('doctrine', ['orm' => ['auto_generate_proxy_classes' => true]]);
        }
    }
}
