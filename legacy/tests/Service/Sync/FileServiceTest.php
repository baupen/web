<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Service\Sync;

use App\Service\Sync\FileService;
use App\Tests\Service\Sync\FileServiceResources\PublicFileModel;
use function count;
use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    /**
     * @var FileService
     */
    private $service;

    private $resourcesFolder = __DIR__ . DIRECTORY_SEPARATOR . 'FileServiceResources';

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->service = new FileService();
    }

    public function testGetNewFiles_findsAllFiles()
    {
        $newFiles = $this->service->getNewFiles($this->resourcesFolder, 'pdf', [], function () {
            return new PublicFileModel();
        });

        $this->assertSame(3, count($newFiles));
        $this->assertSame(file_get_contents($this->resourcesFolder . DIRECTORY_SEPARATOR . 'serializedFiles.json'), json_encode($newFiles));

        $again = $this->service->getNewFiles($this->resourcesFolder, 'pdf', $newFiles, function () {
            return new PublicFileModel();
        });
        $this->assertSame(0, count($again));
    }
}
