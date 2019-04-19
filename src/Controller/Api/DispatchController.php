<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Api;

use App\Api\Request\ConstructionSiteRequest;
use App\Api\Request\CraftsmenRequest;
use App\Api\Response\Data\CraftsmenData;
use App\Api\Response\Data\ProcessingEntitiesData;
use App\Api\Transformer\Dispatch\CraftsmanTransformer;
use App\Controller\Api\Base\ApiController;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Entity\Email;
use App\Enum\EmailType;
use App\Helper\DateTimeFormatter;
use App\Model\Craftsman\CurrentIssueState;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/dispatch")
 */
class DispatchController extends ApiController
{
    const INVALID_CRAFTSMAN = 'invalid craftsman';

    /**
     * gives the appropriate error code the specified error message.
     *
     * @param string $message
     *
     * @return int
     */
    protected function errorMessageToStatusCode($message)
    {
        return parent::errorMessageToStatusCode($message);
    }

    /**
     * @Route("/craftsman/list", name="api_dispatch_craftsman_list", methods={"POST"})
     *
     * @param Request $request
     * @param CraftsmanTransformer $craftsmanTransformer
     *
     * @return Response
     */
    public function listAction(Request $request, CraftsmanTransformer $craftsmanTransformer)
    {
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, ConstructionSiteRequest::class, $constructionSiteRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        $data = new CraftsmenData();
        $data->setCraftsmen($craftsmanTransformer->toApiMultiple($constructionSite->getCraftsmen()->toArray()));

        return $this->success($data);
    }

    /**
     * @Route("", name="api_dispatch", methods={"POST"})
     *
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param EmailServiceInterface $emailService
     *
     * @throws \Exception
     * @throws \Exception
     *
     * @return Response
     */
    public function dispatchAction(Request $request, TranslatorInterface $translator, EmailServiceInterface $emailService)
    {
        /** @var CraftsmenRequest $dispatchRequest */
        /** @var ConstructionSite $constructionSite */
        if (!$this->parseConstructionSiteRequest($request, CraftsmenRequest::class, $dispatchRequest, $errorResponse, $constructionSite)) {
            return $errorResponse;
        }

        //get all craftsmen
        $craftsmanRepo = $this->getDoctrine()->getRepository(Craftsman::class);
        /** @var Craftsman[] $craftsmen */
        $craftsmen = [];
        foreach ($dispatchRequest->getCraftsmanIds() as $craftsmanId) {
            /** @var Craftsman $craftsman */
            $craftsman = $craftsmanRepo->find($craftsmanId);
            if (!$craftsman->getConstructionSite() === $constructionSite) {
                return $this->fail(self::INVALID_CRAFTSMAN);
            }
            $craftsmen[] = $craftsman;
        }

        $now = new \DateTime();
        $dispatchData = new ProcessingEntitiesData();
        foreach ($craftsmen as $craftsman) {
            //count event occurrences
            $state = new CurrentIssueState($craftsman, $now);

            //only send emails if there are issues
            if ($state->getNotRespondedIssuesCount() === 0) {
                $dispatchData->addSkippedId($craftsman->getId());
                continue;
            }

            //send mail & remember if it worked
            if ($this->sendMail($craftsman, $state, $constructionSite, $emailService, $translator)) {
                $craftsman->setLastEmailSent(new \DateTime());
                $this->fastSave($craftsman);
                $dispatchData->addSuccessfulId($craftsman->getId());
            } else {
                $dispatchData->addFailedId($craftsman->getId());
            }
        }

        return $this->success($dispatchData);
    }

    /**
     * @param Craftsman $craftsman
     * @param CurrentIssueState $state
     * @param ConstructionSite $constructionSite
     * @param EmailServiceInterface $emailService
     * @param TranslatorInterface $translator
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function sendMail(Craftsman $craftsman, CurrentIssueState $state, ConstructionSite $constructionSite, EmailServiceInterface $emailService, TranslatorInterface $translator)
    {
        // build up base text
        if ($state->getOverdueIssuesCount() > 0) {
            $subject = $translator->trans('email.overdue.subject', ['%count%' => $state->getOverdueIssuesCount()], 'dispatch');
            $body = $translator->trans('email.overdue.body', ['%count%' => $state->getNotRespondedIssuesCount()], 'dispatch');
        } elseif ($state->getNotReadIssuesCount() > 0) {
            $subject = $translator->trans('email.unread.subject', ['%count%' => $state->getNotReadIssuesCount()], 'dispatch');
            $body = $translator->trans('email.unread.body', ['%count%' => $state->getNotRespondedIssuesCount()], 'dispatch');
        } else {
            $subject = $translator->trans('email.open.subject', ['%count%' => $state->getNotRespondedIssuesCount()], 'dispatch');
            $body = $translator->trans('email.open.body', ['%count%' => $state->getNotRespondedIssuesCount()], 'dispatch');
        }

        //append next limit info
        if ($state->getOverdueIssuesCount() === 0 && $state->getNextResponseLimit() !== null) {
            $body .= "\n";
            $body .= $translator->trans(
                'email.body_limit_info',
                ['%limit%' => $state->getNextResponseLimit()->format(DateTimeFormatter::DATE_FORMAT)],
                'dispatch'
            );
        }

        //append closed issues info
        if ($state->getRecentlyReviewedIssuesCount() > 0) {
            $body .= "\n";
            $body .= $translator->trans('email.body_closed_issues_info', ['%count%' => $state->getRecentlyReviewedIssuesCount()], 'dispatch');
        }

        //append suffix
        $subject .= $translator->trans('email.subject_appendix', ['%construction_site_name%' => $constructionSite->getName()], 'dispatch');

        //send email
        $email = new Email();
        $email->setEmailType(EmailType::ACTION_EMAIL);
        $email->setReceiver($craftsman->getEmail());
        $email->setSubject($subject);
        $email->setBody($body);
        $email->setActionText($translator->trans('email.action_text', [], 'dispatch'));
        $email->setActionLink($this->generateUrl('external_share_craftsman', ['identifier' => $craftsman->getEmailIdentifier(), 'token' => $craftsman->getWriteAuthorizationToken()], UrlGeneratorInterface::ABSOLUTE_URL));
        $this->fastSave($email);

        if ($emailService->sendEmail($email)) {
            $email->setSentDateTime(new \DateTime());
            $this->fastSave($email);

            return true;
        }

        return false;
    }
}
