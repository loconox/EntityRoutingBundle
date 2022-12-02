<?php

namespace Loconox\EntityRoutingBundle\Model;


/**
 * Class Slug
 *
 */
abstract class Slug implements SlugInterface
{
    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var SlugInterface
     */
    protected $new;

    /**
     * @var SlugInterface
     */
    protected $old;

    /**
     * @var mixed
     */
    protected $entityId;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return SlugInterface
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @param SlugInterface $new
     */
    public function setNew($new)
    {
        $this->new = $new;
    }

    public function getNewer()
    {
        $slug = $this;
        while ($slug->getNew()) {
            $slug = $slug->getNew();
        }

        return $slug;
    }

    /**
     * @return SlugInterface
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * @return SlugInterface
     */
    public function getOlds()
    {
        if ($this->getOld() != null) {
            return array_merge([$this->getOld()], $this->getOld()->getOlds());
        }

        return [];
    }

    public function getOlder()
    {
        $slug = $this;
        while ($slug->getOld()) {
            $slug = $slug->getOld();
        }

        return $slug;
    }

    /**
     * @param SlugInterface $old
     */
    public function setOld($old)
    {
        $this->old = $old;
    }

    public function isRedirection()
    {
        return $this->getNew() != null;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
