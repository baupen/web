<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filters\RequiredExactSearchFilter;
use App\Entity\Base\BaseEntity;
use App\Entity\Interfaces\ConstructionSiteOwnedEntityInterface;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An EmailTemplate is used to prepare the email to be sent to the specified receivers.
 *
 * @ApiResource(
 *     collectionOperations={
 *      "get",
 *      "post" = {"security_post_denormalize" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)", "denormalization_context"={"groups"={"email-template-create", "email-template-edit"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('EMAIL_TEMPLATE_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"email-template-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"email-template-edit"}},
 *     attributes={"pagination_enabled"=false}
 * )
 *
 * @ApiFilter(RequiredExactSearchFilter::class, properties={"constructionSite"})
 */
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class EmailTemplate extends BaseEntity implements ConstructionSiteOwnedEntityInterface
{
    use IdTrait;
    use TimeTrait;

    public const PURPOSE_OPEN_ISSUES = 1;
    public const PURPOSE_UNREAD_ISSUES = 2;
    public const PURPOSE_OVERDUE_ISSUES = 3;

    #[Assert\NotBlank]
    #[Groups(['email-template-read', 'email-template-edit'])]
    #[ORM\Column(type: 'text')]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(['email-template-read', 'email-template-edit'])]
    #[ORM\Column(type: 'text')]
    private string $subject;

    #[Assert\NotBlank]
    #[Groups(['email-template-read', 'email-template-edit'])]
    #[ORM\Column(type: 'text')]
    private string $body;

    #[Groups(['email-template-read', 'email-template-edit'])]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $purpose = null;

    #[Assert\NotNull]
    #[Groups(['email-template-read', 'email-template-edit'])]
    #[ORM\Column(type: 'boolean')]
    private bool $selfBcc;

    #[Assert\NotBlank]
    #[Groups(['email-template-create'])]
    #[ORM\ManyToOne(targetEntity: \ConstructionSite::class, inversedBy: 'emailTemplates')]
    private ?ConstructionSite $constructionSite = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
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

    public function getPurpose(): ?int
    {
        return $this->purpose;
    }

    public function setPurpose(?int $purpose): void
    {
        $this->purpose = $purpose;
    }

    public function getSelfBcc(): bool
    {
        return $this->selfBcc;
    }

    public function setSelfBcc(bool $selfBcc): void
    {
        $this->selfBcc = $selfBcc;
    }

    public function getConstructionSite(): ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }

    public function isConstructionSiteSet(): bool
    {
        return null !== $this->constructionSite;
    }
}
