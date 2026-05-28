<?php

namespace App\DataFixtures;

use App\Entity\ConstructionManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConstructionManagerFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = 1;

    public function load(ObjectManager $manager): void
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
            $constructionManager->setAuthenticationHash();
            $manager->persist($constructionManager);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER;
    }
}
