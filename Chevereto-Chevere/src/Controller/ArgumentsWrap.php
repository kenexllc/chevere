<?php

declare(strict_types=1);

/*
 * This file is part of Chevereto\Core.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevere\Controller;

use InvalidArgumentException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use Chevere\Message;
use Chevere\Contracts\Controller\ControllerContract;
use Chevere\Contracts\Controller\ArgumentsWrapContract;

/**
 * ArgumentsWrap provides a object oriented way to retrieve typehinted arguments for the controller.
 */
final class ArgumentsWrap implements ArgumentsWrapContract
{
    /** @var array Typehinted arguments ready to use */
    private $typedArguments;

    /** @var ReflectionFunctionAbstract */
    private $reflection;

    /** @var array Passed callable arguments */
    private $passedArguments;

    /** @var array Usable arguments (FIXME: Better bame) */
    private $arguments;

    /** @var ControllerContract */
    private $controller;

    public function __construct(ControllerContract $controller, array $arguments)
    {
        $this->controller = $controller;
        $this->passedArguments = $arguments;
        $this->processArguments();
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    private function processArguments()
    {
        $this->reflection = new ReflectionMethod($this->controller, '__invoke');
        $this->typedArguments = [];
        $parameterIndex = 0;
        // Magically create typehinted arguments
        foreach ($this->reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (!isset($this->passedArguments[$name])) {
                throw new InvalidArgumentException(
                    (new Message('Unmatched argument %argument% in %controller%'))
                        ->code('%argument%', $name)
                        ->code('%controller%', get_class($this->controller).'::__invoke')
                        ->toString()
                );
            }
            $type = null;
            $parameterType = $parameter->getType();
            if (isset($parameterType)) {
                $type = $parameterType->getName();
            }
            $this->processTypedArgument(
                $parameter,
                $type,
                $this->passedArguments[$name] ?? $this->passedArguments[$parameterIndex] ?? null
            );
            ++$parameterIndex;
        }
        $this->arguments = $this->typedArguments;
    }

    private function processTypedArgument(ReflectionParameter $parameter, string $type = null, $value = null): void
    {
        if (!isset($type) || in_array($type, Controller::TYPE_DECLARATIONS)) {
            $this->typedArguments[] = $value ?? ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null);
        } elseif (null === $value && $parameter->allowsNull()) {
            $this->typedArguments[] = null;
        } else {
            $this->typedArguments[] = new $type($value);
        }
    }
}
