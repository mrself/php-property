<?php declare(strict_types=1);

namespace Mrself\Property;

class NonValuePathException extends PropertyException
{
    /**
     * @var mixed
     */
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
        $path = json_encode($path);
        parent::__construct("Path '$path' is not value path.");
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }
}