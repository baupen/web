<?php

namespace App\Api\Serializer;

use App\Entity\Issue;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class IssueNormalizer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        assert(count($this->decoratedNormalizer->getSupportedTypes($format)) === 0);
        return [Issue::class => true];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param Issue $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        unset($normalized['image']);
        if (null !== $data->getImage()) {
            $url = $this->urlGenerator->generate('issue_image', [
                'issue' => $data->getId(),
                'issueImage' => $data->getImage()->getId(),
                'filename' => $data->getImage()->getFilename(),
            ]);

            $normalized['imageUrl'] = $url;
        }

        if ($data->hasPosition()) {
            $url = $this->urlGenerator->generate('issue_map_render', [
                'issue' => $data->getId(),
            ]);

            $normalized['mapRenderUrl'] = $url;
        }

        return $normalized;
    }
}
