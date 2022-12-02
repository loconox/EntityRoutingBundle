<?php

namespace Loconox\EntityRoutingBundle\Twig;


use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SlugExtension extends AbstractExtension
{
    protected SlugServiceManager $slugServiceManager;

    function __construct(SlugServiceManager $slugServiceManager)
    {
        $this->slugServiceManager = $slugServiceManager;
    }


    public function getSlug($params, ...$otherParams)
    {
        if ($otherParams) {
            $params = array_merge([$params], $otherParams);
        }

        $service = $this->slugServiceManager->get($params);
        if (!$service) {
            throw new \RuntimeException(sprintf('Enable to find slug service of type %s', implode(',', array_map( 'get_class', $params))));
        }

        $slug = $service->findSlug($params, true);
        if (!$slug) {
            throw new NotFoundHttpException('Pas trouvé');
        }
        // It should be useless
        while (null != $slug->getNew()) {
            $slug = $slug->getNew();
        }

        return $slug->getSlug();
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('slug', array($this, 'getSlug')),
        ];
    }
}
