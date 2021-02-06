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

use App\Entity\Craftsman;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CraftsmanSerializer implements NormalizerInterface
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
        return $data instanceof Craftsman &&
            $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param Craftsman $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        $url = $this->urlGenerator->generate('public_resolve', [
            'token' => $object->getAuthenticationToken(),
        ]);

        $data['resolveUrl'] = $url;

        return $data;
    }
}
