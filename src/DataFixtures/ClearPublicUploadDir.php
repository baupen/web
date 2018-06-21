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
use Doctrine\Common\Persistence\ObjectManager;

class ClearPublicUploadDir extends BaseFixture
{
    const ORDER = 0;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $dir = __DIR__ . "/../../public/upload";
        if (is_dir($dir)) {
            $this->deleteDirectory($dir);
        }

        //recreate dir & gitignore
        mkdir($dir);
        file_put_contents($dir . "/" . ".gitignore", "**/**");
    }

    /**
     * @param $dir
     * @return bool
     */
    private function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
