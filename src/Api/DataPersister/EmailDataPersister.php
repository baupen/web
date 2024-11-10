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

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Api\Entity\Email;
use App\Entity\Craftsman;
use App\Entity\IssueEvent;
use App\Enum\IssueEventTypes;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\EmailService;
use App\Service\Interfaces\ReportServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EmailDataPersister implements ContextAwareDataPersisterInterface
{
    use TokenTrait;

    private TokenStorageInterface $tokenStorage;

    private EmailService $emailService;

    private ReportServiceInterface $reportService;

    private IriConverterInterface $iriConverter;

    private ManagerRegistry $managerRegistry;

    /**
     * EmailDataPersister constructor.
     */
    public function __construct(TokenStorageInterface $tokenStorage, EmailService $emailService, IriConverterInterface $iriConverter, ReportServiceInterface $reportService, ManagerRegistry $managerRegistry)
    {
        $this->tokenStorage = $tokenStorage;
        $this->emailService = $emailService;
        $this->iriConverter = $iriConverter;
        $this->reportService = $reportService;
        $this->managerRegistry = $managerRegistry;
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
        if (!$constructionManager instanceof \App\Entity\ConstructionManager) {
            throw new AuthenticationException();
        }

        $craftsman = $this->iriConverter->getResourceFromIri($data->getReceiver());
        if (!$craftsman instanceof Craftsman) {
            throw new BadRequestException('receiver must be a craftsman iri');
        }

        if (!$constructionManager->getConstructionSites()->contains($craftsman->getConstructionSite())) {
            throw new HttpException(Response::HTTP_FORBIDDEN);
        }

        if (!EmailService::tryConstructAddress($craftsman->getEmail(), $craftsman->getContactName()) instanceof \Symfony\Component\Mime\Address) {
            throw new BadRequestException('Craftsman '.$craftsman->getContactName().' has an invalid E-Mail set: '.$craftsman->getEmail());
        }

        foreach ($craftsman->getEmailCCs() as $emailCC) {
            if (!EmailService::tryConstructAddress($emailCC) instanceof \Symfony\Component\Mime\Address) {
                throw new BadRequestException('Craftsman '.$craftsman->getContactName().' has an invalid CC E-Mail set: '.$emailCC);
            }
        }

        if (\App\Entity\Email::TYPE_CRAFTSMAN_ISSUE_REMINDER === $data->getType()) {
            $craftsmanReport = $this->reportService->createCraftsmanReport($craftsman, $craftsman->getLastVisitOnline());
            $success = $this->emailService->sendCraftsmanIssueReminder($constructionManager, $craftsman, $craftsmanReport, $data->getSubject(), $data->getBody(), $data->getSelfBcc());

            // add event
            $issueEvent = new IssueEvent();
            $issueEvent->setConstructionSite($craftsman->getConstructionSite());
            $issueEvent->setRoot($craftsman->getId());
            $issueEvent->setCreatedBy($constructionManager->getId());
            $issueEvent->setLastChangedBy($constructionManager->getId());
            $issueEvent->setTimestamp(new \DateTime());
            $issueEvent->setType(IssueEventTypes::Email);

            $payload = [
                'receiver' => $craftsman->getEmail(),
                'receiverCCs' => $craftsman->getEmailCCs(),
                'receiverBCC' => $data->getSelfBcc() ? $constructionManager->getEmail() : null,
                'subject' => $data->getSubject(),
                'body' => $data->getBody(),
                'type' => IssueEvent::EMAIL_TYPE_CRAFTSMAN_ISSUE_REMINDER,
            ];
            $issueEvent->setPayload(json_encode($payload));

            DoctrineHelper::persistAndFlush($this->managerRegistry, $issueEvent);
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
