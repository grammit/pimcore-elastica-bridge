<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Traits;

use PHPUnit\Framework\Attributes\Before;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Valantic\ElasticaBridgeBundle\Tests\Persistence\PimcoreResetManager;

trait PimcoreDatabaseReset
{
    #[Before]
    public static function _resetDatabaseBeforeEachTest(): void
    {
        if (!\is_subclass_of(static::class, KernelTestCase::class)) {
            throw new \RuntimeException(\sprintf('The "%s" trait can only be used on TestCases that extend "%s".', __TRAIT__, KernelTestCase::class));
        }

        PimcoreResetManager::cleanUpDatabase(static fn () => static::bootKernel());
    }
}
