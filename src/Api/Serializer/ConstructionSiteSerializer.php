<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Serializer;

use App\Entity\ConstructionSite;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConstructionSiteSerializer implements NormalizerInterface
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
        return $data instanceof ConstructionSite &&
            $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if ($object instanceof ConstructionSite && null !== $object->getImage()) {
            $url = $this->urlGenerator->generate('construction_site_image', [
                'constructionSite' => $object->getId(),
                'constructionSiteImage' => $object->getImage()->getId(),
            ]);

            $data['imageUrl'] = $url;
        }

        return $data;
    }
}
