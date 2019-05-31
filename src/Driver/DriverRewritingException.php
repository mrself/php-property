<?php declare(strict_types=1);

namespace Mrself\Property\Driver;

use Mrself\Property\PropertyException;

class DriverRewritingException extends PropertyException
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var IDriver
     */
    private $driver;

    public function __construct(string $name, IDriver $driver)
    {
        $this->name = $name;
        $this->driver = $driver;

        parent::__construct("Driver container already has a driver with the name $name");
    }
}