<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests\DataFixtures;

use App\Entity\ConstructionManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestUserFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 0;
    public const TEST_EMAIL = 'test@mangel.io';

    public function load(ObjectManager $manager)
    {
        $entries = [
            [self::TEST_EMAIL, 'asdf', 'GivenName', 'FamilyName'],
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
