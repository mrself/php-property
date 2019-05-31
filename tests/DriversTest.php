<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use Mrself\Property\Driver\IDriver;
use PHPUnit\Framework\TestCase;

class DriversTest extends TestCase
{
    use TestTrait;

    public function testDriverIsUsedIfItSatisfiesSource()
    {
        $driver = new class implements IDriver {
            public function satisfies($source): bool
            {
                return true;
            }

            public function get($source, $key)
            {
                return 'a';
            }
        };
        $this->drivers->add('my_driver', $driver);

        $result = $this->property->get([], 'a');
        $this->assertEquals('a', $result);
    }
}