<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services\Sync;

use App\Service\Sync\DisplayNameService;
use PHPUnit\Framework\TestCase;

class DisplayNameServiceTest extends TestCase
{
    /**
     * @var DisplayNameService
     */
    private $service;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->service = new DisplayNameService();
    }

    public function testForConstructionSite()
    {
        $cases = [
            '1034_Sun_Park' => '1034 Sun Park',
        ];

        foreach ($cases as $source => $target) {
            $this->assertSame($target, $this->service->forConstructionSite($source));
        }
    }

    public function testForConstructionSiteImage()
    {
        $cases = [
            'image.jpg' => 'image',
            'image.jpg.png' => 'image.jpg',
            'image_duplicate_2018-01-01T12_44.jpg' => 'image',
        ];

        foreach ($cases as $source => $target) {
            $this->assertSame($target, $this->service->forConstructionSiteImage($source));
        }
    }

    public function testForMapFile()
    {
        $cases = [
            'map.pdf' => 'map',
            '1 1 Obergeschoss_links.pdf' => '1. 1. Obergeschoss links',
            '1Obergeschoss.pdf' => '1. Obergeschoss',
            'Haus2.pdf' => 'Haus 2',
            '1Obergeschoss_links.pdf' => '1. Obergeschoss links',
            'ObergeschossHausA.pdf' => 'Obergeschoss Haus A',
            '1218_502_Obergeschoss_160815.pdf' => '1218 502 Obergeschoss 160815',
        ];

        foreach ($cases as $source => $target) {
            $this->assertSame($target, $this->service->forMapFile($source));
        }
    }

    public function testNormalizeMapNames()
    {
        $cases = [
            [
                'map 180916',
                'map2 180816',
                'map3 181016',
            ],
            [
                'SAMEGROUP map 180916',
                'SAMEGROUP map2 180816',
                'SAMEGROUP map3 181016',
            ],
        ];

        $expected = [
            [
                'map',
                'map2',
                'map3',
            ],
            [
                'map',
                'map2',
                'map3',
            ],
        ];

        for ($i = 0; $i < \count($cases); ++$i) {
            $this->assertSame($expected[$i], $this->service->normalizeMapNames($cases[$i]));
        }
    }

    public function testPutIntoTreeStructure_simpleParentWithChildren()
    {
        $elementNames = [
            1 => 'parent',
            2 => 'parent child1',
            3 => 'parent child2',
        ];

        $createNewElement = function () {
            $this->fail('no parent should be created');
        };

        $assignChildToParentCallCount = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCallCount, &$seenChildIds) {
            $this->assertSame(1, $parentId);
            $seenChildIds[] = $childId;

            ++$assignChildToParentCallCount;
        };

        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent);

        $this->assertSame(2, $assignChildToParentCallCount);
        $this->assertEquals([2, 3], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
    }

    public function testPutIntoTreeStructure_parentCreatedOnDemand()
    {
        $elementNames = [
            1 => 'parent',
            2 => 'parent child 1',
            3 => 'parent child 2',
        ];

        $newParentId = 4;
        $createNewElementCallCount = 0;
        $createNewElement = function ($name) use (&$createNewElementCallCount, $newParentId) {
            $this->assertSame($name, 'parent child');
            ++$createNewElementCallCount;

            return $newParentId;
        };

        $assignChildToParentCallCount = 0;
        $seenChildIds = [];
        $assignChildToParent = function ($childId, $parentId) use (&$assignChildToParentCallCount, $newParentId, &$seenChildIds) {
            if ($childId === $newParentId) {
                $this->assertSame(1, $parentId);
            } else {
                $this->assertSame($newParentId, $parentId);
            }
            $seenChildIds[] = $childId;

            ++$assignChildToParentCallCount;
        };

        $this->service->putIntoTreeStructure($elementNames, $createNewElement, $assignChildToParent);

        $this->assertSame(3, $assignChildToParentCallCount);
        $this->assertSame(1, $createNewElementCallCount);
        $this->assertEquals([2, 3, 4], $seenChildIds, '$canonicalize = true', 0.0, 1, true);
    }
}
