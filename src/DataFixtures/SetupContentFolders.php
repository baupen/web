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
use App\Service\Interfaces\FileSystemSyncServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class SetupContentFolders extends BaseFixture
{
    const ORDER = ClearContentFolders::ORDER + 1;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FileSystemSyncServiceInterface
     */
    private $fileSystemSyncService;

    public function __construct(PathServiceInterface $pathService, SerializerInterface $serializer, FileSystemSyncServiceInterface $fileSystemSyncService)
    {
        $this->pathService = $pathService;
        $this->serializer = $serializer;
        $this->fileSystemSyncService = $fileSystemSyncService;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $sourceFolder = __DIR__ . \DIRECTORY_SEPARATOR . 'construction_sites';
        $targetFolder = $this->pathService->getFolderRoot();
        $this->recurse_copy($sourceFolder, $targetFolder);

        $this->fileSystemSyncService->sync();
    }

    private function recurse_copy($sourceFolder, $destinationFolder)
    {
        $dir = opendir($sourceFolder);
        if (!is_dir($destinationFolder)) {
            @mkdir($destinationFolder);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($sourceFolder . '/' . $file)) {
                    $this->recurse_copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                } else {
                    copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
