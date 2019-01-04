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
}
