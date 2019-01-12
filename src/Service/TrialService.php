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
use App\Service\Interfaces\TrialServiceInterface;
use Faker\Factory;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TrialService implements TrialServiceInterface
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var \Faker\Generator
     */
    private $faker;

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * TrialService constructor.
     *
     * @param RequestStack $requestStack
     * @param RegistryInterface $registry
     */
    public function __construct(RequestStack $requestStack, RegistryInterface $registry)
    {
        $request = $requestStack->getCurrentRequest();
        $this->baseUrl = $request->getHost();
        $this->faker = Factory::create($request->getLocale());
        $this->registry = $registry;
    }

    /**
     * creates a trial account with prefilled data.
     *
     * @param string|null $proposedGivenName
     * @param string|null $proposedFamilyName
     *
     * @return ConstructionManager
     */
    public function createTrialAccount(?string $proposedGivenName, ?string $proposedFamilyName)
    {
        $constructionManager = $this->createConstructionManager($proposedGivenName, $proposedFamilyName);

        $manager = $this->registry->getManager();
        $manager->persist($constructionManager);
        $manager->flush();

        return $constructionManager;
    }

    /**
     * @param string|null $proposedGivenName
     * @param string|null $proposedFamilyName
     *
     * @return ConstructionManager
     */
    private function createConstructionManager(?string $proposedGivenName, ?string $proposedFamilyName)
    {
        // create manager
        $constructionManager = new ConstructionManager();
        $constructionManager->setIsTrialAccount(true);
        $constructionManager->setGivenName($proposedGivenName !== null ? $proposedGivenName : $this->faker->firstNameMale);
        $constructionManager->setFamilyName($proposedFamilyName !== null ? $proposedFamilyName : $this->faker->lastName);

        // generate login info
        $email = $this->generateRandomString(10, '_') . '@test.' . $this->baseUrl;
        $password = $this->generateRandomString(10, '-');
        $constructionManager->setEmail($email);
        $constructionManager->setPlainPassword($password);
        $constructionManager->setPassword(true);
        $constructionManager->setResetHash();
        $constructionManager->setRegistrationDate();

        return $constructionManager;
    }

    /**
     * @param int $minimalLength
     * @param string $divider
     *
     * @return bool|string
     */
    private function generateRandomString(int $minimalLength, string $divider)
    {
        $vocals = 'aeiou';
        $vocalsLength = \mb_strlen($vocals);

        //skip because ambiguous: ck, jyi
        $normals = 'bdfghklmnpqrstvwxz';
        $normalsLength = \mb_strlen($normals);

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
     * @param string $selection
     * @param int $selectionLength
     *
     * @return bool|string
     */
    private function getRandomChar(string $selection, int $selectionLength)
    {
        $entry = rand(0, $selectionLength - 1);

        return mb_substr($selection, $entry, 1);
    }
}
