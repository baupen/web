<?php

namespace App\Tests\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AssertAuthenticationTrait
{
    private function assertNotAuthenticated(KernelBrowser $client): void
    {
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/');
        $this->assertResponseRedirects('/login');
    }

    private function assertAuthenticated(KernelBrowser $client): void
    {
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/');
        $this->assertResponseIsSuccessful();
    }
}
