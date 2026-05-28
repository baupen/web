<?php

namespace App\Api\Serializer;

use App\Entity\ConstructionManager;
use App\Entity\Craftsman;
use App\Security\TokenTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class CraftsmanNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use TokenTrait;

    private const ALREADY_CALLED = 'CRAFTSMAN_NORMALIZER_ALREADY_CALLED';

    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Craftsman;
    }

    /**
     * @param ConstructionManager $object
     */
    public function normalize($object, $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $context[self::ALREADY_CALLED] = true;

        $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
        $craftsman = $this->tryGetCraftsman($this->tokenStorage->getToken());
        if ($constructionManager || $craftsman === $object) {
            $context['groups'][] = 'craftsman-read-self';
        }

        return $this->normalizer->normalize($object, $format, $context);
    }
}
