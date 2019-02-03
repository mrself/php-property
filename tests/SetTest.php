<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use Mrself\Property\EmptyPathException;
use Mrself\Property\InvalidTargetException;
use PHPUnit\Framework\TestCase;

class SetTest extends TestCase
{
    use TestTrait;

    public function testSetSetsValueToObjectProperty()
    {
        $object = (object) ['a' => 1];
        $this->property->set($object, 'a', 2);
        $this->assertEquals(2, $object->a);
    }

    public function testSetUsesObjectSetsMethod()
    {
        $object = new class {
            public $a = 1;
            public function setA($value)
            {
                $this->a = $value;
            }
        };
        $this->property->set($object, 'a', 2);
        $this->assertEquals(2, $object->a);
    }

    public function testSetSetsValueToObjectPropertyByTwoPartPath()
    {
        $object = (object) ['a' => (object) ['b' => 1]];
        $this->property->set($object, 'a.b', 2);
        $this->assertEquals(2, $object->a->b);
    }

    public function testSetSetsValueToArray()
    {
        $array = ['a' => 1];
        $this->property->set($array, 'a', 2);
        $this->assertEquals(2, $array['a']);
    }

    public function testSetSetsValueToTwoLevelArray()
    {
        $array = ['a' => ['b' => 1]];
        $this->property->set($array, 'a.b', 2);
        $this->assertEquals(2, $array['a']['b']);
    }

    public function testSetSetsValueToArrayInObject()
    {
        $object = (object) ['a' => ['b' => 1]];
        $this->property->set($object, 'a.b', 2);
        $this->assertEquals(2, $object->a['b']);
    }

    public function testSetSetsValueToObjectInArray()
    {
        $array = ['a' => (object) ['b' => 1]];
        $this->property->set($array, 'a.b', 2);
        $this->assertEquals(2, $array['a']->b);
    }

    public function testSetThrowsEmptyPathException()
    {
        $array = ['a' => 1];
        try {
            $this->property->set($array, '', 2);
        } catch (EmptyPathException $e) {
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }

    public function testSetHandlesInvalidTargetExceptionFromSetByKey()
    {
        $target = '';
        try {
            $this->property->set($target, 'a', 1);
        } catch (InvalidTargetException $e) {
            $this->assertInstanceOf(InvalidTargetException::class, $e->getPrevious());
            $this->assertEquals(['a'], $e->getPath());
            $this->assertEquals('', $e->getTarget());
            $this->assertTrue(true);
            return;
        }
        $this->assertTrue(false);
    }
}