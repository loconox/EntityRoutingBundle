<?php

namespace Loconox\EntityRoutingBundle\Tests\Generator;

use Loconox\EntityRoutingBundle\Entity\Slug;
use Loconox\EntityRoutingBundle\Generator\UrlGenerator;
use Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class UrlGeneratorTest extends TestCase
{
    /**
     * @param $name
     * @param $parameters
     * @param $routes
     * @param $expected
     *
     * @dataProvider routeProviders
     */
    public function testGenerate($name, $parameters, $routes, $slugData, $expected)
    {
        $routeCollection = new RouteCollection();
        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();

        $context->expects($this->any())
            ->method('getHost')
            ->willReturn('');

        $context->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);

        // Init slugs
        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();

        $slugServices = [];
        foreach ($slugData as $type => $slugs) {
            // Slug service
            $slugService = $this->getMockBuilder(SlugServiceInterface::class)
                ->getMock();

            $slugServices[] = [$type, $slugService];
            foreach ($slugs as $id => $slugValue) {
                $slug = new Slug();
                $slug->setType($type);
                $slug->setSlug($slugValue);
                $slugService->expects($this->any())
                    ->method('findSlug')
                    ->with($this->equalTo($id))
                    ->willReturn($slug);
                $slugService->expects($this->any())
                    ->method('validate')
                    ->with($this->equalTo($id))
                    ->willReturn(new ConstraintViolationList());
            }
        }
        $slugServiceManager->expects($this->any())
            ->method('get')
            ->willReturnMap($slugServices);

        $generator = new UrlGenerator($routeCollection, $context, null, $slugServiceManager);

        // Init route collection
        foreach ($routes as $routeName => $route) {
            $routeCollection->add($routeName, $route);
        }

        $this->assertEquals($expected, $generator->generate($name, $parameters));
    }

    public function routeProviders()
    {
        return [
            [
                'foo',
                [],
                [
                    'foo' => new Route('/foo'),
                    'bar' => new Route('/bar'),
                ],
                [],
                '/foo',
            ],
            [
                'foo',
                ['baz' => 42],
                [
                    'foo' => new Route('/foo/{baz}'),
                    'bar' => new Route('/bar'),
                ],
                [],
                '/foo/42',
            ],
            [
                'foo',
                ['baz' => 42],
                [
                    'foo' => new Route('/foo/{baz}'),
                    'bar' => new Route('/bar'),
                ],
                [
                    'baz' => [
                        42 => 'toto',
                    ],
                    'bar' => [
                        1 => 'titi',
                    ],
                ],
                '/foo/toto',
            ],
            [
                'foo',
                ['baz' => 42, 'bar' => 1],
                [
                    'foo' => new Route('/{bar}/{baz}'),
                    'bar' => new Route('/{baz}/{bar}'),
                ],
                [
                    'baz' => [
                        42 => 'toto',
                    ],
                    'bar' => [
                        1 => 'titi',
                    ],
                ],
                '/titi/toto',
            ],
            [
                'foo',
                ['baz' => 42, 'bar' => 1],
                [
                    'foo' => new Route('/{bar}/{baz}'),
                    'bar' => new Route('/{baz}/{bar}'),
                ],
                [
                    'baz' => [
                        42 => 'toto',
                    ],
                    'bar' => [
                        1 => 'titi',
                    ],
                ],
                '/titi/toto',
            ],
        ];
    }

    public function testGenerateMissingParameters()
    {
        $type = 'bar';
        $id = 42;
        $slugValue = 'toto';
        $routeName = 'foo';
        $route = new Route('/{bar}');

        $routeCollection = new RouteCollection();
        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();
        $context->expects($this->any())
            ->method('getHost')
            ->willReturn('');
        $context->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);

        // Init slugs
        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slug = new Slug();
        $slug->setType($type);
        $slug->setSlug($slugValue);
        $slugService->expects($this->never())
            ->method('findSlug');


        $slugServiceManager->expects($this->never())
            ->method('get');

        $generator = new UrlGenerator($routeCollection, $context, null, $slugServiceManager);

        $routeCollection->add($routeName, $route);

        $this->expectException(MissingMandatoryParametersException::class);
        $generator->generate($routeName, []);
    }

    public function testGenerateRouteNotFound()
    {
        $type = 'bar';
        $id = 42;
        $slugValue = 'toto';
        $routeName = 'baz';
        $route = new Route('/{bar}');

        $routeCollection = new RouteCollection();
        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();
        $context->expects($this->any())
            ->method('getHost')
            ->willReturn('');
        $context->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);

        // Init slugs
        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slug = new Slug();
        $slug->setType($type);
        $slug->setSlug($slugValue);
        $slugService->expects($this->never())
            ->method('findSlug');

        $slugServiceManager->expects($this->never())
            ->method('get');

        $generator = new UrlGenerator($routeCollection, $context, null, $slugServiceManager);

        $routeCollection->add($routeName, $route);

        $this->expectException(RouteNotFoundException::class);
        $generator->generate('foo', []);
    }

    public function testGenerateWithObject()
    {
        $type = 'bar';
        $entity = new \stdClass();
        $slugValue = 'toto';
        $routeName = 'foo';
        $route = new Route('/{bar}');

        $routeCollection = new RouteCollection();
        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();
        $context->expects($this->any())
            ->method('getHost')
            ->willReturn('');
        $context->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);

        // Init slugs
        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slug = new Slug();
        $slug->setType($type);
        $slug->setSlug($slugValue);
        $slugService->expects($this->atLeastOnce())
            ->method('findSlug')
            ->with($this->equalTo($entity))
            ->willReturn($slug);
        $slugService->expects($this->never())
            ->method('validate')
            ->with($this->equalTo($entity))
            ->willReturn(new ConstraintViolationList());


        $slugServiceManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo($type))
            ->willReturn($slugService);

        $generator = new UrlGenerator($routeCollection, $context, null, $slugServiceManager);

        $routeCollection->add($routeName, $route);

        $this->assertEquals('/' . $slugValue, $generator->generate($routeName, ['bar' => $entity]));
    }

    public function testGenerateWithWrongParameterType()
    {
        $type = 'bar';
        $entity = new \stdClass();
        $slugValue = 'toto';
        $routeName = 'foo';
        $route = new Route('/{bar}');

        $routeCollection = new RouteCollection();
        $context = $this->getMockBuilder(RequestContext::class)
            ->getMock();
        $context->expects($this->any())
            ->method('getHost')
            ->willReturn('');
        $context->expects($this->any())
            ->method('getParameters')
            ->willReturn([]);

        // Init slugs
        $slugServiceManager = $this->getMockBuilder(SlugServiceManager::class)
            ->getMock();

        $constraint = $this->getMockBuilder(ConstraintViolationInterface::class)
            ->getMock();

        $slugService = $this->getMockBuilder(SlugServiceInterface::class)
            ->getMock();
        $slug = new Slug();
        $slug->setType($type);
        $slug->setSlug($slugValue);

        $slugService
            ->expects($this->atLeastOnce())
            ->method('findSlug')
            ->with($this->equalTo($entity))
            ->willReturn(null);

        $slugService
            ->expects($this->never())
            ->method('validate');

        $slugServiceManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo($type))
            ->willReturn($slugService);

        $generator = new UrlGenerator($routeCollection, $context, null, $slugServiceManager);

        $routeCollection->add($routeName, $route);

        $this->expectException(InvalidParameterException::class);
        $generator->generate($routeName, ['bar' => $entity]);
    }
}
