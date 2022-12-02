<?php

namespace Loconox\EntityRoutingBundle\Model;

interface SlugInterface
{

    const TYPE_REDIRECT = 'redirect';

    const ROUTE_NAME = 'slug';

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId();

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug();

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * Set the entity id
     *
     * @param mixed $id
     */
    public function setEntityId($id);

    /**
     * Get entity id
     *
     * @return mixed
     */
    public function getEntityId();

    /**
     * Get Slug creation datetime
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Set Slug creation datetime
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Get new slug
     *
     * @return SlugInterface|null
     */
    public function getNew();

    /**
     * Set new slug
     *
     * @param SlugInterface $new
     */
    public function setNew($new);

    /**
     * @return SlugInterface
     */
    public function getNewer();

    /**
     * Get old Slug
     *
     * @return SlugInterface|null
     */
    public function getOld();

    /**
     * Set old Slug
     *
     * @param SlugInterface $old
     */
    public function setOld($old);

    /**
     *
     * @return null|array
     */
    public function getOlds();

    /**
     * @return SlugInterface
     */
    public function getOlder();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return boolean
     */
    public function isRedirection();
}