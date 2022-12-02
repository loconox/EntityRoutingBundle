<?php

namespace Loconox\EntityRoutingBundle\Slug;

use Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface;

class SlugServiceManager implements SlugServiceManagerInterface
{
    /**
     * @var array
     */
    protected $services = [];

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * {@inheritdoc}
     */
    public function add(SlugServiceInterface $service, $id, $alias)
    {
        $service->setName($id);
        $service->setAlias($alias);
        $keys = [$service->getClass(), $id, $alias];
        $this->services[] = [
            'key' => $keys,
            'service' => $service
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        foreach ($this->services as $node) {
            $keys = $node['key'];
            $service = $node['service'];
            foreach ($keys as $key) {
                if ($this->matchKey($name, $key)) {
                    return $service;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $result = [];
        foreach ($this->services as $node) {
            $result[] = $node['service'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * {@inheritdoc}
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    protected function matchKey($name, $key)
    {
        if ($name === $key) {
            return true;
        }
        if (is_object($name) && !is_array($key) && class_exists($key) && $name instanceof $key) {
            return true;
        }
        if (is_array($key) && is_array($name)) {
            foreach ($name as $index => $object) {
                if (!is_object($object) || !isset($key[$index])) {
                    return false;
                }
                $class = $key[$index];
                if (!$object instanceof $class) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}