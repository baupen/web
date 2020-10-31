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

use App\Entity\Issue;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

final class IssueDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'ISSUE_DENORMALIZER_ALREADY_CALLED';

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Issue;
    }

    /**
     * @param Issue $data
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        var_dump($data);
        $context['groups'][] = 'can_retrieve_book';

        $context[self::ALREADY_CALLED] = true;

        return $this->denormalizer->denormalize($object, $type, $format, $context);
    }
}
