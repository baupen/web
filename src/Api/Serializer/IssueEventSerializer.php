<?php

namespace App\Api\Serializer;

use App\Entity\Filter;
use App\Entity\IssueEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

readonly class IssueEventSerializer implements NormalizerInterface
{
    public function __construct(private NormalizerInterface $decoratedNormalizer, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [IssueEvent::class];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decoratedNormalizer->supportsNormalization($data, $format);
    }

    /**
     * @param IssueEvent $data
     */
    public function normalize($data, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $normalized = $this->decoratedNormalizer->normalize($data, $format, $context);

        if (null !== $data->getFile()) {
            $url = $this->urlGenerator->generate('issue_event_file', [
                'issueEvent' => $data->getId(),
                'issueEventFile' => $data->getFile()->getId(),
                'filename' => $data->getFile()->getFilename(),
            ]);

            $normalized['fileUrl'] = $url;
        }

        return $normalized;
    }
}
