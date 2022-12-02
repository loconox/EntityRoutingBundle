<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loconox\EntityRoutingBundle\Slug;

use Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface;

/**
 * Interface to manage page services.
 */
interface SlugServiceManagerInterface
{

    /**
     * Add a service mapper
     *
     * @param SlugServiceInterface $service
     * @param string $id
     * @param string $alias
     */
    public function add(SlugServiceInterface $service, $id, $alias);

    /**
     * Get the service by class or service name
     *
     * @param mixed $name
     * @return SlugServiceInterface|false
     */
    public function get($name);

    /**
     * Returns all services
     *
     * @return SlugServiceInterface[]
     */
    public function getAll();

    /**
     * Get tag
     *
     * @return string
     */
    public function getTag();

    /**
     * Set tag
     *
     * @param string $tag
     */
    public function setTag($tag);
}
