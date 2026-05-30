<?php

namespace App\Api\Serializer;

use App\Entity\Map;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class MapSerializer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        assert(count($this->decoratedNormalizer->getSupportedTypes($format)) === 0);
        return [Map::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param Map $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        unset($normalized['file']);
        if (null !== $data->getFile()) {
            $url = $this->urlGenerator->generate('map_file', [
                'map' => $data->getId(),
                'mapFile' => $data->getFile()->getId(),
                'filename' => $data->getFile()->getFilename(),
            ]);

            $normalized['fileUrl'] = $url;
        }

        return $normalized;
    }
}
