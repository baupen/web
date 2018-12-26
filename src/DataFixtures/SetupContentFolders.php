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
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $sourceFolder = __DIR__ . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'persistent';
        $targetFolder = $this->pathService->getFolderRoot();
        $this->copyRecursively($sourceFolder, $targetFolder);

        $this->fileSystemSyncService->sync();
    }

    /**
     * @param $sourceFolder
     * @param $destinationFolder
     *
     * @throws \Exception
     */
    private function copyRecursively($sourceFolder, $destinationFolder)
    {
        if (!is_dir($destinationFolder)) {
            mkdir($destinationFolder);
        }

        $dir = opendir($sourceFolder);
        if ($dir === false) {
            throw new \Exception('failed to open dir ' . $dir);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($sourceFolder . '/' . $file)) {
                    $this->copyRecursively($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
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
