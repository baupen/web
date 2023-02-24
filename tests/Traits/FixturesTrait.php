<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use ApiPlatform\Symfony\Bundle\Test\Client;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\BrowserKit\AbstractBrowser;

trait FixturesTrait
{
    protected function loadFixtures(Client $client, array $classNames = [], bool $append = false): void
    {
        $databaseTool = $client->getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures($classNames, $append);
    }

    protected function loadFixturesBrowser(AbstractBrowser $browser, array $classNames = [], bool $append = false): void
    {
        $databaseTool = $browser->getContainer()->get(DatabaseToolCollection::class)->get();
        $databaseTool->loadFixtures($classNames, $append);
    }
}
