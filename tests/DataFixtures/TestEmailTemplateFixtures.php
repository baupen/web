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

use App\Entity\ConstructionSite;
use App\Entity\EmailTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestEmailTemplateFixtures extends Fixture implements OrderedFixtureInterface
{
    public const ORDER = TestConstructionSiteFixtures::ORDER + 1;

    public function load(ObjectManager $manager)
    {
        $constructionSiteRepository = $manager->getRepository(ConstructionSite::class);
        $constructionSite = $constructionSiteRepository->findOneBy(['name' => TestConstructionSiteFixtures::TEST_CONSTRUCTION_SITE_NAME]);

        $emailTemplate = new EmailTemplate();
        $emailTemplate->setConstructionSite($constructionSite);
        $emailTemplate->setPurpose(EmailTemplate::PURPOSE_OPEN_ISSUES);
        $emailTemplate->setName('Template');
        $emailTemplate->setSubject('Subject');
        $emailTemplate->setBody('Body');
        $emailTemplate->setSelfBcc(true);
        $manager->persist($emailTemplate);

        $manager->flush();
    }

    public function getOrder()
    {
        return self::ORDER;
    }
}
