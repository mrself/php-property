<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use ICanBoogie\Inflector;
use Mrself\Property\InvalidSourceException;
use Mrself\Property\NonexistentKeyException;
use Mrself\Property\NonValuePathException;
use Mrself\Property\Property;
use PHPUnit\Framework\TestCase;

class GetTest extends TestCase
{
    /**
     * @var Property
     */
    protected $property;

    public function testGetReturnsValueFromObjectBySinglePath()
    {
        $object = (object) ['a' => 1];
        $actual = $this->property->get($object, 'a');
        $this->assertEquals(1, $actual);
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

    protected function setUp()
    {
        parent::setUp();
        $this->property = new Property();
    }

    protected function _assertHasException(array $props)
    {
        $inflector = Inflector::get();
        $class = $props['_class'];
        unset($props['_class']);
        $cbParams = $props['_callable'];
        unset($props['_callable']);
        try {
            $args = array_slice($cbParams, 2);
            call_user_func_array([$cbParams[0], $cbParams[1]], $args);
        } catch (\Exception $e) {
            $this->assertInstanceOf($class, $e);
            foreach ($props as $key => $value) {
                $method = 'get' . $inflector->camelize($key);
                $this->assertEquals($value, $e->$method());
            }
            return;
        }
        $this->assertTrue(false);
    }
}