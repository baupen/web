<?php

namespace App\Api\Processor;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Api\Entity\CraftsmanEmail;
use App\Entity\ConstructionManager;
use App\Entity\ConstructionSite;
use App\Entity\Issue;
use App\Entity\IssueEvent;
use App\Enum\EmailType;
use App\Enum\IssueState;
use App\Helper\DoctrineHelper;
use App\Security\TokenTrait;
use App\Service\EmailService;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\ReportServiceInterface;
use App\Service\Interfaces\StorageServiceInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class CraftsmanEmailProcessor implements ProcessorInterface
{
    use TokenTrait;

    /**
     * @param ProcessorInterface<CraftsmanEmail, CraftsmanEmail> $persistProcessor
     */
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $persistProcessor,
        private ManagerRegistry       $doctrine,
        private TokenStorageInterface $tokenStorage,
        private ReportServiceInterface $reportService,
        private EmailServiceInterface $emailService,
        private ManagerRegistry $managerRegistry
    )
    {
    }

    /**
     * @param CraftsmanEmail $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CraftsmanEmail
    {
        if ($operation instanceof Post) {
            $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
            if (!$constructionManager instanceof ConstructionManager) {
                throw new AuthenticationException();
            }

            $craftsman = $data->getCraftsman();
            if (!EmailService::tryConstructAddress($craftsman->getEmail(), $craftsman->getContactName()) instanceof Address) {
                throw new BadRequestException('Craftsman ' . $craftsman->getContactName() . ' has an invalid E-Mail set: ' . $craftsman->getEmail());
            }

            foreach ($craftsman->getEmailCCs() as $emailCC) {
                if (!EmailService::tryConstructAddress($emailCC) instanceof Address) {
                    throw new BadRequestException('Craftsman ' . $craftsman->getContactName() . ' has an invalid CC E-Mail set: ' . $emailCC);
                }
            }

            $craftsmanReport = $this->reportService->createCraftsmanReport($craftsman, $craftsman->getLastVisitOnline());
            $success = $this->emailService->sendCraftsmanIssueReminder($constructionManager, $craftsman, $craftsmanReport, $data->getSubject(), $data->getBody(), $data->getSelfBcc(), $email);
            if (!$success) {
                throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE);
            }

            // add event
            $payload = [
                'receiver' => $craftsman->getEmail(),
                'receiverCCs' => $craftsman->getEmailCCs(),
                'receiverBCC' => $data->getSelfBcc() ? $constructionManager->getEmail() : null,
                'subject' => $data->getSubject(),
                'body' => $data->getBody(),
                'type' => EmailType::CRAFTSMAN_ISSUE_REMINDER->name,
            ];
            $issueEvent = IssueEvent::createFromEmail($craftsman->getConstructionSite(), $craftsman->getId(), $constructionManager->getId(), $payload);
            DoctrineHelper::persistAndFlush($this->managerRegistry, $issueEvent);

            return $data;
        }

        throw new BadRequestException('Craftsman email can only be sent via POST');
    }
}
