<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Helper\FileHelper;
use App\Service\Interfaces\PathServiceInterface;
use App\Service\Interfaces\SyncServiceInterface;
use App\Service\Interfaces\TrialServiceInterface;
use const DIRECTORY_SEPARATOR;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrialService implements TrialServiceInterface
{
    const AUTHENTICATION_SOURCE_TRIAL = 'trial';

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var PathServiceInterface
     */
    private $pathService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    /**
     * TrialService constructor.
     */
    public function __construct(PathServiceInterface $pathService, TranslatorInterface $translator, SyncServiceInterface $syncService, RequestStack $requestStack, RegistryInterface $registry)
    {
        $this->pathService = $pathService;
        $this->translator = $translator;
        $this->syncService = $syncService;

        $request = $requestStack->getCurrentRequest();
        $this->faker = Factory::create($request->getLocale());
        $this->registry = $registry;
    }

    /**
     * creates a trial account with pre-filled data.
     *
     * @throws Exception
     *
     * @return ConstructionManager
     */
    public function createTrialAccount(?string $proposedGivenName = null, ?string $proposedFamilyName = null)
    {
        $constructionManager = $this->createConstructionManager($proposedGivenName, $proposedFamilyName);
        $constructionSite = $this->createConstructionSite($constructionManager);

        $constructionManager->setActiveConstructionSite($constructionSite);

        $manager = $this->registry->getManager();
        $manager->persist($constructionManager);
        $manager->persist($constructionSite);
        $manager->flush();

        $this->addConstructionSiteContent($constructionSite);

        return $constructionManager;
    }

    /**
     * @return ConstructionSite
     */
    private function createConstructionSite(ConstructionManager $constructionManager)
    {
        $constructionSite = new ConstructionSite();
        $constructionSite->setName($this->translator->trans('construction_site.name', ['%name%' => $constructionManager->getName()], 'trial'));
        $constructionSite->setFolderName($constructionManager->getEmail());
        $constructionSite->setStreetAddress($this->translator->trans('construction_site.street_address', [], 'trial'));
        $constructionSite->setLocality($this->translator->trans('construction_site.locality', [], 'trial'));
        $constructionSite->setPostalCode($this->translator->trans('construction_site.postal_code', [], 'trial'));
        $constructionSite->setCountry($this->translator->trans('construction_site.country', [], 'trial'));
        $constructionSite->setIsTrialConstructionSite(true);

        $constructionSite->getConstructionManagers()->add($constructionManager);
        $constructionManager->getConstructionSites()->add($constructionSite);

        return $constructionSite;
    }

    /**
     * @throws Exception
     */
    private function addConstructionSiteContent(ConstructionSite $constructionSite)
    {
        mkdir($this->pathService->getConstructionSiteFolderRoot() . DIRECTORY_SEPARATOR . $constructionSite->getFolderName());

        $this->copyMapFiles($constructionSite);
        $this->copyConstructionSiteFiles($constructionSite);
        $this->syncService->syncConstructionSite($constructionSite, true);
    }

    /**
     * @throws Exception
     */
    private function copyMapFiles(ConstructionSite $constructionSite)
    {
        $sourceFolder = __DIR__ . DIRECTORY_SEPARATOR . 'Trial' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'maps';
        $targetFolder = $this->pathService->getFolderForMapFile($constructionSite);
        FileHelper::copyRecursively($sourceFolder, $targetFolder);
    }

    /**
     * @throws Exception
     */
    private function copyConstructionSiteFiles(ConstructionSite $constructionSite)
    {
        $sourceFolder = __DIR__ . DIRECTORY_SEPARATOR . 'Trial' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'images';
        $targetFolder = $this->pathService->getFolderForConstructionSiteImage($constructionSite);
        FileHelper::copyRecursively($sourceFolder, $targetFolder);
    }

    /**
     * @throws Exception
     *
     * @return ConstructionManager
     */
    private function createConstructionManager(?string $proposedGivenName, ?string $proposedFamilyName)
    {
        // create manager
        $constructionManager = new ConstructionManager();
        $constructionManager->setIsTrialAccount(true);
        $constructionManager->setIsEnabled(true);
        $constructionManager->setGivenName($proposedGivenName !== null ? $proposedGivenName : $this->faker->firstNameMale);
        $constructionManager->setFamilyName($proposedFamilyName !== null ? $proposedFamilyName : $this->faker->lastName);

        // generate unused email
        $maxTries = 10;
        $repository = $this->registry->getRepository(ConstructionManager::class);
        do {
            $email = $this->generateRandomString(5, '_') . '@test.mangel.io';

            if ($maxTries-- < 0) {
                throw new Exception('unable to create new random email');
            }
        } while ($repository->findOneBy(['email' => $email]) !== null);

        // generate login info
        $password = $this->generateRandomString(10, '-');
        $constructionManager->setEmail($email);
        $constructionManager->setPlainPassword($password);
        $constructionManager->setPassword(true);
        $constructionManager->setAuthenticationHash();
        $constructionManager->setRegistrationDate();

        return $constructionManager;
    }

    /**
     * @return bool|string
     */
    private function generateRandomString(int $minimalLength, string $divider)
    {
        $vocals = 'aeiou';
        $vocalsLength = mb_strlen($vocals);

        //skip because ambiguous: ck, jyi
        $normals = 'bdfghklmnpqrstvwxz';
        $normalsLength = mb_strlen($normals);

        $randomString = '';
        $length = 0;
        do {
            if ($length > 0) {
                $randomString .= $divider;
                ++$length;
            }

            // create bigger group
            $randomString .= $this->getRandomChar($normals, $normalsLength);
            $randomString .= $this->getRandomChar($vocals, $vocalsLength);
            $randomString .= $this->getRandomChar($normals, $normalsLength);
            $length += 3;

            // abort if too big already
            if ($length > $minimalLength) {
                break;
            }

            // create smaller group
            $randomString .= $divider;
            $randomString .= $this->getRandomChar($normals, $normalsLength);
            $randomString .= $this->getRandomChar($vocals, $vocalsLength);
            $length += 3;
        } while ($length < $minimalLength);

        return $randomString;
    }

    /**
     * @return bool|string
     */
    private function getRandomChar(string $selection, int $selectionLength)
    {
        $entry = rand(0, $selectionLength - 1);

        return mb_substr($selection, $entry, 1);
    }
}
