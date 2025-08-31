<?php

namespace App\Validator\EntityExists;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class EntityExists extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'entity.not.exist';

    public function __construct(
        public readonly string $entity,
        public readonly string $property,
        ?array $groups = null,
        $payload = null,
        array $options = [],
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
