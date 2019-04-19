<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Dispatch;

use App\Api\External\Transformer\Base\BatchTransformer;
use App\Entity\Craftsman;
use App\Model\Craftsman\CurrentIssueState;
use Symfony\Component\Routing\RouterInterface;

class CraftsmanTransformer extends BatchTransformer
{
    /**
     * @var \App\Api\Transformer\Base\CraftsmanTransformer
     */
    private $craftsmanTransformer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param \App\Api\Transformer\Base\CraftsmanTransformer $craftsmanTransformer
     * @param RouterInterface $router
     */
    public function __construct(\App\Api\Transformer\Base\CraftsmanTransformer $craftsmanTransformer, RouterInterface $router)
    {
        $this->craftsmanTransformer = $craftsmanTransformer;
        $this->router = $router;
    }

    /**
     * @param Craftsman $entity
     *
     * @throws \Exception
     *
     * @return \App\Api\Entity\Dispatch\Craftsman
     */
    public function toApi($entity)
    {
        $craftsman = new \App\Api\Entity\Dispatch\Craftsman($entity->getId());
        $this->craftsmanTransformer->writeApiProperties($entity, $craftsman);

        $craftsman->setLastEmailSent($entity->getLastEmailSent());
        $craftsman->setLastOnlineVisit($entity->getLastOnlineVisit());
        $craftsman->setPersonalUrl($this->router->generate('external_share_craftsman', ['identifier' => $entity->getEmailIdentifier(), 'writeAuthenticationToken' => $entity->getWriteAuthorizationToken()]));

        $state = new CurrentIssueState($entity, new \DateTime());
        $craftsman->setNotRespondedIssuesCount($state->getNotRespondedIssuesCount());
        $craftsman->setNotReadIssuesCount($state->getNotReadIssuesCount());
        $craftsman->setNextResponseLimit($state->getNextResponseLimit());

        return $craftsman;
    }
}
