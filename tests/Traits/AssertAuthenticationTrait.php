<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AssertAuthenticationTrait
{
    private function assertNotAuthenticated(KernelBrowser $client)
    {
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    private function assertAuthenticated(KernelBrowser $client)
    {
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
    }
}
