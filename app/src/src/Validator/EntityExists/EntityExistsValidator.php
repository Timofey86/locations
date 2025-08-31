<?php

namespace App\Validator\EntityExists;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EntityExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        /* @var EntityExists $constraint */

        if (!$constraint instanceof EntityExists) {
            throw new \LogicException('Wrong constraint');
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (empty($constraint->entity)) {
            throw new \LogicException('Missed entity option');
        }

        $criteria = [
            $constraint->property => $value,
        ];
        if (is_array($constraint->payload)) {
            $validatingObject = $this->context->getRoot();
            foreach ($constraint->payload as $column => $attribute) {
                $criteria[$column] = $validatingObject->{$attribute};
            }
        }

        $data = $this->entityManager->getRepository($constraint->entity)->findOneBy($criteria);

        if (null === $data) {
            $this->context->buildViolation(new TranslatableMessage($constraint->message, [], 'validators'))
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
