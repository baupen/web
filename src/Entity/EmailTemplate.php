<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Filters\RequiredSearchFilter;
use App\Entity\Base\BaseEntity;
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
 *      "post" = {"security_post_denormalize" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)", "denormalization_context"={"groups"={"email-template-create", "email-template-write"}}}
 *      },
 *     itemOperations={
 *      "get" = {"security" = "is_granted('EMAIL_TEMPLATE_VIEW', object)"},
 *      "patch" = {"security" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)"},
 *      "delete" = {"security" = "is_granted('EMAIL_TEMPLATE_MODIFY', object)"},
 *     },
 *     normalizationContext={"groups"={"email-template-read"}, "skip_null_values"=false},
 *     denormalizationContext={"groups"={"email-template-write"}},
 *     attributes={"pagination_enabled"=false}
 * )
 * @ApiFilter(RequiredSearchFilter::class, properties={"constructionSite"})
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EmailTemplate extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    public const TYPE_USER_DEFINED = 1;
    public const TYPE_OPEN_ISSUES = 2;
    public const TYPE_UNREAD_ISSUES = 3;
    public const TYPE_OVERDUE_ISSUES = 4;

    /**
     * @var string
     *
     * @Groups({"email-template-read", "email-template-edit"})
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @Groups({"email-template-read", "email-template-edit"})
     * @ORM\Column(type="string")
     */
    private $subject;

    /**
     * @var string
     *
     * @Groups({"email-template-read", "email-template-edit"})
     * @ORM\Column(type="string")
     */
    private $body;

    /**
     * @var int
     *
     * @Groups({"email-template-read", "email-template-edit"})
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @var bool
     *
     * @Groups({"email-template-read", "email-template-edit"})
     * @ORM\Column(type="boolean")
     */
    private $selfBcc;

    /**
     * @var ConstructionSite
     *
     * @Assert\NotBlank
     * @Groups({"email-template-create"})
     * @ORM\ManyToOne(targetEntity="ConstructionSite")
     */
    private $constructionSite;

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

    public function isSelfBcc(): bool
    {
        return $this->selfBcc;
    }

    public function setSelfBcc(bool $selfBcc): void
    {
        $this->selfBcc = $selfBcc;
    }
}
