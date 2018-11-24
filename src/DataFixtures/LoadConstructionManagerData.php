<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\ConstructionManager;
use Doctrine\Common\Persistence\ObjectManager;

class LoadConstructionManagerData extends BaseFixture
{
    const ORDER = 0;

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
            ['j@mangel.io', 'asdf', 'Julian', 'Dunskus'],
            ['a@mangel.io', 'asdf', 'Adrian', 'Hoffmann'],
            ['f@mangel.io', 'asdf', 'Florian', 'Moser'],
            ['l@mangel.io', 'asdf', 'Lexu', 'Cardoso'],
        ];

        foreach ($entries as $entry) {
            $appUser = new ConstructionManager();
            $appUser->setEmail($entry[0]);
            $appUser->setPlainPassword($entry[1]);
            $appUser->setGivenName($entry[2]);
            $appUser->setFamilyName($entry[3]);
            $appUser->setPassword();
            $appUser->setResetHash();
            $appUser->setRegistrationDate();
            $manager->persist($appUser);
        }
        $manager->flush();
    }

    public function getOrder()
    {
        return static::ORDER;
    }
}
