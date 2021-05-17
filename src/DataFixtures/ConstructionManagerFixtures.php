<?php

/*
 * This file is part of the baupen project.
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
    public const ORDER = 0;

    public function load(ObjectManager $manager)
    {
        $entries = [
            ['j@baupen.ch', 'asdf', 'Julian', 'Dunskus'],
            ['a@baupen.ch', 'asdf', 'Adrian', 'Hoffmann'],
            ['f@baupen.ch', 'asdf', 'Florian', 'Moser'],
        ];

        foreach ($entries as $entry) {
            $constructionManager = new ConstructionManager();
            $constructionManager->setEmail($entry[0]);
            $constructionManager->setPasswordFromPlain($entry[1]);
            $constructionManager->setGivenName($entry[2]);
            $constructionManager->setFamilyName($entry[3]);
            $constructionManager->setCanAssociateSelf(true);
            $manager->persist($constructionManager);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
