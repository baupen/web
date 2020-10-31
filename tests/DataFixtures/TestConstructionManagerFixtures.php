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

class TestConstructionManagerFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 0;
    public const CONSTRUCTION_MANAGER_EMAIL = 'test@mangel.io';
    public const CONSTRUCTION_MANAGER_2_EMAIL = 'test2@mangel.io';
    public const CONSTRUCTION_MANAGER_TRIAL_EMAIL = 'trial@mangel.io';
    public const CONSTRUCTION_MANAGER_EXTERNAL_EMAIL = 'external@mangel.io';

    public function load(ObjectManager $manager)
    {
        $entries = [
            [self::CONSTRUCTION_MANAGER_EMAIL, 'asdf', 'GivenName', 'FamilyName'],
            [self::CONSTRUCTION_MANAGER_2_EMAIL, 'asdf', 'GivenName', 'FamilyName'],
            [self::CONSTRUCTION_MANAGER_TRIAL_EMAIL, 'asdf', 'GivenName', 'FamilyName', true],
            [self::CONSTRUCTION_MANAGER_EXTERNAL_EMAIL, 'asdf', 'GivenName', 'FamilyName', false, true],
        ];

        foreach ($entries as $entry) {
            $constructionManager = new ConstructionManager();
            $constructionManager->setEmail($entry[0]);
            $constructionManager->setPasswordFromPlain($entry[1]);
            $constructionManager->setGivenName($entry[2]);
            $constructionManager->setFamilyName($entry[3]);
            $constructionManager->setIsTrialAccount($entry[4] ?? false);
            $constructionManager->setIsExternalAccount($entry[5] ?? false);
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
