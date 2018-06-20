<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 12:38 PM
 */

namespace App\Tests\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class FixturesTestCase extends WebTestCase
{
    protected static $application;

    /**
     * @throws \Exception
     */
    protected function setUp()
    {
        /*
        $client = static::createClient();

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $commands = [
            'doctrine:migrations:migrate -q',
            'doctrine:fixtures:load -n -q'
        ];

        foreach ($commands as $command) {
            $application->run(new StringInput($command));
        }
        */
    }
}