<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Production;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\ConstructionManager;
use BadMethodCallException;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;

class LoadConstructionManagerData extends BaseFixture
{
    const AUTHENTICATION_SOURCE_FIXTURES = 'fixtures';

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws BadMethodCallException
     * @throws Exception
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $user = new ConstructionManager();
        $user->setEmail('info@mangel.io');
        $user->setPlainPassword('kjkdakjdw');
        $user->setGivenName('Florian');
        $user->setFamilyName('Moser');
        $user->setPassword();
        $user->setAuthenticationHash();
        $user->setRegistrationDate();
        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

    /**
     * create an instance with all random values.
     *
     * @return mixed
     */
    protected function getAllRandomInstance()
    {
        return null;
    }
}
