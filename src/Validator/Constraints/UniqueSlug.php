<?php

namespace Loconox\EntityRoutingBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 *
 * @Annotation
 * @Target({"CLASS", "ANNOTATION"})
 */
class UniqueSlug extends Constraint
{
    public $message = 'Slug already exists';
    public $service = 'loconox_entity_routing.validator.unique_slug';
    public $errorPath = null;

    public function validatedBy()
    {
        return $this->service;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
