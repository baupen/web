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
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\IssueImage;
use App\Entity\IssuePosition;
use App\Entity\Map;
use App\Service\Interfaces\PathServiceInterface;
use BadMethodCallException;
use DateTime;
use const DIRECTORY_SEPARATOR;
use Doctrine\Common\Persistence\ObjectManager;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\SerializerInterface;

class LoadIssueData extends BaseFixture
{
    const ORDER = EnrichConstructionSiteData::ORDER + LoadConstructionManagerData::ORDER + LoadCraftsmanData::ORDER + ClearContentFolders::ORDER + 1;
    const MULTIPLICATION_FACTOR = 3;

    const REGISTRATION_SET = 1;
    const RESPONSE_SET = 2;
    const REVIEW_SET = 4;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var array to generate random positions/scales; length 11/7/5 which are all prime
     */
    private $xOrientationArray = [0.12, 0.26, 0.31, 0.36, 0.45, 0.56, 0.57, 0.63, 0.74, 0.78, 0.85];
    private $yOrientationArray = [0.21, 0.34, 0.44, 0.51, 0.67, 0.79, 0.89];
    private $scaleArray = [0.2, 0.6, 0.78, 0.89, 1];

    private $currentExponent = 7;

    private $randomMapCounter = 0;
    private $randomCraftsmanCounter = 0;
    private $randomConstructionManagerCounter = 0;

    public function __construct(SerializerInterface $serializer, PathServiceInterface $pathService)
    {
        $this->serializer = $serializer;
        $this->pathService = $pathService;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws BadMethodCallException
     * @throws Exception
     */
    public function load(ObjectManager $manager)
    {
        $issuesJson = file_get_contents(__DIR__ . '/Resources/issues.json');
        $images = glob(__DIR__ . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'issue_images' . DIRECTORY_SEPARATOR . '*.*');

        $getFreshIssueSet = function ($counter) use ($issuesJson) {
            /** @var Issue[] $issues */
            $issues = $this->serializer->deserialize($issuesJson, Issue::class . '[]', 'json');

            //permute
            shuffle($issues);

            //set random positions
            foreach ($issues as $issue) {
                if ($counter % 8 > 0) {
                    // each 8th issue has an optional position
                    $x = $this->xOrientationArray[$counter % \count($this->xOrientationArray)];
                    $y = $this->yOrientationArray[$counter % \count($this->yOrientationArray)];
                    $position = new IssuePosition();
                    if ($counter % 3 === 0) {
                        $position->setPositionX($y);
                        $position->setPositionY($x);
                    } else {
                        $position->setPositionX($x);
                        $position->setPositionY($y);
                    }
                    $position->setPositionZoomScale($this->scaleArray[$counter % \count($this->scaleArray)]);
                    $issue->setPosition($position);
                    $position->setIssue($issue);
                }
                ++$counter;
            }

            return $issues;
        };

        $issueNumber = 1;
        $constructionSites = $manager->getRepository(ConstructionSite::class)->findAll();
        foreach ($constructionSites as $constructionSite) {
            $constructionSiteManagers = $constructionSite->getConstructionManagers()->toArray();
            $craftsmen = $constructionSite->getCraftsmen()->toArray();
            $maps = [];
            foreach ($constructionSite->getMaps() as $map) {
                if ($map->getFile() !== null) {
                    $maps[] = $map;
                }
            }

            for ($i = 0; $i < self::MULTIPLICATION_FACTOR; ++$i) {
                $statusSets = [
                    0,
                    self::REGISTRATION_SET,
                    self::REGISTRATION_SET | self::RESPONSE_SET,
                    self::REGISTRATION_SET | self::RESPONSE_SET | self::REVIEW_SET,
                    self::REGISTRATION_SET | self::REVIEW_SET,
                ];

                foreach ($statusSets as $statusSet) {
                    $this->add($manager, $maps, $craftsmen, $constructionSiteManagers, $getFreshIssueSet($issueNumber), $images, $issueNumber, $statusSet);
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

    /**
     * @param $index
     * @param array $collection
     *
     * @return mixed
     */
    private function getRandomEntry(&$index, array $collection)
    {
        $index = ($index + 1) % \count($collection);

        return $collection[$index];
    }

    /**
     * @return int
     */
    private function getRandomNumber()
    {
        return (7 ** ($this->currentExponent++ % 17)) % 17;
    }

    /**
     * @param ObjectManager         $manager
     * @param Map[]                 $maps
     * @param Craftsman[]           $craftsmen
     * @param ConstructionManager[] $constructionManagers
     * @param Issue[]               $issues
     * @param string[]              $images
     * @param int                   $issueNumber
     * @param int                   $setStatus
     *
     * @throws Exception
     */
    private function add(ObjectManager $manager, array $maps, array $craftsmen, array $constructionManagers, array $issues, array $images, int &$issueNumber, int $setStatus)
    {
        if (\count($constructionManagers) === 0 || \count($maps) === 0 || \count($craftsmen) === 0) {
            return;
        }

        //use global counters so result of randomization is always the same
        $randomMapCounter = $this->randomMapCounter;
        $randomCraftsmanCounter = $this->randomCraftsmanCounter;
        $randomConstructionManagerCounter = $this->randomConstructionManagerCounter;

        foreach ($issues as $issue) {
            $issue->setMap($this->getRandomEntry($randomMapCounter, $maps));
            if ($issue->getPosition() !== null) {
                $issue->getPosition()->setMapFile($issue->getMap()->getFile());
            }

            if ($setStatus !== 0 || $this->getRandomNumber() > 7) {
                //if no status is set leave craftsman null sometime
                $issue->setCraftsman($this->getRandomEntry($randomCraftsmanCounter, $craftsmen));
            } else {
                \assert($issue->getCraftsman() === null);
            }

            $dayOffset = 0;
            if ($setStatus & self::REGISTRATION_SET) {
                $issue->setNumber($issueNumber++);

                if ($setStatus & self::REVIEW_SET) {
                    $issue->setReviewBy($this->getRandomEntry($randomConstructionManagerCounter, $constructionManagers));
                    $dayOffset = $this->getRandomNumber();
                    $issue->setReviewedAt(new DateTime('-' . ($dayOffset) . ' days -' . $this->getRandomNumber() . ' hours'));
                }

                if ($setStatus & self::RESPONSE_SET) {
                    $issue->setResponseBy($issue->getCraftsman());
                    $dayOffset += $this->getRandomNumber() + 1;
                    $issue->setRespondedAt(new DateTime('-' . ($dayOffset) . ' days -' . $this->getRandomNumber() . ' hours'));
                }

                $issue->setRegistrationBy($this->getRandomEntry($randomConstructionManagerCounter, $constructionManagers));
                $dayOffset += $this->getRandomNumber() + 1;
                $issue->setRegisteredAt(new DateTime('-' . ($dayOffset) . ' days -' . $this->getRandomNumber() . ' hours'));
            }

            $issue->setUploadBy($this->getRandomEntry($randomConstructionManagerCounter, $constructionManagers));
            $dayOffset += $this->getRandomNumber() + 1;
            $issue->setUploadedAt(new DateTime('-' . ($dayOffset) . ' days -' . $this->getRandomNumber() . ' hours'));

            if ($this->getRandomNumber() > 7) {
                $issue->setResponseLimit(new DateTime(($this->getRandomNumber()) . ' days'));
            }

            if ($this->getRandomNumber() > 3) {
                // add image to issue
                $sourceImage = $images[$issueNumber * $this->getRandomNumber() % \count($images)];
                $targetFolder = $this->pathService->getFolderForIssueImage($issue->getMap()->getConstructionSite());

                // ensure target folder exists
                if (!file_exists($targetFolder)) {
                    mkdir($targetFolder, 0777, true);
                }

                // create new filename
                $extension = pathinfo($sourceImage, PATHINFO_EXTENSION);
                $fileName = Uuid::uuid4()->toString() . '.' . $extension;
                $targetPath = $targetFolder . DIRECTORY_SEPARATOR . $fileName;

                //copy file to target folder
                copy($sourceImage, $targetPath);

                // save to db
                $file = new IssueImage();
                $file->setFilename($fileName);
                $file->setDisplayFilename($fileName);
                $file->setHash(hash_file('sha256', $targetPath));
                $file->setIssue($issue);
                $issue->setImage($file);
                $issue->getImages()->add($file);
                $manager->persist($file);
            }

            if ($issue->getPosition() !== null) {
                $manager->persist($issue->getPosition());
            }

            $manager->persist($issue);
        }

        //write back values
        $this->randomMapCounter = $randomMapCounter;
        $this->randomCraftsmanCounter = $randomCraftsmanCounter;
        $this->randomConstructionManagerCounter = $randomConstructionManagerCounter;
    }
}
