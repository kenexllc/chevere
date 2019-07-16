<?php

declare(strict_types=1);

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Chevere;

class RouteWildcardValidation
{
    /** @var string */
    protected $wildcardName;

    /** @var string */
    protected $wildcardString;

    /** @var string */
    protected $regex;

    /** @var string */
    protected $routeKey;

    public function __construct(string $wildcardName, string $regex, Route $route)
    {
        $this->wildcardName = $wildcardName;
        $this->wildcardString = "{{$wildcardName}}";
        $this->regex = $regex;
        $this->routeKey = $route->getKey();
        $this->routeWheres = $route->getWheres();
        $this->handleValidateFormat();
        $this->handleValidateMatch();
        $this->handleValidateUnique();
        $this->handleValidateRegex();
    }

    protected function handleValidateFormat()
    {
        if (!$this->validateFormat($this->wildcardName)) {
            throw new CoreException(
                (new Message("String %s must contain only alphanumeric and underscore characters and it shouldn't start with a numeric value."))
                    ->code('%s', $this->wildcardName)
            );
        }
    }

    protected function validateFormat(string $wildcardName): bool
    {
        return !Utils\Str::startsWithNumeric($wildcardName) && preg_match('/^[a-z0-9_]+$/i', $wildcardName);
    }

    protected function handleValidateMatch()
    {
        if (!$this->validateMatch($this->wildcardName, $this->routeKey)) {
            throw new CoreException(
                (new Message("Wildcard %s doesn't exists in %r."))
                    ->code('%s', $this->wildcardString)
                    ->code('%r', $this->routeKey)
            );
        }
    }

    protected function validateMatch(string $wildcardName, string $routeKey): bool
    {
        return Utils\Str::contains("{{$wildcardName}}", $routeKey) || Utils\Str::contains('{'."$wildcardName?".'}', $routeKey);
    }

    protected function handleValidateUnique()
    {
        if (!$this->validateUnique($this->wildcardName, $this->routeWheres)) {
            throw new CoreException(
                (new Message('Where clause for %s wildcard has been already declared.'))
                    ->code('%s', $this->wildcardString)
            );
        }
    }

    protected function validateUnique(string $wildcardName, ?array $haystack): bool
    {
        return !isset($haystack[$wildcardName]);
    }

    protected function handleValidateRegex()
    {
        if (!Validate::regex('/'.$this->wildcardName.'/')) {
            throw new CoreException(
                (new Message('Invalid regex pattern %s.'))
                    ->code('%s', $this->regex)
            );
        }
    }
}