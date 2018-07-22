<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Transformer\Base;

use App\Entity\Issue;

class PublicIssueTransformer
{
    /**
     * @var IssueTransformer
     */
    private $issueTransformer;

    /**
     * PublicIssueTransformer constructor.
     *
     * @param IssueTransformer $issueTransformer
     */
    public function __construct(IssueTransformer $issueTransformer)
    {
        $this->issueTransformer = $issueTransformer;
    }

    /**
     * @param Issue $source
     * @param \App\Api\Entity\Base\PublicIssue $target
     */
    public function writeApiProperties($source, $target)
    {
        $this->issueTransformer->writeApiProperties($source, $target);

        $target->setRegisteredAt($source->getRegisteredAt());
        $target->setRegistrationByName($source->getRegistrationBy()->getName());
        $target->setNumber($source->getNumber());
    }
}
