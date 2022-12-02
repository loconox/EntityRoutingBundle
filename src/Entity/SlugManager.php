<?php

namespace Loconox\EntityRoutingBundle\Entity;

use Loconox\EntityRoutingBundle\Model\SlugInterface;
use Loconox\EntityRoutingBundle\Model\SlugManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class SlugManager extends EntityRepository implements SlugManagerInterface
{
    public function __construct(EntityManagerInterface $em, $class)
    {
        $this->_em         = $em;
        $this->_class      = $em->getClassMetadata($class);
        $this->_entityName = $class;
    }

    public function findLastBy(array $criteria)
    {
        $criteria = array_merge(
            $criteria,
            [
                'new' => null,
            ]
        );
        $slug     = $this->findOneBy($criteria);

        return $slug;
    }

    /**
     * Save a slug into db
     *
     * @param SlugInterface $slug
     */
    public function doSave(SlugInterface $slug)
    {
        $this->_em->persist($slug);
        $this->_em->flush();
    }

    public function findBySlug(SlugInterface $slug)
    {
        $qb = $this->createQueryBuilder('s');

        $parameters = [
            'slug' => $slug->getSlug(),
            'type' => $slug->getType(),
        ];
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->eq('s.slug', ':slug'),
                $qb->expr()->eq('s.type', ':type')
            )
        );
        $qb->setParameters($parameters);

        return $qb->getQuery()->execute();
    }

    /**
     * Create an empty slug
     *
     * @return SlugInterface
     */
    public function create()
    {
        $slug = new $this->_entityName();

        return $slug;
    }

    /**
     * @param string $type
     * @param string|null $value
     *
     * @return SlugInterface
     */
    public function createSlug($type, $value = null)
    {
        $slug = $this->create();

        $slug->setType($type);
        $slug->setSlug($value);

        return $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SlugInterface $slug)
    {
        $this->doSave($slug);
    }

    /**
     * Get slugs matching the slug based on the regex ^slug.*
     *
     * @param SlugInterface $slug
     *
     * @return mixed
     */
    public function findSlugLike(SlugInterface $slug)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->like('s.slug', ':slug'),
                $qb->expr()->eq('s.type', ':type')
            )
        );
        $qb->setParameter('type', $slug->getType());
        $qb->setParameter('slug', sprintf('%%%s%%', $slug->getSlug()));

        return $qb->getQuery()->execute();
    }
}