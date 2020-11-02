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

use App\Entity\ConstructionManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConstructionManagerFixtures extends Fixture implements OrderedFixtureInterface
{
    const ORDER = 0;

    public function load(ObjectManager $manager)
    {
        $entries = [
            ['j@mangel.io', 'asdf', 'Julian', 'Dunskus'],
            ['a@mangel.io', 'asdf', 'Adrian', 'Hoffmann'],
            ['f@mangel.io', 'asdf', 'Florian', 'Moser'],
        ];

        foreach ($entries as $entry) {
            $constructionManager = new ConstructionManager();
            $constructionManager->setEmail($entry[0]);
            $constructionManager->setPasswordFromPlain($entry[1]);
            $constructionManager->setGivenName($entry[2]);
            $constructionManager->setFamilyName($entry[3]);
            $constructionManager->setIsEnabled(true);
            $manager->persist($constructionManager);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}