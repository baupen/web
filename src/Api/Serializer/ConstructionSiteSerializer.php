<?php

namespace App\Api\Serializer;

use App\Entity\ConstructionSite;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class ConstructionSiteSerializer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        assert(count($this->decoratedNormalizer->getSupportedTypes($format)) === 0);
        return [ConstructionSite::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param ConstructionSite $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        unset($normalized['image']);
        if (null !== $data->getImage()) {
            $url = $this->urlGenerator->generate('construction_site_image', [
                'constructionSite' => $data->getId(),
                'constructionSiteImage' => $data->getImage()->getId(),
                'filename' => $data->getImage()->getFilename(),
            ]);

            $normalized['imageUrl'] = $url;
        }

        return $normalized;
    }
}
