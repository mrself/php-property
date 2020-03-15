<?php declare(strict_types=1);

namespace Mrself\Property\Driver;

class DriverContainer
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * @param string $name
     * @param IDriver $driver
     * @param bool $rewrite
     * @throws DriverRewritingException
     */
    public function add(string $name, IDriver $driver, $rewrite = false)
    {
        if ($this->has($name) && !$rewrite) {
            throw new DriverRewritingException($name, $driver);
        }
        $this->registry[$name] = $driver;
    }

    public function has(string $name)
    {
        return array_key_exists($name, $this->registry);
    }

    public function define($source)
    {
        foreach ($this->registry as $name => $driver) {
            if ($driver->satisfies($source)) {
                return $driver;
            }
        }
        return null;
    }
}