<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use PHPUnit\Framework\TestCase;

class CanGetTest extends TestCase
{
    use TestTrait;

    public function testCanGetReturnsFalseIfArrayKeyDoesNotExist()
    {
        $this->assertFalse($this->property->canGet([], 'a'));
    }

    public function testCanGetReturnsFalseIfObjectPropertyIfProtected()
    {
        $object = new class {
            protected $field;
        };
        $this->assertFalse($this->property->canGet($object, 'field'));
    }
}