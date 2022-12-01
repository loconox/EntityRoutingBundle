<?php

namespace Loconox\EntityRoutingBundle\Tests\Matcher;

use Loconox\EntityRoutingBundle\Entity\Slug;
use Loconox\EntityRoutingBundle\Entity\SlugManager;
use Loconox\EntityRoutingBundle\Matcher\UrlMatcher;
use Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class UrlMatcherTest extends TestCase
{

    public function testMatch()
    {
        $type = 'bar';
        $slugValue = 'toto';
        $routeName = 'foo';
        $route = new Route('/{' . $type . '}');
        $slug = new Slug();
        $entity = new \stdClass();
        $expected = [
            '_route' => $routeName,
            $type => $entity,
        ];

        $routeCollection = new RouteCollection();
        $routeCollection->add($routeName, $route);

        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();

        $slugManager = $this->getMockBuilder(SlugManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slugManager
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->equalTo(['type' => $type, 'slug' => $slugValue]))
            ->willReturn($slug);

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slugService
            ->expects($this->atLeastOnce())
            ->method('getEntity')
            ->with($this->equalTo($slug))
            ->willReturn($entity);
        $slugService
            ->expects($this->atLeastOnce())
            ->method('getAlias')
            ->willReturn($type);

        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();
        $slugServiceManager
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [$type, $slugService],
                ['_route', false],
            ]);

        $matcher = new UrlMatcher($routeCollection, $context, $slugServiceManager, $slugManager);

        $this->assertEquals($expected, $matcher->match('/' . $slugValue));
    }

    public function testMatchSameRegex()
    {
        $type1 = 'foo';
        $routeName1 = 'foo';
        $route1 = new Route('/{' . $type1 . '}');

        $type2 = 'bar';
        $routeName2 = 'bar';
        $route2 = new Route('/{' . $type2 . '}');

        $slug = new Slug();
        $slugValue = 'toto';
        $entity = new \stdClass();
        $expected = [
            '_route' => $routeName2,
            $type2 => $entity,
        ];

        $routeCollection = new RouteCollection();
        $routeCollection->add($routeName1, $route1);
        $routeCollection->add($routeName2, $route2);

        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();

        $slugManager = $this->getMockBuilder(SlugManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slugManager
            ->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->willReturnCallback(function ($params) use ($type2, $slugValue, $slug) {
                $type = $params['type'];
                $value = $params['slug'];
                if ($type == $type2 && $value == $slugValue) {
                    return $slug;
                }

                return null;
            });

        $slugService1 = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slugService1
            ->expects($this->never())
            ->method('getEntity');
        $slugService1
            ->expects($this->atLeastOnce())
            ->method('getAlias')
            ->willReturn($type1);

        $slugService2 = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slugService2
            ->expects($this->atLeastOnce())
            ->method('getEntity')
            ->with($this->equalTo($slug))
            ->willReturn($entity);
        $slugService2
            ->expects($this->atLeastOnce())
            ->method('getAlias')
            ->willReturn($type2);

        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();
        $slugServiceManager
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [$type1, $slugService1],
                [$type2, $slugService2],
                ['_route', false],
            ]);

        $matcher = new UrlMatcher($routeCollection, $context, $slugServiceManager, $slugManager);

        $this->assertEquals($expected, $matcher->match('/' . $slugValue));
    }

    public function testMatchRedirect()
    {
        $type = 'foo';
        $routeName = 'foo';
        $route = new Route('/{' . $type . '}');

        $newSlug = new Slug();
        $slug = new Slug();
        $slug->setNew($newSlug);
        $slugValue = 'toto';
        $entity = new \stdClass();
        $expected = [
            '_controller' => 'FrameworkBundle:Redirect:urlRedirect',
            '_route' => $routeName,
            $type => $entity,
        ];

        $routeCollection = new RouteCollection();
        $routeCollection->add($routeName, $route);

        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();

        $slugManager = $this->getMockBuilder(SlugManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slugManager->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with($this->equalTo(['type' => $type, 'slug' => $slugValue]))
            ->willReturn($slug);

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slugService->expects($this->atLeastOnce())
            ->method('getEntity')
            ->with($this->equalTo($slug))
            ->willReturn($entity);
        $slugService
            ->expects($this->atLeastOnce())
            ->method('getAlias')
            ->willReturn($type);

        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();
        $slugServiceManager->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(function ($t) use ($slugService, $type) {
                if ($type == $t) {
                    return $slugService;
                }

                return false;
            });

        $matcher = new UrlMatcher($routeCollection, $context, $slugServiceManager, $slugManager);

        $this->assertEquals($expected, $matcher->match('/' . $slugValue));
    }

    public function testMatchSimpleRoute()
    {
        $routeName = 'foo';
        $route = new Route('/foo');
        $expected = [
            '_route' => $routeName,
        ];

        $routeCollection = new RouteCollection();
        $routeCollection->add($routeName, $route);

        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();

        $slugManager = $this->getMockBuilder(SlugManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $slugManager->expects($this->never())
            ->method('findOneBy');

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slugService->expects($this->never())
            ->method('getEntity');

        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();
        $slugServiceManager->expects($this->any())
            ->method('get')
            ->with($this->anything())
            ->willReturn(false);

        $matcher = new UrlMatcher($routeCollection, $context, $slugServiceManager, $slugManager);

        $this->assertEquals($expected, $matcher->match('/foo'));
    }
}
