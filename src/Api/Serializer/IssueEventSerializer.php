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

use App\Entity\IssueEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IssueEventSerializer implements NormalizerInterface
{
    private NormalizerInterface $decorated;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(NormalizerInterface $decoratedNormalizer, UrlGeneratorInterface $urlGenerator)
    {
        $this->decorated = $decoratedNormalizer;
        $this->urlGenerator = $urlGenerator;
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return $data instanceof IssueEvent
            && $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param IssueEvent $object
     */
    public function normalize($object, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (null !== $object->getFile()) {
            $url = $this->urlGenerator->generate('issue_event_file', [
                'issueEvent' => $object->getId(),
                'issueEventFile' => $object->getFile()->getId(),
                'filename' => $object->getFile()->getFilename(),
            ]);

            $data['fileUrl'] = $url;
        }

        return $data;
    }
}
