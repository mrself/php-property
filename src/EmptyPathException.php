<?php declare(strict_types=1);

namespace Mrself\Property;

class EmptyPathException extends PropertyException
{
    public function __construct()
    {
        parent::__construct("Empty path was given");
    }
}