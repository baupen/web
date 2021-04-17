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
use App\Service\Interfaces\ReportServiceInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

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
     * @var ReportServiceInterface
     */
    private $reportService;

    /**
     * @var IriConverterInterface
     */
    private $iriConverter;

    /**
     * EmailDataPersister constructor.
     */
    public function __construct(TokenStorageInterface $tokenStorage, EmailService $emailService, IriConverterInterface $iriConverter, ReportServiceInterface $reportService)
    {
        $this->tokenStorage = $tokenStorage;
        $this->emailService = $emailService;
        $this->iriConverter = $iriConverter;
        $this->reportService = $reportService;
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
        if (!$constructionManager) {
            throw new AuthenticationException();
        }

        $craftsman = $this->iriConverter->getItemFromIri($data->getReceiver());
        if (!$craftsman instanceof Craftsman) {
            throw new BadRequestException('receiver must be a craftsman iri');
        }

        if (!$constructionManager->getConstructionSites()->contains($craftsman->getConstructionSite())) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        if (\App\Entity\Email::TYPE_CRAFTSMAN_ISSUE_REMINDER === $data->getType()) {
            $craftsmanReport = $this->reportService->createCraftsmanReport($craftsman, $craftsman->getLastVisitOnline());
            $success = $this->emailService->sendCraftsmanIssueReminder($constructionManager, $craftsman, $craftsmanReport, $data->getSubject(), $data->getBody(), $data->getSelfBcc());
        } else {
            throw new BadRequestException('unknown email type');
        }

        $statusCode = $success ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE;

        return new Response(Response::$statusTexts[$statusCode], $statusCode);
    }

    public function remove($data, array $context = [])
    {
        throw new \BadMethodCallException();
    }
}
