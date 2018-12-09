<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\External\Transformer;

use App\Api\External\Entity\Point;
use App\Entity\IssuePosition;
use App\Entity\MapFile;
use Symfony\Bridge\Doctrine\RegistryInterface;

class IssuePositionTransformer
{
    /**
     * @var RegistryInterface
     */
    private $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param IssuePosition $entity
     *
     * @return \App\Api\External\Entity\IssuePosition
     */
    public function toApi($entity)
    {
        if ($entity === null) {
            return null;
        }

        $position = new \App\Api\External\Entity\IssuePosition();

        $point = new Point();
        $point->setX($entity->getPositionX());
        $point->setY($entity->getPositionY());
        $position->setPoint($point);

        $position->setZoomScale($entity->getPositionZoomScale());
        $position->setMapFileId($entity->getMapFile()->getId());

        return $position;
    }

    public function fromApi(?\App\Api\External\Entity\IssuePosition $position, \App\Entity\Issue $entity)
    {
        $existing = $entity->getPosition();

        if ($position === null) {
            // remove existing if needed
            if ($existing !== null) {
                $manager = $this->doctrine->getManager();
                $manager->remove($existing);
                $manager->flush();
            }

            return null;
        }
        if ($existing === null) {
            $existing = new IssuePosition();
            $existing->setIssue($entity);
        }

        $linkedMapFile = $this->doctrine->getRepository(MapFile::class)->find($position->getMapFileId());
        if ($linkedMapFile === null) {
            return null;
        }

        $existing->setMapFile($linkedMapFile);
        $existing->setPositionX($position->getPoint()->getX());
        $existing->setPositionY($position->getPoint()->getY());
        $existing->setPositionZoomScale($position->getZoomScale());

        return $existing;
    }
}
