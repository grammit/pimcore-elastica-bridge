<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

trait SymfonyKernelTrait
{
    private static KernelInterface $kernel;

    private static bool $booted = false;

    protected static function getContainer(): ContainerInterface
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        try {
            /** @phpstan-ignore return.type */
            return self::$kernel->getContainer()->get('test.service_container');
        } catch (ServiceNotFoundException $e) {
            throw new \LogicException('Could not find service "test.service_container". Try updating the "framework.test" config to "true".', 0, $e);
        }
    }

    protected static function bootKernel(array $options = []): KernelInterface
    {
        self::ensureKernelShutdown();

        $kernel = self::createKernel($options);
        $kernel->boot();
        self::$kernel = $kernel;
        self::$booted = true;

        return self::$kernel;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $class = self::getKernelClass();

        $env = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        /** @phpstan-ignore return.type */
        return new $class($env, (bool) $debug);
    }

    protected static function ensureKernelShutdown(): void
    {
        if (isset(self::$kernel)) {
            self::$kernel->boot();
            $container = self::$kernel->getContainer();

            if ($container->has('services_resetter')) {
                // Instantiate the service because Container::reset() only resets services that have been used
                $container->get('services_resetter');
            }

            self::$kernel->shutdown();
            self::$booted = false;

            if ($container instanceof ResetInterface) {
                $container->reset();
            }
        }
    }

    protected static function getKernelClass(): string
    {
        if (!isset($_SERVER['KERNEL_CLASS']) && !isset($_ENV['KERNEL_CLASS'])) {
            throw new \LogicException(sprintf('You must set the KERNEL_CLASS environment variable to the fully-qualified class name of your Kernel in phpunit.xml / phpunit.xml.dist or override the "%1$s::createKernel()" or "%1$s::getKernelClass()" method.', static::class));
        }

        if (!class_exists($class = $_ENV['KERNEL_CLASS'] ?? $_SERVER['KERNEL_CLASS'])) {
            throw new \RuntimeException(sprintf('Class "%s" doesn\'t exist or cannot be autoloaded. Check that the KERNEL_CLASS value in phpunit.xml matches the fully-qualified class name of your Kernel or override the "%s::createKernel()" method.', $class, static::class));
        }

        return $class;
    }
}
