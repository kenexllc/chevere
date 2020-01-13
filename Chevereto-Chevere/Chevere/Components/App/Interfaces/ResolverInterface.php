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

namespace Chevere\Components\App\Interfaces;

use Chevere\Components\App\Interfaces\BuilderInterface;

interface ResolverInterface
{
    public function __construct(ResolvableInterface $resolvable);

    public function builder(): BuilderInterface;
}
