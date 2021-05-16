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

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param ConstructionManager $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;

        $constructionManager = $this->tryGetConstructionManager($this->tokenStorage->getToken());
        $craftsman = $this->tryGetCraftsman($this->tokenStorage->getToken());
        if ($constructionManager || $craftsman === $object) {
            $context['groups'][] = 'craftsman-read-self';
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Craftsman;
    }
}
