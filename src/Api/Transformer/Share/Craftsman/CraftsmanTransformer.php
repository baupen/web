<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Craftsman;

use App\Entity\Craftsman;
use Symfony\Component\Routing\RouterInterface;

class CraftsmanTransformer
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
     */
    public function __construct(\App\Api\Transformer\Base\CraftsmanTransformer $craftsmanTransformer, RouterInterface $router)
    {
        $this->craftsmanTransformer = $craftsmanTransformer;
        $this->router = $router;
    }

    /**
     * @param Craftsman $entity
     *
     * @return \App\Api\Entity\Base\Craftsman
     */
    public function toApi($entity, string $identifier, bool $canRespondToIssues)
    {
        $craftsman = new \App\Api\Entity\Share\Craftsman\Craftsman($entity->getId());
        $this->craftsmanTransformer->writeApiProperties($entity, $craftsman);

        $craftsman->setReportUrl($this->router->generate('external_report_craftsman', ['identifier' => $identifier, 'hash' => uniqid()]));
        $craftsman->setCanRespondToIssues($canRespondToIssues);
        $craftsman->setReadOnlyViewUrl($this->router->generate('external_share_craftsman', ['identifier' => $entity->getEmailIdentifier()]));

        return $craftsman;
    }
}
