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

namespace Chevere\App;

use Chevere\File;
use Chevere\Path\PathHandle;

final class Build
{
    const FILE_INDETIFIER = 'var:build';

    /** @var PathHandle */
    private $pathHandle;

    public function __construct()
    {
        $this->pathHandle =  new PathHandle(static::FILE_INDETIFIER);
    }

    public function pathHandle(): PathHandle
    {
        return $this->pathHandle;
    }

    public function exists(): bool
    {
        return File::exists($this->pathHandle->path());
    }
}