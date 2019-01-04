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

class DisplayNameService_NormalizeMapNamesTest extends TestCase
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

    public function testNoChangeForSimple()
    {
        $this->assertCasesMatchExpected(
            [
                'map',
                'map2',
                'map3',
            ],
            [
                'map',
                'map2',
                'map3',
            ]
        );
    }

    public function testRemoveDateGroups()
    {
        $this->assertCasesMatchExpected(
            [
                'map 180916',
                'map2 180816',
                'map3 181016',
            ],
            [
                'map',
                'map2',
                'map3',
            ]
        );
    }

    public function testRemoveSameGroups()
    {
        $this->assertCasesMatchExpected(
            [
                'SAMEGROUP map',
                'SAMEGROUP map2',
                'SAMEGROUP map3',
            ],
            [
                'map',
                'map2',
                'map3',
            ]
        );
    }

    private function assertCasesMatchExpected($case, $expected)
    {
        $this->assertSame($expected, $this->service->normalizeMapNames($case));
    }
}
