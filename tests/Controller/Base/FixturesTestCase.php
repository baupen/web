<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Controller\Base;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class FixturesTestCase extends WebTestCase
{
    protected static $application;

    /**
     * @return RegistryInterface
     */
    protected function getDoctrine(): RegistryInterface
    {
        return $this->doctrine;
    }

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        $client = static::createClient();

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $commands = [
            'doctrine:migrations:migrate -q',
            'doctrine:fixtures:load -n -q',
        ];

        foreach ($commands as $command) {
            $application->run(new StringInput($command));
        }

        $this->doctrine = $client->getKernel()->getContainer()
            ->get('doctrine');
    }

    /**
     * @return bool
     */
    protected function preventImageUploadTesting()
    {
        if (getenv("TRAVIS") === "true") {
            $this->markTestSkipped(
                'On Travis, skipping tests dealing with image upload because of unreliable results.'
            );
            return true;
        }
        return false;
    }

    /**
     * @var RegistryInterface $doctrine
     */
    private $doctrine;
}
