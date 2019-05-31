<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use ICanBoogie\Inflector;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Property\Driver\DriverContainer;
use Mrself\Property\Property;
use Mrself\Property\PropertyProvider;

trait TestTrait
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * @var DriverContainer
     */
    protected $drivers;

    protected function setUp()
    {
        parent::setUp();
        ContainerRegistry::reset();
        PropertyProvider::make()->register();
        $this->property = Property::make();
        $this->drivers = ContainerRegistry::get('Mrself\Property')
            ->get(DriverContainer::class);
    }

    protected function _assertHasException(array $props)
    {
        $inflector = Inflector::get();
        $class = $props['_class'];
        unset($props['_class']);
        $cbParams = $props['_callable'];
        unset($props['_callable']);
        try {
            $args = array_slice($cbParams, 2);
            call_user_func_array([$cbParams[0], $cbParams[1]], $args);
        } catch (\Exception $e) {
            $this->assertInstanceOf($class, $e);
            foreach ($props as $key => $value) {
                $method = 'get' . $inflector->camelize($key);
                $this->assertEquals($value, $e->$method());
            }
            return;
        }
        $this->assertTrue(false);
    }
}