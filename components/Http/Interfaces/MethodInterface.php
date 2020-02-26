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

namespace Chevere\Components\Http\Interfaces;

interface MethodInterface
{
    /**
     * @return string Method name RFC 7231.
     */
    public static function name(): string;

    /**
     * @return string Method description RFC 7231.
     */
    public static function description(): string;
}
