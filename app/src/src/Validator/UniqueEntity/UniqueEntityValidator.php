<?php

namespace App\Validator\UniqueEntity;

use App\Validator\EntityExists\EntityExists;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        /* @var EntityExists $constraint */

        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $criteria = [
            $constraint->property => $value,
        ];

        $validatingObject = $this->context->getObject();

        $targetField = $constraint->targetField ?? 'id';
        $targetValue = null;

        if (method_exists($validatingObject, 'get' . ucfirst($targetField))) {
            $targetValue = $validatingObject->{'get' . ucfirst($targetField)}();
        } elseif (property_exists($validatingObject, $targetField)) {
            $targetValue = $validatingObject->$targetField;
        }

        $filter = $this->entityManager->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity($constraint->entity);
        $data = $this->entityManager->getRepository($constraint->entity)->findOneBy($criteria);
        $filter->enableForEntity($constraint->entity);

        if ($data) {
            if (method_exists($data, 'getDeletedAt') && $data->getDeletedAt() !== null) {
                $this->context->buildViolation(new TranslatableMessage($constraint->message, [], 'validators'))
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            } elseif ($data->{'get' . ucfirst($targetField)}() !== $targetValue) {
                $this->context->buildViolation(new TranslatableMessage($constraint->message, [], 'validators'))
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            }
        }
    }
}
