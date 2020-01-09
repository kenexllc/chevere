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

namespace Chevere\Components\App;

use InvalidArgumentException;
use Chevere\Components\File\FilePhp;
use Chevere\Components\File\FileReturn;
use Chevere\Components\Message\Message;
use Chevere\Components\Variable\VariableExport;
use Chevere\Components\App\Contracts\BuildContract;
use Chevere\Components\App\Contracts\CheckoutContract;
use Chevere\Components\File\Contracts\FileReturnContract;

/**
 * Checkout the application build.
 * The checkout consists in the creation of a build file which maps the build checksums.
 */
final class Checkout implements CheckoutContract
{
    private BuildContract $build;

    private FileReturnContract $fileReturn;

    /**
     * Creates a new instance.
     */
    public function __construct(BuildContract $build)
    {
        $this->build = $build;
        $this->assertIsMaked();
        $file = $this->build->file();
        if ($file->exists()) {
            $file->remove();
        }
        $file->create();
        $this->fileReturn = new FileReturn(
            new FilePhp($file)
        );
        $this->fileReturn->put(
            new VariableExport($this->build->checksums())
        );
    }

    public function fileReturn(): FileReturnContract
    {
        return $this->fileReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function checksum(): string
    {
        return $this->fileReturn->filePhp()->file()->checksum();
    }

    /**
     * The BuildContract must be maked to checkout the application.
     */
    private function assertIsMaked(): void
    {
        if (!$this->build->isMaked()) {
            throw new InvalidArgumentException(
                (new Message('Instance of %type% %argument% must be built to construct a %className% instance'))
                    ->code('%type%', BuildContract::class)
                    ->code('%argument%', '$build')
                    ->code('%className%', self::class)
                    ->toString()
            );
        }
    }
}
