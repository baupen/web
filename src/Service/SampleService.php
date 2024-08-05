<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\DataFixtures\Model\AssetFile;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Entity\Map;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SampleServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SampleService implements SampleServiceInterface
{
    private PathServiceInterface $pathService;

    private SerializerInterface $serializer;

    private StorageServiceInterface $storageService;

    /**
     * SampleService constructor.
     */
    public function __construct(PathServiceInterface $pathService, SerializerInterface $serializer, StorageServiceInterface $storageService)
    {
        $this->pathService = $pathService;
        $this->serializer = $serializer;
        $this->storageService = $storageService;
    }

    public function createSampleConstructionSite(string $sampleName, ConstructionManager $constructionManager): ConstructionSite
    {
        $samplePath = $this->pathService->getSampleConstructionSite($sampleName);

        // create construction site
        $constructionSiteJsonPath = $samplePath.DIRECTORY_SEPARATOR.'construction_site.json';
        $constructionSiteJson = file_get_contents($constructionSiteJsonPath);
        /** @var ConstructionSite $constructionSite */
        $constructionSite = $this->serializer->deserialize($constructionSiteJson, ConstructionSite::class, 'json');
        $this->storageService->setNewFolderName($constructionSite);

        // add construction site image
        $constructionSiteImagePath = $samplePath.DIRECTORY_SEPARATOR.'preview.jpg';
        if (file_exists($constructionSiteImagePath)) {
            $assetFile = new AssetFile($constructionSiteImagePath);
            $this->storageService->uploadConstructionSiteImage($assetFile, $constructionSite);
        }

        // add content
        $this->addMaps($constructionSite, $samplePath);
        $this->addCraftsmen($constructionSite, $samplePath);
        $this->addIssues($constructionSite, $constructionManager, $samplePath);

        // add construction manager
        $constructionSite->getConstructionManagers()->add($constructionManager);
        $constructionManager->getConstructionSites()->add($constructionSite);

        return $constructionSite;
    }

    private function addCraftsmen(ConstructionSite $constructionSite, string $path): void
    {
        $craftsmenJsonPath = $path.DIRECTORY_SEPARATOR.'craftsmen.json';
        if (!file_exists($craftsmenJsonPath)) {
            return;
        }

        $craftsmenJson = file_get_contents($craftsmenJsonPath);
        /** @var Craftsman[] $craftsmen */
        $craftsmen = $this->serializer->deserialize($craftsmenJson, Craftsman::class.'[]', 'json');
        foreach ($craftsmen as $craftsman) {
            $craftsman->setConstructionSite($constructionSite);
            $constructionSite->getCraftsmen()->add($craftsman);
        }
    }

    private function addMaps(ConstructionSite $constructionSite, string $path): void
    {
        $mapsJsonPath = $path.DIRECTORY_SEPARATOR.'maps.json';
        $mapRelationsJsonPath = $path.DIRECTORY_SEPARATOR.'map_relations.json';
        if (!file_exists($mapsJsonPath) || !file_exists($mapRelationsJsonPath)) {
            return;
        }

        $mapsJson = file_get_contents($mapsJsonPath);
        $mapRelationsJson = file_get_contents($mapRelationsJsonPath);
        /** @var Map[] $maps */
        $maps = $this->serializer->deserialize($mapsJson, Map::class.'[]', 'json');
        $mapRelations = json_decode($mapRelationsJson, true);
        $mapCount = count($maps);
        for ($i = 0; $i < $mapCount; ++$i) {
            $map = $maps[$i];
            $mapParent = $mapRelations[$i];
            $map->setConstructionSite($constructionSite);
            $constructionSite->getMaps()->add($map);

            // add parent link
            if (isset($mapParent['parent'])) {
                $map->setParent($maps[$mapParent['parent']]);
            }

            // check if corresponding map file exist
            $expectedMapFileName = FileHelper::sanitizeFileName($map->getName());
            $mapFilePath = $path.DIRECTORY_SEPARATOR.'map_files'.DIRECTORY_SEPARATOR.$expectedMapFileName.'.pdf';
            if (!file_exists($mapFilePath)) {
                continue;
            }

            // add map file
            $assetFile = new AssetFile($mapFilePath);
            $this->storageService->uploadMapFile($assetFile, $map);
        }
    }

    private function addIssues(ConstructionSite $constructionSite, ConstructionManager $constructionManager, string $path): void
    {
        $issuesJsonPath = $path.DIRECTORY_SEPARATOR.'issues.json';
        $issueRelationsJsonPath = $path.DIRECTORY_SEPARATOR.'issue_relations.json';
        if (!file_exists($issuesJsonPath) || !file_exists($issueRelationsJsonPath)) {
            return;
        }

        $issuesJson = file_get_contents($issuesJsonPath);
        $issueRelationsJson = file_get_contents($issueRelationsJsonPath);
        /** @var Issue[] $issues */
        $issues = $this->serializer->deserialize($issuesJson, Issue::class.'[]', 'json', ['groups' => ['issue-write', 'issue-create']]);
        $issueRelations = json_decode($issueRelationsJson, true);
        $issueCount = count($issues);
        for ($i = 0; $i < $issueCount; ++$i) {
            $issue = $issues[$i];
            $issue->setNumber($i + 1);
            $issue->setCreatedAt(new \DateTime());
            $issue->setCreatedBy($constructionManager);

            $issueRelation = $issueRelations[$i];
            $issue->setConstructionSite($constructionSite);
            $constructionSite->getIssues()->add($issue);

            // add relations
            $craftsmanIndex = $issueRelation['craftsman'];
            /** @var Craftsman $craftsman */
            $craftsman = $constructionSite->getCraftsmen()->get($craftsmanIndex);
            $issue->setCraftsman($craftsman);
            $craftsman->getIssues()->add($issue);

            $mapIndex = $issueRelation['map'];
            /** @var Map $map */
            $map = $constructionSite->getMaps()->get($mapIndex);
            $issue->setMap($map);
            $map->getIssues()->add($issue);

            // no description = no issue image
            if (null === $issue->getDescription()) {
                continue;
            }

            // check if corresponding issue image exist
            $expectedIssueImageFileName = FileHelper::sanitizeFileName($issue->getDescription());
            $issueImagePath = $path.DIRECTORY_SEPARATOR.'issue_images'.DIRECTORY_SEPARATOR.$expectedIssueImageFileName.'.jpg';
            if (!file_exists($issueImagePath)) {
                // $this->logger->error($issueImagePath.' does not exist'); allow issues without images
                continue;
            }

            // add issue image
            $assetFile = new AssetFile($issueImagePath);
            $this->storageService->uploadIssueImage($assetFile, $issue);
        }
    }
}
