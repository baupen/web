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

use App\Entity\Craftsman;
use App\Security\TokenTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CraftsmanSerializer implements NormalizerInterface
{
    use TokenTrait;

    private $decorated;
    private $urlGenerator;
    private $tokenStorage;

    public function __construct(NormalizerInterface $decoratedNormalizer, UrlGeneratorInterface $urlGenerator, TokenStorageInterface $tokenStorage)
    {
        $this->decorated = $decoratedNormalizer;
        $this->urlGenerator = $urlGenerator;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, ?string $format = null)
    {
        return $data instanceof Craftsman
            && $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @param Craftsman $object
     */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if ($this->tryGetConstructionManager($this->tokenStorage->getToken())) {
            $url = $this->urlGenerator->generate('public_resolve', [
                'token' => $object->getAuthenticationToken(),
            ]);

            $data['resolveUrl'] = $url;
        }

        return $data;
    }
}
