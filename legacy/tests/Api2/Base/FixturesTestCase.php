<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\Api2\Base;

use Exception;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class FixturesTestCase extends WebTestCase
{
    protected static $application;

    protected function getDoctrine(): RegistryInterface
    {
        return $this->doctrine;
    }

    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
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
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $client = static::createClient();
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $this->doctrine = $client->getKernel()->getContainer()->get('doctrine');
    }

    /**
     * @var RegistryInterface
     */
    private $doctrine;
}
