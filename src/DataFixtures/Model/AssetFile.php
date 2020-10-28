<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Model;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AssetFile extends UploadedFile
{
    public function __construct(string $path)
    {
        $originalName = pathinfo($path, PATHINFO_BASENAME);

        $newPath = $path.'.tmp';
        copy($path, $newPath);

        parent::__construct($newPath, $originalName);
    }

    public function move(string $directory, string $name = null)
    {
        $target = $this->getTargetFile($directory, $name);

        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $renamed = copy($this->getPathname(), $target);
        restore_error_handler();
        if (!$renamed) {
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
        }

        chmod($target, 0666 & ~umask());

        return $target;
    }
}
