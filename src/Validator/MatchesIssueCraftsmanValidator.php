<?php

namespace App\Validator;

use App\Entity\Issue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MatchesIssueCraftsmanValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof MatchesIssueCraftsman) {
            throw new UnexpectedTypeException($constraint, MatchesIssueCraftsman::class);
        }

        if (null === $value) {
            return;
        }

        /** @var ?Issue $object */
        $object = $this->context->getObject();
        if (!$object) {
            throw new \Exception("Attribute can only be applied to issue.");
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalEntityData = $unitOfWork->getOriginalEntityData($object);

        if (!\array_key_exists($this->context->getPropertyPath(), $originalEntityData)) {
            return;
        }

        if (null !== $originalEntityData[$this->context->getPropertyPath()]) {
            return;
        }

        if ($value === $object->getCraftsman()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ property }}', $this->context->getPropertyPath())
            ->addViolation();
    }
}
