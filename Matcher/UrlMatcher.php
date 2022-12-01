<?php

namespace Loconox\EntityRoutingBundle\Matcher;

use Loconox\EntityRoutingBundle\Entity\SlugManager;
use Loconox\EntityRoutingBundle\Model\Slug;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher as BaseUrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class UrlMatcher extends BaseUrlMatcher
{
    /**
     * @var SlugManager
     */
    protected $slugManager;

    /**
     * @var SlugServiceManager
     */
    protected $slugServiceManager;

    /**
     * Constructor.
     *
     * @param RouteCollection $routes A RouteCollection instance
     * @param RequestContext $context The context
     * @param SlugServiceManager $slugServiceManager
     * @param SlugManager $slugManager
     */
    public function __construct(
        RouteCollection $routes,
        RequestContext $context,
        SlugServiceManager $slugServiceManager,
        SlugManager $slugManager
    ) {
        $this->routes             = $routes;
        $this->context            = $context;
        $this->slugServiceManager = $slugServiceManager;
        $this->slugManager        = $slugManager;
    }

    /**
     * Tries to match a URL with a set of routes.
     *
     * @param string $pathinfo The path info to be parsed
     * @param RouteCollection $routes The set of routes
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     */
    protected function matchCollection(string $pathinfo, RouteCollection $routes): array
    {
        foreach ($routes as $name => $route) {
            $compiledRoute = $route->compile();

            // check the static prefix of the URL first. Only use the more expensive preg_match when it matches
            if ('' !== $compiledRoute->getStaticPrefix() && 0 !== strpos(
                    $pathinfo,
                    $compiledRoute->getStaticPrefix()
                )
            ) {
                continue;
            }

            if ( ! preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
                continue;
            }

            $hostMatches = array();
            if ($compiledRoute->getHostRegex() && ! preg_match(
                    $compiledRoute->getHostRegex(),
                    $this->context->getHost(),
                    $hostMatches
                )
            ) {
                continue;
            }

            // check HTTP method requirement
            if ($requiredMethods = $route->getMethods()) {
                // HEAD and GET are equivalent as per RFC
                if ('HEAD' === $method = $this->context->getMethod()) {
                    $method = 'GET';
                }

                if ( ! in_array($method, $requiredMethods)) {
                    $this->allow = array_merge($this->allow, $requiredMethods);

                    continue;
                }
            }

            $status = $this->handleRouteRequirements($pathinfo, $name, $route);

            if (self::ROUTE_MATCH === $status[0]) {
                return $status[1];
            }

            if (self::REQUIREMENT_MISMATCH === $status[0]) {
                continue;
            }

            $attributes = $this->getAttributes($route, $name, array_replace($matches, $hostMatches));

            $mismatch = false;
            foreach ($attributes as $attr => &$value) {
                $slugService = $this->getSlugService($attr, $route);
                if ( ! $slugService) {
                    continue;
                }
                $type = $slugService->getAlias();

                /** @var Slug $slug */
                $slug = $this->slugManager->findOneBy(
                    [
                        'type' => $type,
                        'slug' => $value,
                    ]
                );

                // Slug not found, maybe it's another route with the same regex
                if ( ! $slug) {
                    $mismatch = true;
                    break;
                }

                // Slug found be there is a new one, do redirect
                if ($slug->getNew() !== null) {
                    $attributes['_controller'] = 'FrameworkBundle:Redirect:urlRedirect';
                }
                $value = $slugService->getEntity($slug);

            }

            if ($mismatch) {
                continue;
            }

            return $attributes;
        }

        return [];
    }

    private function getSlugService($attr, $route)
    {
        if (false !== $slugService = $this->slugServiceManager->get($attr)) {
            return $slugService;
        }

        $types = $route->getOption('types');
        if (isset($types[$attr]) && (false !== $slugService = $this->slugServiceManager->get($types[$attr]))) {
            return $slugService;
        }

        return null;
    }
}
