<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\DataPersister;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Api\Entity\Email;
use App\Entity\Craftsman;
use App\Security\TokenTrait;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EmailDataPersister implements ContextAwareDataPersisterInterface
{
    use TokenTrait;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * EmailDataPersister constructor.
     */
    public function __construct(TokenStorageInterface $tokenStorage, EmailService $emailService, IriConverterInterface $iriConverter)
    {
        $this->tokenStorage = $tokenStorage;
        $this->emailService = $emailService;
        $this->iriConverter = $iriConverter;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Email && ($context['collection_operation_name'] ?? null) === 'post';
    }

    /**
     * @param Email $data
     */
    public function persist($data, array $context = [])
    {
        $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());

        $craftsman = $this->iriConverter->getItemFromIri($data->getReceiver());
        if (!$craftsman instanceof Craftsman) {
            throw new BadRequestException('receiver must be a craftsman iri');
        }

        $success = $this->emailService->sendCraftsmanIssueReminder($constructionManager, $craftsman, $data->getSubject(), $data->getBody(), $data->getSelfBcc());

        $statusCode = $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new Response($statusCode);
    }

    public function remove($data, array $context = [])
    {
        throw new \BadMethodCallException();
    }
}
