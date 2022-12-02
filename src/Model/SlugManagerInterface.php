<?php

namespace Loconox\EntityRoutingBundle\Model;

use Doctrine\ORM\QueryBuilder;

interface SlugManagerInterface
{
    /**
     * Get Slug form string slug
     *
     * @param string $slugStr
     * @param SlugInterface[] $parents
     * @return SlugInterface|false
     */
    public function find($slugStr, $parents);

    /**
     * Get Slug by criteria
     *
     * @param array $criteria
     * @return SlugInterface|false
     */
    public function findBy(array $criteria);

    /**
     * Get the last Slug by criteria
     *
     * @param array $criteria
     * @return SlugInterface|false
     */
    public function findLastBy(array $criteria);

    /**
     * Create an empty slug
     *
     * @return SlugInterface
     */
    public function create();

    /**
     * Save a slug
     *
     * @param SlugInterface $slug
     */
    public function save(SlugInterface $slug);

    /**
     * Returns Slugs matching the given Slug
     *
     * @param SlugInterface $slug
     * @return SlugInterface[]|false
     */
    public function findBySlug(SlugInterface $slug);

    /**
     * Get slugs matching the slug based on the regex ^slug.*
     *
     * @param SlugInterface $slug
     * @return mixed
     */
    public function findSlugLike(SlugInterface $slug);

    /**
     * @param string $alias
     * @param string $indexBy
     * @return QueryBuilder
     */
    public function createQueryBuilder($alias, $indexBy = null);
}