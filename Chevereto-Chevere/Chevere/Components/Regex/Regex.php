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

namespace Chevere\Components\Regex;

use Throwable;
use Chevere\Components\Message\Message;
use Chevere\Components\Regex\Exceptions\RegexException;
use Chevere\Components\Regex\Interfaces\RegexInterface;

final class Regex implements RegexInterface
{
    /** @var string */
    private string $regex;

    /**
     * Creates a new instance.
     *
     * @throws RegexException if $regex is not a valid regular expresion
     */
    public function __construct(string $regex)
    {
        $this->regex = $regex;
        $this->assertRegex();
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->regex;
    }

    private function assertRegex(): void
    {
        try {
            preg_match($this->regex, '');
        } catch (Throwable $e) {
            throw new RegexException(
                (new Message('Invalid regex string %regex% provided %error%'))
                    ->code('%regex%', $this->regex)
                    ->code('%error%', $e->getMessage())
                    ->toString(),
                0,
                $e
            );
        }
    }
}
