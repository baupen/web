<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Production;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\BackendUser;
use Doctrine\Common\Persistence\ObjectManager;

class LoadBackendUserData extends BaseFixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $user = new BackendUser();
        $user->setEmail('info@example.com');
        $user->setPlainPassword('heafhwabechabwehjcbwa');
        $user->setPassword();
        $user->setResetHash();
        $user->setRegistrationDate(new \DateTime());
        $user->setIsEnabled(true);
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
