<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Property\Property;
use Mrself\Property\PropertyProvider;
use PHPUnit\Framework\TestCase;

class ProviderTest extends TestCase
{
    public function testPropertyServiceExistsInContainer()
    {
        ContainerRegistry::reset();
        $propertyProvider = PropertyProvider::make();
        $propertyProvider->register();

        $property = $propertyProvider->container()->get(Property::class);
        $this->assertInstanceOf(Property::class, $property);
    }
}