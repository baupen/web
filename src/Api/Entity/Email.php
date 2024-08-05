<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Api\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     collectionOperations={
 *      "post" = {"denormalization_context"={"groups"={"email-create"}}}
 *      },
 *     itemOperations={
 *      "none": {"method": "GET", "controller": NonExistingController::class }
 *     }
 * )
 */
class Email
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $noneIdentifier;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private string $receiver;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private string $subject;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private string $body;

    #[Assert\NotNull]
    #[Groups(['email-create'])]
    private bool $selfBcc;

    #[Assert\NotNull]
    #[Groups(['email-create'])]
    private int $type;

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function setReceiver(string $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getSelfBcc(): bool
    {
        return $this->selfBcc;
    }

    public function setSelfBcc(bool $selfBcc): void
    {
        $this->selfBcc = $selfBcc;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }
}
