<?php

namespace Loconox\EntityRoutingBundle\Event;


use Loconox\EntityRoutingBundle\Model\SlugInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SlugEvent extends Event
{

    /**
     * @var mixed
     */
    protected $entity;

    protected SlugInterface|null $slug;

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->slug = null;
    }

    /**
     * Get the entity
     *
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return SlugInterface|null
     */
    public function getSlug(): ?SlugInterface
    {
        return $this->slug;
    }

    /**
     * @param SlugInterface|null $slug
     */
    public function setSlug(?SlugInterface $slug): void
    {
        $this->slug = $slug;
    }
}
