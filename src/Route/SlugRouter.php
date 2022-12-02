<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loconox\EntityRoutingBundle\Route;

use Loconox\EntityRoutingBundle\Entity\SlugManager;
use Loconox\EntityRoutingBundle\Generator\UrlGenerator;
use Loconox\EntityRoutingBundle\Matcher\UrlMatcher;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Config\ContainerParametersResource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class SlugRouter implements RequestMatcherInterface, VersatileGeneratorInterface, RouterInterface
{

    /**
     * @var \Symfony\Component\Routing\RouteCollection|null
     */
    protected $collection = null;

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @var UrlGenerator
     */
    protected $generator;

    /**
     * @var SlugServiceManager
     */
    protected $slugServiceManager;

    /**
     * @var SlugManager
     */
    protected $slugManager;

    /**
     * @var UrlMatcher
     */
    protected $matcher;

    /**
     * @param SlugServiceManager $slugServiceManager
     * @param SlugManager $slugManager
     * @param string $resource
     * @param string $type
     * @param LoaderInterface $loader
     */
    public function __construct(
        SlugServiceManager $slugServiceManager,
        SlugManager        $slugManager,
                           $resource,
                           $type,
        LoaderInterface    $loader
    )
    {
        $this->slugServiceManager = $slugServiceManager;
        $this->slugManager = $slugManager;
        $this->resource = $resource;
        $this->type = $type;
        $this->loader = $loader;
        $this->cache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;

        if (null !== $this->generator) {
            $this->getGenerator()->setContext($context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContext(): RequestContext
    {
        return $this->context;
    }

    /**
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function getRouteCollection()
    {
        if (null === $this->collection) {
            $this->collection = $this->loader->load($this->resource, $this->type);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        if (is_object($name) && $name instanceof Route) {
            return true;
        }

        if (is_string($name)) {
            return $this->getRouteCollection()->get($name) !== null;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $this->getGenerator()->generate($name, $parameters, $referenceType);
    }

    public function getGenerator()
    {
        if (null !== $this->generator) {
            return $this->generator;
        }

        $this->generator = new UrlGenerator(
            $this->getRouteCollection(),
            $this->context,
            null,
            $this->slugServiceManager
        );

        return $this->generator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteDebugMessage(string $name, array $parameters = []): string
    {
        return "Route '$name' not found";
    }

    public function match(string $pathinfo): array
    {
        $match = $this->getMatcher()->match($pathinfo);

        if (!$match) {
            throw new ResourceNotFoundException($pathinfo);
        }

        return $this->handleRedirect($match);
    }

    public function matchRequest(Request $request): array
    {
        $matcher = $this->getMatcher();
        $match = $matcher->matchRequest($request);

        if (!$match) {
            throw new ResourceNotFoundException($request->getPathInfo());
        }

        return $this->handleRedirect($match);
    }

    protected function handleRedirect($match): array
    {
        if ($match['_controller'] === 'FrameworkBundle:Redirect:urlRedirect') {
            $routeName = $match['_route'];
            $controller = $match['_controller'];
            unset($match['_controller']);
            unset($match['_route']);
            $redirect = [
                '_controller' => $controller,
                '_route' => '',
                'path' => $this->getGenerator()->generate($routeName, $match),
                'params' => [],
                'permanent' => true,
            ];

            return $redirect;
        }

        return $match;
    }

    public function getMatcher()
    {
        if (null !== $this->matcher) {
            return $this->matcher;
        }

        $this->matcher = new UrlMatcher(
            $this->getRouteCollection(),
            $this->context,
            $this->slugServiceManager,
            $this->slugManager
        );

        return $this->matcher;
    }
}
