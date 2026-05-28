<?php

namespace App\Api\Serializer;

use App\Entity\Craftsman;
use App\Entity\Filter;
use App\Security\TokenTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class FilterSerializer implements NormalizerInterface
{
    use TokenTrait;

    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator, private TokenStorageInterface $tokenStorage)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Filter::class];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param Filter $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        $url = $this->urlGenerator->generate('public_filtered', [
            'token' => $data->getAuthenticationToken(),
        ]);

        $normalized['filteredUrl'] = $url;

        return $normalized;
    }
}
