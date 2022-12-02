<?php

namespace Loconox\EntityRoutingBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Loconox\EntityRoutingBundle\Model\Slug as BaseSlug;

/**
 * Class BaseSlug
 *
 */
#[UniqueEntity(fields: ["slug", "type"], ignoreNull: false)]
class Slug extends BaseSlug
{
    /**
     * @var int
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }


    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime);
    }
}
