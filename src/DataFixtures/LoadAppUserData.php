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
use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\Craftsman;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAppUserData extends BaseFixture
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
        /* @var AppUser[] $appUsers */
        $appUsers = $this->loadSomeRandoms($manager);

        $testAppUser = $this->getAllRandomInstance();
        $testAppUser->setPlainPassword("asdf");
        $testAppUser->setIdentifier("j");
        $appUsers[] = $testAppUser;

        foreach ($appUsers as $appUser) {
            $appUser->setPassword();
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }

    /**
     * create an instance with all random values.
     *
     * @return AppUser
     */
    protected function getAllRandomInstance()
    {
        $faker = $this->getFaker();

        $appUser = new AppUser();
        $appUser->setPlainPassword($faker->asciify());
        $appUser->setIdentifier($faker->asciify());
        $this->fillRandomPerson($appUser);

        return $appUser;
    }
}
