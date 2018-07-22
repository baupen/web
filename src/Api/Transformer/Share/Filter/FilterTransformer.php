<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Share\Filter;

use App\Entity\Filter;
use Symfony\Component\Routing\RouterInterface;

class FilterTransformer
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * CraftsmanTransformer constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Filter $entity
     * @param array $args
     *
     * @return \App\Api\Entity\Share\Filter\Filter
     */
    public function toApi($entity, string $identifier)
    {
        $issue = new \App\Api\Entity\Share\Filter\Filter($entity->getId());

        //because we can't cache reports anyways; just generate a random hash anytime this is converted
        $issue->setReportUrl($this->router->generate('external_report_filter', ['identifier' => $identifier, 'hash' => sha1(time())]));

        return $issue;
    }
}
