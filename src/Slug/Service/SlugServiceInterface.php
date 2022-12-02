<?php

namespace Loconox\EntityRoutingBundle\Slug\Service;

use Loconox\EntityRoutingBundle\Model\SlugInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface SlugServiceInterface
{
    /**
     * Get the classe name
     *
     * @return array|string
     */
    public function getClass(): array|string;

    /**
     * Get the service name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set service name
     *
     * @param string $name
     */
    public function setName(string $name);

    /**
     * Get the service alias
     *
     * @return string
     */
    public function getAlias(): string;

    /**
     * Set the service alias
     *
     * @param string $alias
     */
    public function setAlias(string $alias);

    /**
     * Get the entity linked to the slug
     *
     * @param SlugInterface $slug
     * @return mixed
     */
    public function getEntity(SlugInterface $slug);

    /**
     * Get the slug linked to the entity
     *
     * @param $entity
     * @param bool $create
     * @param bool $optional
     * @return SlugInterface|null
     */
    public function findSlug($entity, $create = false, $optional = false): ?SlugInterface;

    /**
     * Create a Slug linked to the entity
     *
     * @param mixed $entity
     * @param bool $save save the slug
     * @return SlugInterface
     */
    public function createSlug($entity, bool $save = true): SlugInterface;

    /**
     * Update the link with the linked entity
     *
     * @param mixed $entity
     * @return SlugInterface the updated Slug
     */
    public function updateSlug($entity): SlugInterface;

    /**
     * Create a new slug linked to the updated entity
     *
     * @param $entity
     * @param SlugInterface $oldSlug
     * @return SlugInterface the new slug
     */
    public function incrementSlug($entity, SlugInterface $oldSlug): SlugInterface;

    /**
     * Set the slug of the entity
     *
     * @param $entity
     * @param SlugInterface $slug
     */
    public function setEntitySlug(SlugInterface $slug, $entity);

    /**
     * Get the slug linked to the entity
     *
     * @param mixed $entity
     * @return string
     */
    public function getEntitySlug($entity): string;

    /**
     * Initialize slug values
     *
     * @param SlugInterface $slug
     * @param $entity
     * @return mixed
     */
    public function setValues(SlugInterface $slug, $entity);

    /**
     * Returns true if the entity has changed, false otherwise
     *
     * @param $entity
     * @return bool
     */
    public function hasChanged($entity): bool;

    /**
     * Returns the entity id
     *
     * @param $entity
     * @return mixed
     */
    public function getEntityId($entity);

    /**
     * Validate slug before saving it.
     *
     * @param SlugInterface $slug
     *
     * @return ConstraintViolationListInterface A list of constraint violations
     *                                          If the list is empty, validation
     *                                          succeeded
     */
    public function validate($value): ?ConstraintViolationListInterface;

    /**
     * Save a slug into db
     *
     * @param SlugInterface $slug
     * @return void
     */
    public function saveSlug(SlugInterface $slug): void;
}
