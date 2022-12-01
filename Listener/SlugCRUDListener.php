<?php

namespace Loconox\EntityRoutingBundle\Listener;

use Loconox\EntityRoutingBundle\Event\SlugEvent;
use Loconox\EntityRoutingBundle\Events;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface;
use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlug;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SlugCRUDListener implements EventSubscriberInterface
{

    protected SlugServiceManagerInterface $serviceManager;

    protected EventDispatcherInterface $eventDispatcher;

    function __construct(SlugServiceManagerInterface $serviceManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->serviceManager = $serviceManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param SlugEvent $event
     * @throws \Exception
     */
    public function actionCreateSlug(SlugEvent $event)
    {
        $entity = $event->getEntity();

        $service = $this->serviceManager->get(get_class($entity));

        if (!$service) {
            throw new \Exception(sprintf('No service found for the class %s', get_class($entity)));
        }

        $violations = $service->validate($entity);
        if ($violations->count() > 0 && $violations->get(0)->getConstraint() instanceof UniqueSlug) {
            $this->eventDispatcher->dispatch($event, Events::UNIQUE_SLUG_VIOLATION);
        }

        if (!$event->getSlug()) {
            $service->createSlug($entity);
        }

    }

    /**
     * @param SlugEvent $event
     * @throws \Exception
     */
    public function actionUpdateSlug(SlugEvent $event)
    {
        $entity = $event->getEntity();

        $service = $this->serviceManager->get(get_class($entity));

        if (!$service) {
            throw new \Exception(sprintf('No service found for the class %s', get_class($entity)));
        }

        // Update the slug itself
        $newSlug = $service->updateSlug($entity);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::ACTION_CREATE_SLUG => 'actionCreateSlug',
            Events::ACTION_UPDATE_SLUG => 'actionUpdateSlug',
        ];
    }
}
