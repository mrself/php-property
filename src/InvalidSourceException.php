<?php declare(strict_types=1);

namespace Mrself\Property;

class InvalidSourceException extends PropertyException
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var string|array
     */
    protected $path;

    public function __construct($source, $path)
    {
        $this->source = $source;
        $this->path = $path;

        $path = json_encode($path);
        parent::__construct("Invalid type of source for path '$path'");
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array|string
     */
    public function getPath()
    {
        return $this->path;
    }

}