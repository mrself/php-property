<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use Mrself\Property\EmptyPathException;
use Mrself\Property\InvalidSourceException;
use Mrself\Property\NonAccessiblePropertyException;
use Mrself\Property\NonexistentKeyException;
use Mrself\Property\NonValuePathException;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase
{
    use TestTrait;

    public function testGetReturnsValueFromArrayInObject()
    {
        $object = (object) ['a' => ['b' => 1]];
        $actual = $this->property->get($object, 'a.b');
        $this->assertEquals(1, $actual);
    }

    public function testGetReturnsValueFromObjectInArray()
    {
        $object = ['a' => (object) ['b' => 1]];
        $actual = $this->property->get($object, 'a.b');
        $this->assertEquals(1, $actual);
    }

    /*
     * Start object properties
     */

    public function testGetReturnsValueFromObjectBySinglePath()
    {
        $object = (object) ['a' => 1];
        $actual = $this->property->get($object, 'a');
        $this->assertEquals(1, $actual);
    }

    public function testGetThrowsExceptionIfPathIsEmpty()
    {
        $object = (object) ['a' => 1];
        $this->_assertHasException([
            '_class' => EmptyPathException::class,
            '_callable' => [$this->property, 'get', $object, '']
        ]);
    }

    public function testGetReturnsValueFromObjectByMultiplePath()
    {
        $object = (object) ['a' => (object) ['b' => 1]];
        $actual = $this->property->get($object, 'a.b');
        $this->assertEquals(1, $actual);
    }

    public function testGetReturnsValueFromObjectByArrayPath()
    {
        $object = (object) ['a' => (object) ['b' => 1]];
        $actual = $this->property->get($object, ['a', 'b']);
        $this->assertEquals(1, $actual);
    }

    public function testGetReturnsValueFromObjectByMethod()
    {
        $object = new class {
            public function getA()
            {
                return 1;
            }
        };
        $actual = $this->property->get($object, 'a');
        $this->assertEquals(1, $actual);
    }

    public function testGetThrownExceptionIfObjectDoesNotHaveProperty()
    {
        $object = (object) ['a' => 1];
        $this->_assertHasException([
            'key' => 'b',
            'source' => $object,
            '_class' => NonexistentKeyException::class,
            '_callable' => [$this->property, 'get', $object, 'b']
        ]);
    }

    public function testGetThrowsForeignExceptionIfCannotGetObjectProperty()
    {
        $object = new class {
            public function __get($name)
            {
                throw new \Exception();
            }
        };
        $this->_assertHasException([
            '_class' => \Exception::class,
            '_callable' => [$this->property, 'get', $object, 'b']
        ]);
    }

    public function testGetThrownExceptionIfPropertyIsNonAccessible()
    {
        $object = new class {
            protected $b;
        };
        $this->_assertHasException([
            '_class' => NonAccessiblePropertyException::class,
            '_callable' => [$this->property, 'get', $object, 'b']
        ]);
    }

    /*
     * End object properties
     */

    public function testGetReturnsValueFromArrayBySinglePath()
    {
        $array = ['a' => 1];
        $actual = $this->property->get($array, 'a');
        $this->assertEquals(1, $actual);
    }

    public function testGetThrowsExceptionIfArrayDoesNotHaveKey()
    {
        $array = ['a' => 1];
        $this->_assertHasException([
            'key' => 'b',
            'source' => $array,
            '_class' => NonexistentKeyException::class,
            '_callable' => [$this->property, 'get', $array, 'b']
        ]);
    }

    public function testGetThrowsInvalidSourceException()
    {
        $source = '';
        $this->_assertHasException([
            'path' => ['b'],
            'source' => $source,
            '_class' => InvalidSourceException::class,
            '_callable' => [$this->property, 'get', $source, 'b']
        ]);
    }

    public function testGetParsesValuePath()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:myValue');
        $this->assertEquals('myValue', $actual);
    }

    public function testGetParsesValuePathAsBoolType()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:true:bool');
        $this->assertEquals(true, $actual);
    }

    public function testGetParsesValuePathAsIntType()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:1:int');
        $this->assertEquals(1, $actual);
    }

    public function testGetParsesValuePathAsFloatType()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:1.1:float');
        $this->assertEquals(1.1, $actual);
    }

    public function testGetParsesValuePathAsArrayType()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:1:array');
        $this->assertEquals([1], $actual);
    }

    public function testGetParsesValuePathReturnsEmptyString()
    {
        $source = ['a' => 1];
        $actual = $this->property->get($source, 'value:');
        $this->assertEquals('', $actual);
    }

    public function testParseValuePathThrowsNonValuePathException()
    {
        $this->_assertHasException([
            'path' => 'notValuePath',
            '_class' => NonValuePathException::class,
            '_callable' => [$this->property, 'parseValuePath', 'notValuePath']
        ]);
    }

    public function testGetterIsCalledFirst()
    {
        $source = new class {
            protected $field;

            public function getField()
            {
                return 'value';
            }
        };
        $value = $this->property->get($source, 'field');
        $this->assertEquals('value', $value);
    }

    public function testMagicGetCanBeUsed()
    {
        $source = new class {
            public function __get($name)
            {
                if ($name === 'field') {
                    return 'value';
                }
                return false;
            }
        };
        $value = $this->property->get($source, 'field');
        $this->assertEquals('value', $value);
    }

    public function testItWorksWithIssuers()
    {
        $source = new class {
            public function isField()
            {
                return 'value';
            }
        };
        $value = $this->property->get($source, 'field');
        $this->assertEquals('value', $value);
    }
}