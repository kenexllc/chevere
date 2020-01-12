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

namespace Chevere\Components\Runtime;

use Chevere\Components\Data\Data;
use Chevere\Components\Data\Traits\DataMethodTrait;
use Chevere\Components\Runtime\Interfaces\RuntimeInterface;
use Chevere\Components\Runtime\Interfaces\SetInterface;

/**
 * Runtime applies runtime config and provide data about the App Runtime.
 */
final class Runtime implements RuntimeInterface
{
    use DataMethodTrait;

    /**
     * Creates a new instance.
     *
     * @param SetInterface $runtimeContract
     */
    public function __construct(SetInterface ...$setContract)
    {
        $this->data = new Data([]);
        foreach ($setContract as $set) {
            $this->data = $this->data
                ->withAddedKey(
                    $set->name(),
                    $set->value()
                );
        }
        // $this->data = $this->data
        //     ->withAddedKey('errorReportingLevel', error_reporting());
    }
}
