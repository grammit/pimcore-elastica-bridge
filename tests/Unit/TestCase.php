<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Unit;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Valantic\ElasticaBridgeBundle\Tests\Traits\PimcoreDatabaseReset;
use Zenstruck\Foundry\Test\Factories;

abstract class TestCase extends KernelTestCase
{
    use PimcoreDatabaseReset;
    use Factories;
}
