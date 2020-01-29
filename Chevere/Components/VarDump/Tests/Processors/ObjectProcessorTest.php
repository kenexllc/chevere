<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Components\VarDump\Tests\Processors;

use Chevere\Components\VarDump\Processors\ObjectProcessor;
use Chevere\Components\X\Tests\AbstractProcessorTest;
use stdClass;

final class ObjectProcessorTest extends AbstractProcessorTest
{
    protected function getProcessorName(): string
    {
        return ObjectProcessor::class;
    }

    protected function getInvalidConstructArgument()
    {
        return [];
    }

    public function testEmptyObject(): void
    {
        $processor = new ObjectProcessor($this->getVarFormat(new stdClass));
        $this->assertSame(stdClass::class, $processor->info());
        $this->assertSame('', $processor->value());
    }

    public function testUnsetObject(): void
    {
        $processor = new ObjectProcessor($this->getVarFormat(new DummyClass));
        $this->assertSame(DummyClass::class, $processor->info());
        $this->assertStringContainsString('public $public null', $processor->value());
        $this->assertStringContainsString('protected $protected null', $processor->value());
        $this->assertStringContainsString('private $private null', $processor->value());
        $this->assertStringContainsString('private $circularReference null', $processor->value());
    }

    public function testObjectProperty(): void
    {
        $processor = new ObjectProcessor($this->getVarFormat((new DummyClass)->withPublic()));
        $this->assertStringContainsString('public $public object stdClass', $processor->value());
    }

    public function testCircularReference(): void
    {
        $object = (new DummyClass)->withCircularReference();
        $processor = new ObjectProcessor($this->getVarFormat($object));
        $this->assertStringContainsString('private $circularReference (circular object reference)', $processor->value());
    }

    public function testDeep(): void
    {
        $object = (new DummyClass)->withProtected();
        $processor = new ObjectProcessor($this->getVarFormat($object)->withDepth(4));
        $this->assertStringContainsString('protected $protected (max depth reached)', $processor->value());
    }
}

final class DummyClass
{
    private object $private;

    protected object $protected;

    public object $public;

    private object $circularReference;

    public function withPrivate(): self
    {
        $new = clone $this;
        $new->private = new stdClass;

        return $new;
    }

    public function withProtected(): self
    {
        $new = clone $this;
        $new->protected = new stdClass;

        return $new;
    }

    public function withPublic(): self
    {
        $new = clone $this;
        $new->public = new stdClass;

        return $new;
    }

    public function withCircularReference(): self
    {
        $new = clone $this;
        $new->circularReference = $new;

        return $new;
    }
}
