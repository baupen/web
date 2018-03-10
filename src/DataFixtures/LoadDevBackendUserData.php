<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDevBackendUserData extends BaseFixture
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
        $user = $manager->getRepository("App:BackendUser")->findOneBy(["email" => "info@example.com"]);
        $user->setPlainPassword("asdf1234");
        $user->setPassword();
        $manager->persist($user);
        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
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
