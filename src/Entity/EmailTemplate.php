<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Api\Provider\AuthenticatedCollectionProvider;
use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Enum\EmailTemplatePurpose;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    denormalizationContext: ['groups' => ['email-template:write']],
    normalizationContext: ['groups' => ['email-template:read', 'time:read'], "skip_null_values" => false],
)]
#[GetCollection(
    provider: AuthenticatedCollectionProvider::class,
    security: "is_granted('ROLE_ASSOCIATED_CONSTRUCTION_MANAGER')"
)]
#[Get(security: 'is_granted("EMAIL_TEMPLATE_VIEW", object)')]
#[Post(securityPostDenormalize: 'is_granted("EMAIL_TEMPLATE_MODIFY", object)', denormalizationContext: ['groups' => ['email-template:create', 'email-template:write']])]
#[Patch(security: 'is_granted("EMAIL_TEMPLATE_MODIFY", object)')]
#[Delete(security: 'is_granted("EMAIL_TEMPLATE_MODIFY", object)')]
#[ApiFilter(SearchFilter::class, properties: ['constructionSite'], strategy: SearchFilter::STRATEGY_EXACT)]
class EmailTemplate extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    #[Assert\NotBlank]
    #[Groups(['email-template:read', 'email-template:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $name;

    #[Assert\NotBlank]
    #[Groups(['email-template:read', 'email-template:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $subject;

    #[Assert\NotBlank]
    #[Groups(['email-template:read', 'email-template:write'])]
    #[ORM\Column(type: Types::TEXT)]
    private string $body;

    #[Groups(['email-template:read', 'email-template:write'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: EmailTemplatePurpose::class)]
    private ?EmailTemplatePurpose $purpose = null;

    #[Assert\NotNull]
    #[Groups(['email-template:read', 'email-template:write'])]
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $selfBcc;

    #[Assert\NotBlank]
    #[Groups(['email-template:read', 'email-template:create'])]
    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: ConstructionSite::class, inversedBy: 'emailTemplates')]
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

    public function getPurpose(): ?EmailTemplatePurpose
    {
        return $this->purpose;
    }

    public function setPurpose(?EmailTemplatePurpose $purpose): void
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

    public function getConstructionSite(): ?ConstructionSite
    {
        return $this->constructionSite;
    }

    public function setConstructionSite(ConstructionSite $constructionSite): void
    {
        $this->constructionSite = $constructionSite;
    }
}
