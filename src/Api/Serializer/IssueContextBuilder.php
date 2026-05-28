<?php

namespace App\Api\Serializer;

use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\Issue;
use App\Security\TokenTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class IssueContextBuilder implements SerializerContextBuilderInterface
{
    use TokenTrait;

    private SerializerContextBuilderInterface $decorated;
    private TokenStorageInterface $tokenStorage;

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
            if ($constructionManager instanceof \App\Entity\ConstructionManager) {
                $context['groups'][] = 'issue-write';
            }

            $craftsman = $this->tryGetCraftsman($this->tokenStorage->getToken());
            if ($craftsman instanceof \App\Entity\Craftsman) {
                $context['groups'][] = 'issue-craftsman-write';
            }
        }

        return $context;
    }
}
