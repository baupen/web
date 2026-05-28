<?php

namespace App\Api\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Entity\Issue;
use App\Security\TokenTrait;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsDecorator('api_platform.serializer.context_builder')]
readonly class ApiContextBuilder implements SerializerContextBuilderInterface
{
    use TokenTrait;

    public function __construct(private SerializerContextBuilderInterface $decorated, private TokenStorageInterface $tokenStorage)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (Issue::class === $resourceClass && isset($context['groups']) && false === $normalization) {
            $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
            if ($constructionManager instanceof ConstructionManager) {
                $context['groups'][] = 'issue:write';
            }

            $craftsman = $this->tryGetCraftsman($this->tokenStorage->getToken());
            if ($craftsman instanceof Craftsman) {
                $context['groups'][] = 'issue:write-craftsman';
            }
        }

        if (ConstructionManager::class === $resourceClass && isset($context['groups']) && true === $normalization) {
            $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
            if ($constructionManager instanceof ConstructionManager) {
                $context['groups'][] = 'construction-manager:read-self';
            }
        }

        return $context;
    }
}
