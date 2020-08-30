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
use App\Entity\ConstructionSite;
use App\Entity\Note;
use BadMethodCallException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadNoteData extends BaseFixture
{
    const ORDER = EnrichConstructionSiteData::ORDER + LoadConstructionManagerData::ORDER + 1;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws BadMethodCallException
     */
    public function load(ObjectManager $manager)
    {
        $json = file_get_contents(__DIR__ . '/Resources/notes.json');

        $getFreshNoteSet = function () use ($json) {
            return $this->serializer->deserialize($json, Note::class . '[]', 'json');
        };

        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            foreach ($constructionSite->getConstructionManagers() as $constructionManager) {
                /** @var Note[] $notes */
                $notes = $getFreshNoteSet();

                //permute
                shuffle($notes);
                foreach ($notes as $note) {
                    $note->setConstructionSite($constructionSite);
                    $note->setCreatedBy($constructionManager);
                    $manager->persist($note);
                }
            }
        }
        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }
}
