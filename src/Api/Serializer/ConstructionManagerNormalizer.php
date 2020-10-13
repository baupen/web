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

use App\Entity\ConstructionManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ConstructionManagerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CONSTRUCTION_MANAGER_NORMALIZER_ALREADY_CALLED';

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ConstructionManager;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        if ($this->userHasPermissionsForConstructionManager($object)) {
            $context['groups'][] = 'self:write';
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->normalizer->normalize($object, $format, $context);
    }

    private function userHasPermissionsForConstructionManager(ConstructionManager $object): bool
    {
        $constructionManager = $this->tokenStorage->getToken()->getUser();

        return $constructionManager instanceof ConstructionManager && $object === $constructionManager;
    }
}
