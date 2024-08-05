<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataPersister;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Security\TokenTrait;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ConstructionManagerDataPersister implements ContextAwareDataPersisterInterface
{
    use TokenTrait;

    private ContextAwareDataPersisterInterface $decorated;

    private TokenStorageInterface $tokenStorage;

    private UserServiceInterface $userService;

    private ManagerRegistry $manager;

    private IriConverterInterface $iriConverter;

    /**
     * ConstructionManagerDataPersister constructor.
     */
    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, TokenStorageInterface $tokenStorage, UserServiceInterface $userService, ManagerRegistry $manager, IriConverterInterface $iriConverter)
    {
        $this->decorated = $decoratedDataPersister;
        $this->tokenStorage = $tokenStorage;
        $this->userService = $userService;
        $this->manager = $manager;
        $this->iriConverter = $iriConverter;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof ConstructionManager
            && $this->decorated->supports($data, $context);
    }

    /**
     * @param ConstructionManager $data
     */
    public function persist($data, array $context = [])
    {
        if (($context['collection_operation_name'] ?? null) !== 'post') {
            return $this->decorated->persist($data, $context);
        }

        $registrationSuccessful = $this->userService->tryRegister($data, $error);

        // return constructionManager for users who can see them anyways
        $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
        if ($constructionManager && $constructionManager->getCanAssociateSelf()) {
            $constructionManagerRepository = $this->manager->getRepository(ConstructionManager::class);

            return $constructionManagerRepository->findOneBy(['email' => $data->getEmail()]);
        }

        // return status code for unauthenticated registration
        $status = Response::HTTP_CREATED;
        if (!$registrationSuccessful) {
            switch ($error) {
                case UserServiceInterface::REGISTRATION_FAIL_ACCOUNT_DISABLED:
                    $status = Response::HTTP_EXPECTATION_FAILED;
                    break;
                case UserServiceInterface::REGISTRATION_FAIL_EMAIL_NOT_SENT:
                    $status = Response::HTTP_SERVICE_UNAVAILABLE;
                    break;
                case UserServiceInterface::REGISTRATION_FAIL_ALREADY_REGISTERED:
                default:
                    $status = Response::HTTP_BAD_REQUEST;
            }
        }

        return new Response($error, $status);
    }

    /**
     * @param ConstructionSite $data
     */
    public function remove($data, array $context = [])
    {
        $data->delete();

        return $this->decorated->persist($data, $context);
    }
}
