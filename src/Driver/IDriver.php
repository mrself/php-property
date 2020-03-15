<?php declare(strict_types=1);

namespace Mrself\Property\Driver;

interface IDriver
{
    public function satisfies($source): bool;

    public function get($source, $name);
}