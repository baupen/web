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

use App\Entity\Issue;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IssueSerializer implements NormalizerInterface
{
    private $decorated;
    private $urlGenerator;

    public function __construct(NormalizerInterface $decoratedNormalizer, UrlGeneratorInterface $urlGenerator)
    {
        $this->decorated = $decoratedNormalizer;
        $this->urlGenerator = $urlGenerator;
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof Issue
            && $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param Issue $object
     */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (null !== $object->getImage()) {
            $url = $this->urlGenerator->generate('issue_image', [
                'issue' => $object->getId(),
                'issueImage' => $object->getImage()->getId(),
                'filename' => $object->getImage()->getFilename(),
            ]);

            $data['imageUrl'] = $url;
        }

        if ($object->hasPosition()) {
            $url = $this->urlGenerator->generate('issue_map_render', [
                'issue' => $object->getId(),
            ]);

            $data['mapRenderUrl'] = $url;
        }

        return $data;
    }
}
