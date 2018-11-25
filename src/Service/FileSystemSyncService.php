<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionSite;
use App\Service\Interfaces\FileSystemSyncServiceInterface;
use App\Service\Interfaces\PathServiceInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FileSystemSyncService implements FileSystemSyncServiceInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    public function __construct(RegistryInterface $registry, PathServiceInterface $pathService)
    {
        $this->registry = $registry;
        $this->pathService = $pathService;
    }

    /**
     * syncs the filesystem with the database, creating/updating construction sites as needed.
     */
    public function sync()
    {
        $constructionSites = $this->registry->getRepository(ConstructionSite::class)->findAll();
        $existingDirectories = glob($this->pathService->getFolderRoot() . \DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
    }
}
