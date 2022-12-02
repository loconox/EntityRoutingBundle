<?php

namespace Loconox\EntityRoutingBundle\Listener;

use Loconox\EntityRoutingBundle\Event\SlugEvent;
use Loconox\EntityRoutingBundle\Exception\SlugServiceNotFoundException;
use Loconox\EntityRoutingBundle\Model\SlugManagerInterface;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface;
use Loconox\EntityRoutingBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UniqueSlugViolationListener implements EventSubscriberInterface
{
    /**
     * @var SlugManagerInterface
     */
    protected $slugManager;

    /**
     * @var SlugServiceManagerInterface
     */
    protected $slugServiceManager;

    function __construct($slugManager, $slugServiceManager)
    {
        $this->slugManager = $slugManager;
        $this->slugServiceManager = $slugServiceManager;
    }

    /**
     * @param SlugEvent $event
     * @return void
     */
    public function uniqueSlugViolation(SlugEvent $event): void
    {
        $entity = $event->getEntity();

        $service = $this->slugServiceManager->get($entity);

        if (!$service) {
            throw new SlugServiceNotFoundException($entity);
        }

        $slugViolation = $service->createSlug($entity, false);

        $slugs = $this->slugManager->findSlugLike($slugViolation);

        // add an integer at the end of the slug
        $i = 1;
        foreach($slugs as $slug) {
            // if it's the slug already present in bdd, continue
            if ($slug->getEntityId() == $slugViolation->getEntityId()) {
                continue;
            }
            preg_match('/'.$slugViolation->getSlug().'-([0-9]+)$/', $slug->getSlug(), $matches);
            if (!empty($matches)) {
                $j = intval($matches[1]);
                $i = $j >= $i ? $j + 1 : $i;
            }
        }
        $slugViolation->setSlug($slugViolation->getSlug().'-'.$i);
        $service->setEntitySlug($slugViolation, $entity);

        $service->saveSlug($slugViolation);

        $event->setSlug($slugViolation);
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::UNIQUE_SLUG_VIOLATION => 'uniqueSlugViolation',
        ];
    }
}
