<?php declare(strict_types=1);

namespace Mrself\Property;

class InvalidTargetException extends PropertyException
{
    /**
     * @var mixed
     */
    protected $target;

    /**
     * @var string|array
     */
    protected $path;

    public function __construct($target, $path = null, \Exception $prev = null)
    {
        $this->target = $target;
        $this->path = $path;

        $path = json_encode($path);
        $message = "Invalid type of target";
        if ($path) {
            $message .= " for path '$path'";
        }
        parent::__construct($message, 0, $prev);
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return array|string
     */
    public function getPath()
    {
        return $this->path;
    }
}