<?php declare(strict_types=1);

namespace Mrself\Property;

use Mrself\DataTransformers\DataTransformers;
use Mrself\Options\Annotation\Option;
use Mrself\Options\OptionableInterface;
use Mrself\Options\WithOptionsTrait;
use Mrself\Property\Driver\DriverContainer;
use Mrself\Util\StringUtil;

class Property implements OptionableInterface
{
    use WithOptionsTrait;

    /**
     * @var DataTransformers
     */
    protected $dataTransformers;

    /**
     * @Option()
     * @var DriverContainer
     */
    protected $driversContainer;

    /**
     * @param $source
     * @param $path
     * @param array $transformers
     * @return mixed
     * @throws EmptyPathException
     * @throws InvalidSourceException
     */
    public function get($source, $path, $transformers = [])
    {
        $transformers = (array) $transformers;
        if (is_string($path)) {
            $parts = explode('|', $path);
            $path = $parts[0];
            $transformers = array_merge(array_slice($parts, 1), $transformers);
        }
        $value = $this->getValue($source, $path);
        return $this->dataTransformers
            ->applyTransformers($value, $transformers);
    }

    public function getValue($source, $path)
    {
        if ($this->isValuePath($path)) {
            return $this->parseValuePath($path);
        }
        $path = $this->pathToArray($path);
        if (!$path[0]) {
            throw new EmptyPathException();
        }
        $originalPath = $path;
        while (null !== ($key = array_shift($path))) {
            $driver = $this->driversContainer->define($source);
            if ($driver) {
                $source = $driver->get($source, $key);
            } elseif (is_object($source)) {
                $source = $this->objectGet($source, $key);
            } elseif (is_array($source)) {
                $source = $this->arrayGet($source, $key);
            } else {
                throw new InvalidSourceException($source, $originalPath);
            }
        }
        return $source;
    }

    /**
     * Check if value can be accessed
     *
     * @param $source
     * @param $path
     * @return bool
     */
    public function canGet($source, $path)
    {
        try {
            $this->get($source, $path);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @deprecated Not used. Is here because it may be useful later
     * @param $source
     * @param $path
     * @return mixed
     * @throws EmptyPathException
     * @throws InvalidSourceException
     * @throws NonValuePathException
     * @throws NonexistentKeyException
     */
    protected function getPreLast($source, $path)
    {
        $path = $this->pathToArray($path);
        if (!$path[0]) {
            throw new EmptyPathException();
        }
        $path = array_slice($path, 0, -1);
        if (empty($path)) {
            return $source;
        }
        return $this->get($source, $path);
    }

    /**
     * @param $target
     * @param $path
     * @param $value
     * @throws EmptyPathException
     * @throws InvalidTargetException
     */
    public function set(&$target, $path, $value)
    {
        $path = $this->pathToArray($path);
        if (!$path[0]) {
            throw new EmptyPathException();
        }
        $originalPath = $path;
        $key = array_pop($path);
        if (count($originalPath) > 1) {
            foreach ($path as $name) {
                if (is_object($target)) {
                    $target = &$target->$name;
                } elseif (is_array($target)) {
                    $target = &$target[$name];
                }
            }
            $this->set($target, $key, $value);
        } else {
            try {
                $this->setByKey($target, $key, $value);
            } catch (InvalidTargetException $e) {
                throw new InvalidTargetException($target, $originalPath, $e);
            }
        }
    }

    /**
     * @param $target
     * @param $key
     * @param $value
     * @throws InvalidTargetException
     */
    public function setByKey(&$target, $key, $value)
    {
        if (is_object($target)) {
            $this->objectSet($target, $key, $value);
        } elseif (is_array($target)) {
            $target[$key] = $value;
        } else {
            throw new InvalidTargetException($target);
        }
    }

    protected function objectSet($object, $key, $value)
    {
        $method = 'set' . StringUtil::camelize($key);
        if (method_exists($object, $method)) {
            $object->$method($value);
        } else {
            $object->$key = $value;
        }
    }

    protected function pathToArray($path)
    {
        if (is_array($path)) {
            return $path;
        }
        return explode('.', $path);
    }

    /**
     * @param $object
     * @param $key
     * @return mixed
     * @throws NonexistentKeyException
     * @throws \Exception
     */
    public function objectGet($object, $key)
    {
        $method = 'get'. StringUtil::camelize($key);
        if (method_exists($object, $method)) {
            return $object->$method();
        }

        $method = 'is'. StringUtil::camelize($key);
        if (method_exists($object, $method)) {
            return $object->$method();
        }

        if (property_exists($object, $key) && !$this->isPropertyPublic($object, $key)) {
            throw new NonAccessiblePropertyException($object, $key);
        }

        try {
            return $object->$key;
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Undefined property') !== false) {
                throw new NonexistentKeyException($object, $key, 'object');
            }
            throw $e;
        }
    }

    protected function isPropertyPublic($object, string $key)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionProperty = $reflectionObject->getProperty($key);
        return $reflectionProperty->isPublic();
    }

    /**
     * @param $array
     * @param $key
     * @return mixed
     * @throws NonexistentKeyException
     */
    public function arrayGet($array, $key)
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        throw new NonexistentKeyException($array, $key, 'array');
    }

    /**
     * @param $path
     * @return mixed
     * @throws NonValuePathException
     */
    public function parseValuePath($path)
    {
        if (!$this->isValuePath($path)) {
            throw new NonValuePathException($path);
        }
        $path = explode(':', $path);
        if (array_key_exists(2, $path)) {
            $result = $path[1];
            settype($result, $path[2]);
            return $result;
        }
        return $path[1];
    }

    public function isValuePath($path): bool
    {
        if (!is_string($path)) {
            return false;
        }
        return substr($path, 0, strlen('value:')) === 'value:';
    }

    protected function onInit()
    {
        $this->dataTransformers = DataTransformers::make();
    }
}