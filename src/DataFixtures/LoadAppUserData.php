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
        $entries = [
            ["j", "asdf", "Julian", "Dunskus"],
            ["a", "asdf", "Adrian", "Hoffmann"],
            ["f", "asdf", "Florian", "Moser"]
        ];

        foreach ($entries as $entry) {
            $appUser = new AppUser();
            $appUser->setIdentifier($entry[0]);
            $appUser->setPlainPassword($entry[1]);
            $appUser->setGivenName($entry[2]);
            $appUser->setFamilyName($entry[3]);
            $appUser->setAuthenticationToken();
            $appUser->setPassword();
            $manager->persist($appUser);
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
        $appUser->setAuthenticationToken();
        $this->fillRandomPerson($appUser);

        return $appUser;
    }
}
