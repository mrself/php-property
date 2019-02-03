<?php declare(strict_types=1);

namespace Mrself\Property;

use ICanBoogie\Inflector;

class Property
{
    /**
     * @var Inflector
     */
    protected $inflector;

    public function __construct()
    {
        $this->inflector = Inflector::get();
    }

    /**
     * @param $source
     * @param $path
     * @return mixed
     * @throws InvalidSourceException
     * @throws NonValuePathException
     * @throws NonexistentKeyException
     */
    public function get($source, $path)
    {
        if ($this->isValuePath($path)) {
            return $this->parseValuePath($path);
        }
        $path = $this->pathToArray($path);
        $originalPath = $path;
        while (null !== ($key = array_shift($path))) {
            if (is_object($source)) {
                $source = $this->objectGet($source, $key);
            } elseif (is_array($source)) {
                $source = $this->arrayGet($source, $key);
            } else {
                throw new InvalidSourceException($source, $originalPath);
            }
        }
        return $source;
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
        if (property_exists($object, $key)) {
            return $object->$key;
        }
        $method = 'get'. $this->inflector->camelize($key);
        if (method_exists($object, $method)) {
            return $object->$method();
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
}