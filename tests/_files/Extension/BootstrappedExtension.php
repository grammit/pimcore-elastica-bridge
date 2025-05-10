<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Extension;

use PHPUnit\Event\Test\PreparationStarted as TestStartedEvent;
use PHPUnit\Event\Test\PreparationStartedSubscriber as TestStartedSubscriber;
use PHPUnit\Event\TestRunner\Finished as TestRunnerFinishedEvent;
use PHPUnit\Event\TestRunner\FinishedSubscriber as TestRunnerFinishedSubscriber;
use PHPUnit\Event\TestRunner\Started as TestRunnerStartedEvent;
use PHPUnit\Event\TestRunner\StartedSubscriber as TestRunnerStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class BootstrappedExtension implements Extension
{
    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $extension = new PimcoreExtension();

        $facade->registerSubscriber(new class ($extension) implements TestRunnerStartedSubscriber {
            public function __construct(
                private readonly PimcoreExtension $extension,
            ) {}

            public function notify(TestRunnerStartedEvent $event): void
            {
                $this->extension->executeBeforeFirstTest();
            }
        });

        $facade->registerSubscriber(new class ($extension) implements TestRunnerFinishedSubscriber {
            public function __construct(
                private readonly PimcoreExtension $extension,
            ) {}

            public function notify(TestRunnerFinishedEvent $event): void
            {
                $this->extension->executeAfterLastTest();
            }
        });

        $facade->registerSubscriber(new class ($extension) implements TestStartedSubscriber {
            public function __construct(
                private readonly PimcoreExtension $extension,
            ) {}

            public function notify(TestStartedEvent $event): void
            {
                $this->extension->executeBeforeTest();
            }
        });
    }
}
