<?php declare(strict_types=1);

namespace Mrself\Property;

use Mrself\Container\Container;
use Mrself\Container\ServiceProvider;
use Mrself\Property\Driver\DriverContainer;

class PropertyProvider extends ServiceProvider
{

    protected function getContainer(): Container
    {
        $container = Container::make();
        $container->set(DriverContainer::class, new DriverContainer());
        return $container;
    }

    protected function getNamespace(): string
    {
        return 'Mrself\Property';
    }
}