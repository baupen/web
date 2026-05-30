<?php

namespace App\Api\Serializer;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Security\TokenTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class CraftsmanSerializer implements NormalizerInterface
{
    use TokenTrait;

    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator, private TokenStorageInterface $tokenStorage)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        assert(count($this->decoratedNormalizer->getSupportedTypes($format)) === 0);
        return [Craftsman::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param Craftsman $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        if ($this->tryGetConstructionManager($this->tokenStorage->getToken()) instanceof ConstructionManager) {
            $url = $this->urlGenerator->generate('public_resolve', [
                'token' => $data->getAuthenticationToken(),
            ]);

            $normalized['resolveUrl'] = $url;
        }

        return $normalized;
    }
}
