<?php

namespace Loconox\EntityRoutingBundle\Validator\Constraints;

use Loconox\EntityRoutingBundle\Exception\SlugServiceNotFoundException;
use Loconox\EntityRoutingBundle\Model\SlugManagerInterface;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueSlugValidator extends ConstraintValidator
{

    /**
     * @var SlugManagerInterface
     */
    protected $slugManager;

    /**
     * @var SlugServiceManagerInterface
     */
    protected $slugServiceManager;

    function __construct(SlugServiceManagerInterface $slugServiceManager, SlugManagerInterface $slugManager)
    {
        $this->slugManager = $slugManager;
        $this->slugServiceManager = $slugServiceManager;
    }

    public function validate($entity, Constraint $constraint)
    {
        $service = $this->slugServiceManager->get($entity);

        if (!$service) {
            throw new SlugServiceNotFoundException($entity);
        }

        $slug = $service->createSlug($entity, false);

        $slugs = $this->slugManager->findBySlug($slug);

        /* Si aucun slug ne match le slug ou qu'un seul slug et que ce slug est lié à la même
         * entity que celle passée en paramètre, c'est qu'il est unique.
         */
        if (empty($slugs) || (count($slugs) === 1) && $slug->getEntityId() == ($slugs instanceof \Iterator ? $slugs->current()->getEntityId() : current($slugs)->getEntityId())) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setInvalidValue($slug->getSlug())
            ->addViolation();
    }
}
