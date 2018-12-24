<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Services;

use App\Service\DisplayNameService;
use App\Service\Interfaces\DisplayNameServiceInterface;
use PHPUnit\Framework\TestCase;

class DisplayNameServiceTest extends TestCase
{
    /**
     * @var DisplayNameServiceInterface
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
            '1Obergeschoss.pdf' => '1. Obergeschoss',
            'Haus2.pdf' => 'Haus 2',
            '1Obergeschoss_links.pdf' => '1. Obergeschoss links',
            'ObergeschossHausA.pdf' => 'Obergeschoss HausA',
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
                'map2 180916',
                'map3 180916',
            ],
            [
                'SAMEGROUP map 180916',
                'SAMEGROUP map2 180916',
                'SAMEGROUP map3 180916',
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
}
