<?php declare(strict_types=1);

namespace Mrself\Property;

class NonexistentKeyException extends PropertyException
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var int|string
     */
    protected $key;

    public function __construct($source, $key, string $type)
    {
        $this->source = $source;
        $this->key = $key;
        $type = ucfirst($type);

        parent::__construct("$type does not contain key '$key'");
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return int|string
     */
    public function getKey()
    {
        return $this->key;
    }
}