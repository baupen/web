<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class CloseToNowWhenPreviouslyNullValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CloseToNowWhenPreviouslyNull) {
            throw new UnexpectedTypeException($constraint, CloseToNowWhenPreviouslyNull::class);
        }

        if (null === $value) {
            return;
        }

        $object = $this->context->getObject();
        if (!\is_object($object)) {
            return;
        }

        $unitOfWork = $this->entityManager->getUnitOfWork();
        $originalEntityData = $unitOfWork->getOriginalEntityData($object);

        if (!\array_key_exists($this->context->getPropertyPath(), $originalEntityData)) {
            return;
        }

        if (null !== $originalEntityData[$this->context->getPropertyPath()]) {
            return;
        }

        $now = new \DateTimeImmutable();
        $difference = \abs($now->getTimestamp() - $value->getTimestamp());

        if ($difference <= $constraint->tolerance) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ property }}', $this->context->getPropertyPath())
            ->setParameter('{{ tolerance }}', (string)$constraint->tolerance)
            ->addViolation();
    }
}
