<?php

namespace App\Api\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Api\Processor\CraftsmanEmailProcessor;
use App\Entity\ConstructionSite;
use App\Entity\Craftsman;
use App\Enum\EmailType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    processor: CraftsmanEmailProcessor::class,
    denormalizationContext: ['groups' => ['email:create']],
)]
#[Post]
class CraftsmanEmail
{
    #[ApiProperty(identifier: true)]
    private $noneIdentifier;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private ConstructionSite $constructionSite;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private Craftsman $receiver;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private string $subject;

    #[Assert\NotBlank]
    #[Groups(['email-create'])]
    private string $body;

    #[Assert\NotNull]
    #[Groups(['email-create'])]
    private bool $selfBcc;

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function getReceiver(): Craftsman
    {
        return $this->receiver;
    }

    public function setReceiver(Craftsman $receiver): void
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
}
