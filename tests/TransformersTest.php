<?php declare(strict_types=1);

namespace Mrself\Property\Tests;

use PHPUnit\Framework\TestCase;

class TransformersTest extends TestCase
{
    use TestTrait;

    public function testTransformersAreApplied()
    {
        $object = (object) ['a' => 'ab'];
        $actual = $this->property->get($object, 'a|first');
        $this->assertEquals('a', $actual);
    }

    public function testTransformersAreAppliedAsArgument()
    {
        $object = (object) ['a' => 'ab'];
        $actual = $this->property->get($object, 'a', 'first');
        $this->assertEquals('a', $actual);
    }
}