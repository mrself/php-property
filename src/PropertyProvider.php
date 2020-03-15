<?php declare(strict_types=1);

namespace Mrself\Property;

use Mrself\Container\Container;
use Mrself\Container\ContainerInterface;
use Mrself\Container\Registry\ContainerRegistry;
use Mrself\Property\Driver\DriverContainer;

class PropertyProvider
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function register()
    {
        $this->container = new Container();
        ContainerRegistry::add('Mrself\Property', $this->container);
        $this->container->set(DriverContainer::class, new DriverContainer());
    }

    public function boot()
    {

    }

    public static function make()
    {
        return new static();
    }
}