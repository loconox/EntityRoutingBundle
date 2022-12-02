<?php

namespace Loconox\EntityRoutingBundle\Tests\Slug;

use Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use PHPUnit\Framework\TestCase;

class SlugServiceManagerTest extends TestCase
{
    public function testAdd()
    {
        $fooService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $manager = new SlugServiceManager();

        $this->assertCount(0, $manager->getAll());
        $manager->add($fooService, 'adw.service.foo', 'foo');
        $this->assertCount(1, $manager->getAll());
    }

    public function testGet()
    {
        $fooService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $fooService->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(get_class(new FooClass())));
        $manager = new SlugServiceManager();

        $manager->add($fooService, 'adw.service.foo', 'foo');
        $this->assertEquals($fooService, $manager->get('foo'));
        $this->assertEquals($fooService, $manager->get(new FooClass()));
    }

    public function testGetUnknownService()
    {
        $manager = new SlugServiceManager();

        $this->assertFalse($manager->get('foo'));
        $this->assertFalse($manager->get(new FooClass()));
    }

    public function testGetArrayClass()
    {
        $foobarService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $foobarService->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue([get_class(new TotoClass()), get_class(new BarClass())]));
        $manager = new SlugServiceManager();

        $manager->add($foobarService, 'adw.service.foobar', 'foobar');
        $this->assertEquals($foobarService, $manager->get('foobar'));
        $this->assertEquals($foobarService, $manager->get([new FooClass(), new BarClass()]));
    }

    public function testGetClassWithServiceArrayClass()
    {
        $foobarService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $foobarService->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue([get_class(new TotoClass()), get_class(new FooClass())]));
        $manager = new SlugServiceManager();

        $manager->add($foobarService, 'adw.service.foobar', 'foobar');
        $this->assertFalse($manager->get(new FooClass()));
    }

    public function testGetArrayClassWithServiceClass()
    {
        $foobarService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $foobarService->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue(get_class(new FooClass())));
        $manager = new SlugServiceManager();

        $manager->add($foobarService, 'adw.service.foobar', 'foobar');
        $this->assertFalse($manager->get([new FooClass(), new FooClass()]));
    }

    public function testGetArrayClassWrongOrder()
    {
        $foobarService = $this->getMockBuilder(SlugServiceInterface::class)->getMock();
        $foobarService->expects($this->once())
            ->method('getClass')
            ->will($this->returnValue([get_class(new BarClass()), get_class(new TotoClass())]));
        $manager = new SlugServiceManager();

        $manager->add($foobarService, 'adw.service.foobar', 'foobar');
        $this->returnValue($foobarService, $manager->get([new FooClass(), new BarClass()]));
    }
}
class TotoClass{}
class FooClass extends TotoClass{}
class BarClass{}
