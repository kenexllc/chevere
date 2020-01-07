<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests\Http;

use InvalidArgumentException;
use Chevere\Components\Http\Method;
use Chevere\Contracts\Http\MethodContract;
use PHPUnit\Framework\TestCase;

final class MethodTest extends TestCase
{
    public function testBadConstruct(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Method('get');
    }

    public function testConstruct(): void
    {
        foreach (MethodContract::ACCEPT_METHOD_NAMES as $methodName) {
            $method = new Method($methodName);
            $this->assertSame($methodName, $method->toString());
        }
    }
}
