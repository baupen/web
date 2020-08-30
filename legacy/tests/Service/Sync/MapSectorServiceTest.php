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

use App\Entity\ConstructionSite;
use App\Entity\MapFile;
use App\Model\SyncTransaction;
use App\Service\Sync\MapSectorService;
use App\Tests\Service\Sync\Mock\ObjectManagerMock;
use App\Tests\Service\Sync\Mock\PathServiceMock;
use function count;
use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\TestCase;

class MapSectorServiceTest extends TestCase
{
    /**
     * @var MapSectorService
     */
    private $service;

    private $resourcesFolder = __DIR__.DIRECTORY_SEPARATOR.'MapSectorServiceResources';

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $pathService = new PathServiceMock($this->resourcesFolder);
        $this->service = new MapSectorService($pathService);
    }

    public function testParses3OGCorrectly()
    {
        $transaction = new SyncTransaction();
        $constructionSite = $this->createConstructionSite('3OG.pdf');
        $this->service->syncMapSectors($transaction, $constructionSite);

        $sectors = $constructionSite->getMapFiles()[0]->getSectors();
        $this->assertSame(2, count($sectors));

        $sector1 = $sectors[0];
        $this->assertSame('id_1', $sector1->getIdentifier());
        $this->assertSame('Dusche1', $sector1->getName());
        $this->assertSame(3, count($sector1->getPoints()));

        $point1 = $sector1->getPoints()[1];
        $this->assertSame(0.8, $point1->x);
        $this->assertSame(0.9, $point1->y);

        $this->assertTransactionPersist($transaction, 2);

        // check detected that no change
        $transaction = new SyncTransaction();
        $this->service->syncMapSectors($transaction, $constructionSite);
        $this->assertTransactionPersist($transaction, 0);

        // check change detected
        $sector1->getPoints()[0]->x = 2;
        $transaction = new SyncTransaction();
        $this->service->syncMapSectors($transaction, $constructionSite);
        $this->assertTransactionPersist($transaction, 1);
    }

    /**
     * @return ConstructionSite
     */
    private function createConstructionSite(string $mapFileName)
    {
        $constructionSite = new ConstructionSite();

        $mapFile = new MapFile();
        $mapFile->setFilename($mapFileName);
        $mapFile->setConstructionSite($constructionSite);
        $constructionSite->getMapFiles()->add($mapFile);

        return $constructionSite;
    }

    private function assertTransactionPersist(SyncTransaction $transaction, int $expected)
    {
        $saves = 0;
        $transaction->execute(new ObjectManagerMock(), function () use (&$saves) {
            ++$saves;

            return false;
        });
        $this->assertSame($expected, $saves);
    }
}
