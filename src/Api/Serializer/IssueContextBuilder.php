<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Issue;
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class IssueContextBuilder implements SerializerContextBuilderInterface
{
    use TokenTrait;

    private $decorated;
    private $tokenStorage;

    public function __construct(SerializerContextBuilderInterface $decorated, TokenStorageInterface $tokenStorage)
    {
        $this->decorated = $decorated;
        $this->tokenStorage = $tokenStorage;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Issue::class === $resourceClass && isset($context['groups']) && false === $normalization) {
            $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
            if (null !== $constructionManager) {
                $context['groups'][] = 'issue-write';
            }

            $craftsman = $this->tryGetCraftsman($this->tokenStorage->getToken());
            if (null !== $craftsman) {
                $context['groups'][] = 'issue-craftsman-write';
            }
        }

        return $context;
    }
}
