<?php

/*
 * This file is part of the baupen project.
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
    public const ORDER = 1;
    public const CONSTRUCTION_MANAGER_EMAIL = 'test@baupen.ch';
    public const ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL = 'associated@baupen.ch';
    public const DISASSOCIATED_CONSTRUCTION_MANAGER_EMAIL = 'disassociated@baupen.ch';

    public function load(ObjectManager $manager): void
    {
        $entries = [
            [self::CONSTRUCTION_MANAGER_EMAIL, 'asdf', 'GivenName', 'FamilyName', true],
            [self::ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL, 'asdf', 'GivenName', 'FamilyName', false],
            [self::DISASSOCIATED_CONSTRUCTION_MANAGER_EMAIL, 'asdf', 'GivenName', 'FamilyName', false],
        ];

        foreach ($entries as $entry) {
            $constructionManager = new ConstructionManager();
            $constructionManager->setEmail($entry[0]);
            $constructionManager->setPasswordFromPlain($entry[1]);
            $constructionManager->setGivenName($entry[2]);
            $constructionManager->setFamilyName($entry[3]);
            $constructionManager->setCanAssociateSelf($entry[4]);
            $constructionManager->setIsEnabled(true);
            $manager->persist($constructionManager);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER;
    }
}
