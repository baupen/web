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

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\ConstructionSiteImage;
use App\Entity\IssueImage;
use App\Entity\MapFile;
use App\Service\Interfaces\CacheServiceInterface;

class CacheServiceDataPersister implements ContextAwareDataPersisterInterface
{
    private $decorated;
    private $cache;

    public function __construct(ContextAwareDataPersisterInterface $decoratedDataPersister, CacheServiceInterface $cache)
    {
        $this->decorated = $decoratedDataPersister;
        $this->cache = $cache;
    }

    public function supports($data, array $context = []): bool
    {
        return ($data instanceof ConstructionSiteImage || $data instanceof IssueImage || $data instanceof MapFile) &&
            ($context['collection_operation_name'] ?? null) === 'post' &&
            $this->decorated->supports($data, $context);
    }

    public function persist($data, array $context = [])
    {
        $result = $this->decorated->persist($data, $context);

        if ($data instanceof ConstructionSiteImage) {
            $this->cache->warmUpCacheForConstructionSiteImage($data);
        } elseif ($data instanceof IssueImage) {
            $this->cache->warmUpCacheForIssueImage($data);
        } elseif ($data instanceof MapFile) {
            $this->cache->warmUpCacheForMapFile($data);
        } else {
            throw new \Exception('Unsupported class: '.get_class($data));
        }

        return $result;
    }

    public function remove($data, array $context = [])
    {
        return $this->decorated->remove($data, $context);
    }
}
