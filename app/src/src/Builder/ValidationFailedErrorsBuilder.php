<?php

namespace App\Builder;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailedErrorsBuilder
{
    public function build(ConstraintViolationListInterface $list): array
    {
        $errors = [];
        foreach ($list as $violation) {
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $errors;
    }
}
