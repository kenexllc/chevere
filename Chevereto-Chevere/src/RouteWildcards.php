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

class RouteWildcards
{
    /** @var string Key set representation */
    public $set;

    /** @var array */
    public $wildcards;

    /** @var array An array containing all the key sets for the route (optionals combo) */
    public $powerSet;

    /** @var array An array containing the optional wildcards */
    protected $optionals;

    /** @var array An array indexing the optional wildcards */
    protected $optionalsIndex;

    /** @var array An array indexing the mandatory wildcards */
    protected $mandatoryIndex;

    public function __construct(string $routeString)
    {
        // $matches[0] => [{wildcard}, {wildcard?},...]
        // $matches[1] => [wildcard, wildcard?,...]
        if (!preg_match_all(Route::REGEX_WILDCARD_SEARCH, $routeString, $matches)) {
            return;
        }
        $this->set = $routeString;
        $this->optionals = [];
        $this->optionalsIndex = [];
        foreach ($matches[0] as $k => $v) {
            // Change {wildcard} to {n} (n is the wildcard index)
            if (isset($this->set)) {
                $this->set = Utils\Str::replaceFirst($v, "{{$k}}", $this->set);
            }
            $wildcard = $matches[1][$k];
            if (Utils\Str::endsWith('?', $wildcard)) {
                $wildcardTrim = Utils\Str::replaceLast('?', null, $wildcard);
                $this->optionals[] = $k;
                $this->optionalsIndex[$k] = $wildcardTrim;
            } else {
                $wildcardTrim = $wildcard;
            }
            if (in_array($wildcardTrim, $this->wildcards ?? [])) {
                throw new CoreException(
                    (new Message('Must declare one unique wildcard per capturing group, duplicated %s detected in route %r.'))
                        ->code('%s', $matches[0][$k])
                        ->code('%r', $routeString)
                );
            }
            $this->wildcards[] = $wildcardTrim;
        }
        // Determine if route contains optional wildcards
        if (!empty($this->optionals)) {
            $mandatoryDiff = array_diff($this->wildcards ?? [], $this->optionalsIndex);
            $this->mandatoryIndex = [];
            foreach ($mandatoryDiff as $k => $v) {
                $this->mandatoryIndex[$k] = null;
            }
            // Generate the optionals power set, keeping its index keys in case of duplicated optionals
            $powerSet = Utils\Arr::powerSet($this->optionals, true);
            // Build the route set, it will contain all the possible route combinations
            $this->powerSet = $this->processPowerSet($powerSet);
        }
    }

    protected function processPowerSet(array $powerSet): array
    {
        $routeSet = [];
        foreach ($powerSet as $set) {
            $auxSet = $this->set;
            // auxWildcards keys represent the wildcards being used. Iterate it with foreach.
            $auxWildcards = $this->mandatoryIndex;
            foreach ($set as $replaceKey => $replaceValue) {
                $replace = $this->optionals[$replaceKey];
                if ($replaceValue !== null) {
                    $replaceValue = "{{$replaceValue}}";
                    $auxWildcards[$replace] = null;
                }
                $auxSet = str_replace("{{$replace}}", $replaceValue ?? '', $auxSet);
                $auxSet = Path::normalize($auxSet);
            }
            ksort($auxWildcards);
            /*
             * Maps expected regex indexed matches [0,1,2,] to registered wildcard index [index=>n].
             * For example, a set /test-{0}--{2} will capture 0->0 and 1->2. Storing the expected index allows\
             * to easily map matches => wildcards => values.
             */
            $routeSet[$auxSet] = array_keys($auxWildcards);
        }

        return $routeSet;
    }
}