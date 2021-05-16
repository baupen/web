<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller;

use App\Tests\DataFixtures\TestConstructionManagerFixtures;
use App\Tests\DataFixtures\TestConstructionSiteFixtures;
use App\Tests\Traits\AssertAuthenticationTrait;
use App\Tests\Traits\AssertEmailTrait;
use App\Tests\Traits\TestDataTrait;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LegacyUrlControllerTest extends WebTestCase
{
    use FixturesTrait;
    use AssertEmailTrait;
    use AssertAuthenticationTrait;
    use TestDataTrait;

    public function testCanLoginCraftsman()
    {
        $client = $this->createClient();
        $this->loadFixtures([TestConstructionManagerFixtures::class, TestConstructionSiteFixtures::class]);

        $constructionSite = $this->getTestConstructionSite();
        $craftsman = $constructionSite->getCraftsmen()[0];

        $legacyUrl = '/external/share/c/'.$craftsman->getAuthenticationToken().'?token=ed5aad1d-3698-49e8-baa3-71237127317';
        $newUrl = '/resolve/'.$craftsman->getAuthenticationToken();

        $client->request('GET', $legacyUrl);
        $this->assertResponseRedirects($newUrl);
    }
}
