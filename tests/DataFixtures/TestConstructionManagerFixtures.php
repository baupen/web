<?php

namespace App\Tests\DataFixtures;

use App\Entity\ConstructionManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestConstructionManagerFixtures extends Fixture implements OrderedFixtureInterface
{
    public const int ORDER = 1;
    public const string CONSTRUCTION_MANAGER_EMAIL = 'test@test.ch';
    public const string ASSOCIATED_CONSTRUCTION_MANAGER_EMAIL = 'associated@test.ch';
    public const string DISASSOCIATED_CONSTRUCTION_MANAGER_EMAIL = 'disassociated@test.ch';

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
            $constructionManager->setPassword(password_hash($entry[1], PASSWORD_BCRYPT));
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
