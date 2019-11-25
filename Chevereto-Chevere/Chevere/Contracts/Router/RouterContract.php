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

namespace Chevere\Contracts\Router;

use Chevere\Components\Folder\Exceptions\UnserializeException;
use Chevere\Components\Router\Exception\RouteNotFoundException;
use Chevere\Contracts\Route\RouteContract;
use Psr\Http\Message\UriInterface;
use TypeError;

interface RouterContract
{
    const CACHE_ID = 'router';

    /**
     * Return an instance with the specified RouterPropertiesContract.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified RouterPropertiesContract.
     */
    public function withProperties(RouterPropertiesContract $properties): RouterContract;

    /**
     * Returns a boolean indicating whether the instance can resolve routing.
     */
    public function canResolve(): bool;

    /**
     * Returns a RoutedContract for the given UriInterface.
     *
     * @throws UnserializeException   if the route string object can't be unserialized
     * @throws TypeError              if the found route doesn't implement the RouteContract
     * @throws RouteNotFoundException if no route resolves the given UriInterface
     */
    public function resolve(UriInterface $uri): RoutedContract;
}
