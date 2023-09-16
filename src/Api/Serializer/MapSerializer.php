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

use App\Entity\Map;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MapSerializer implements NormalizerInterface
{
    private $decorated;
    private $urlGenerator;

    public function __construct(NormalizerInterface $decoratedNormalizer, UrlGeneratorInterface $urlGenerator)
    {
        $this->decorated = $decoratedNormalizer;
        $this->urlGenerator = $urlGenerator;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof Map
            && $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param Map $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if (null !== $object->getFile()) {
            $url = $this->urlGenerator->generate('map_file', [
                'map' => $object->getId(),
                'mapFile' => $object->getFile()->getId(),
                'filename' => $object->getFile()->getFilename(),
            ]);

            $data['fileUrl'] = $url;
        }

        return $data;
    }
}
