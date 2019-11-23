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
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SyncServiceInterface;
use BadMethodCallException;
use const DIRECTORY_SEPARATOR;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
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
     * @var SyncServiceInterface
     */
    private $fileSystemSyncService;

    /**
     * SetupContentFolders constructor.
     */
    public function __construct(PathServiceInterface $pathService, SerializerInterface $serializer, SyncServiceInterface $fileSystemSyncService)
    {
        $this->pathService = $pathService;
        $this->serializer = $serializer;
        $this->fileSystemSyncService = $fileSystemSyncService;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws BadMethodCallException
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $sourceFolder = __DIR__ . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'persistent';
        $targetFolder = $this->pathService->getFolderRoot();
        FileHelper::copyRecursively($sourceFolder, $targetFolder);

        $this->fileSystemSyncService->sync();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
