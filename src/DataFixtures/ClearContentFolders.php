<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ClearContentFolders extends BaseFixture
{
    const ORDER = 0;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $this->deleteDirectoryContents($this->pathService->getTransientFolderRoot(), true, ['.gitignore', '.htaccess']);
        $this->deleteDirectoryContents($this->pathService->getFolderRoot(), true, ['.gitignore', '.htaccess']);
    }

    /**
     * @param $dir
     * @param bool $isRoot
     * @param array $exceptions
     *
     * @return bool
     */
    private function deleteDirectoryContents($dir, $isRoot = false, $exceptions = [])
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir) && !\in_array(basename($dir), $exceptions, true)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..' || \in_array($item, $exceptions, true)) {
                continue;
            }

            if (!$this->deleteDirectoryContents($dir . \DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        if (!$isRoot) {
            return rmdir($dir);
        }

        return true;
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
