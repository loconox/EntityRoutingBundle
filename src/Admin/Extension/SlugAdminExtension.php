<?php

namespace Loconox\EntityRoutingBundle\Admin\Extension;

use Loconox\EntityRoutingBundle\Event\SlugEvent;
use Loconox\EntityRoutingBundle\Events;
use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlug;
use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Symfony\Component\EventDispatcher\EventDispatcher;

class SlugAdminExtension extends AdminExtension
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     */
    public function postPersist(AdminInterface $admin, $entity)
    {
        $event = new SlugEvent($entity);
        $this->dispatcher->dispatch($event, Events::ACTION_CREATE_SLUG);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(AdminInterface $admin, $entity)
    {
        $event = new SlugEvent($entity);
        $this->dispatcher->dispatch($event, Events::ACTION_UPDATE_SLUG, );
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param EventDispatcher $dispatcher
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function validate(AdminInterface $admin, ErrorElement $errorElement, $object)
    {
        // Valide que le slug est unique
        $errors = $admin->getValidator()->validate($object, new UniqueSlug());

        if (count($errors) == 0) {
            return;
        }

        $event = new SlugEvent($object);
        $this->dispatcher->dispatch(Events::UNIQUE_SLUG_VIOLATION, $event);

        return;
    }
}
