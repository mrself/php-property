<?php declare(strict_types=1);

namespace Mrself\Property;

class NonAccessiblePropertyException extends PropertyException
{
    /**
     * @var string
     */
    protected $property;

    /**
     * @var mixed
     */
    protected $object;

    public function __construct($object, string $property)
    {
        $this->object = $object;
        $this->property = $property;

        parent::__construct("The property '$property' is not accessible in the given object'");
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }
}