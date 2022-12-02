<?php

namespace Loconox\EntityRoutingBundle\Tests\Listener;

use Loconox\EntityRoutingBundle\Event\SlugEvent;
use Loconox\EntityRoutingBundle\Listener\UniqueSlugViolationListener;
use Loconox\EntityRoutingBundle\Entity\Slug;
use PHPUnit\Framework\TestCase;

class UniqueSlugViolationListenerTest extends TestCase
{
    public function testUniqueSlugVioation()
    {

        $entity = new \stdClass();

        $event = new SlugEvent($entity);

        $slugViolation = new Slug();
        $slugViolation->setSlug('foo');
        $slugViolation->setEntityId(42);

        $slugFoo = new Slug();
        $slugFoo->setEntityId(42);

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->once())
            ->method('findSlugLike')
            ->with($this->equalTo($slugViolation))
            ->will($this->returnValue([$slugFoo]));

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
            ->method('createSlug')
            ->with($this->equalTo($entity), $this->equalTo(false))
            ->will($this->returnValue($slugViolation));
        $service->expects($this->once())
            ->method('setEntitySlug')
            ->with($this->equalTo($slugViolation), $this->equalTo($entity));

        $slugServiceManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface')->getMock();
        $slugServiceManager->expects($this->once())
            ->method('get')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($service));


        $listener = new UniqueSlugViolationListener($slugManager, $slugServiceManager);
        $listener->uniqueSlugViolation($event);
        $this->assertEquals('foo-1', $slugViolation->getSlug());
    }

    public function testUniqueSlugVioationEmpty()
    {

        $entity = new \stdClass();

        $event = new SlugEvent($entity);

        $slugViolation = new Slug();
        $slugViolation->setSlug('foo');

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->once())
            ->method('findSlugLike')
            ->with($this->equalTo($slugViolation))
            ->will($this->returnValue([]));

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
            ->method('createSlug')
            ->with($this->equalTo($entity), $this->equalTo(false))
            ->will($this->returnValue($slugViolation));
        $service->expects($this->once())
            ->method('setEntitySlug')
            ->with($this->equalTo($slugViolation), $this->equalTo($entity));

        $slugServiceManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface')->getMock();
        $slugServiceManager->expects($this->once())
            ->method('get')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($service));


        $listener = new UniqueSlugViolationListener($slugManager, $slugServiceManager);
        $listener->uniqueSlugViolation($event);
        $this->assertEquals('foo-1', $slugViolation->getSlug());
    }

    public function testUniqueSlugVioationMultiple()
    {

        $entity = new \stdClass();

        $event = new SlugEvent($entity);

        $slugViolation = new Slug();
        $slugViolation->setSlug('foo');
        $slugViolation->setEntityId(42);

        $slugFoo = new Slug();
        $slugFoo->setEntityId(42);
        $slugBar = new Slug();
        $slugBar->setSlug('foo-3');
        $slugBaz = new Slug();
        $slugBaz->setSlug('foo-toto');
        $slugBabar = new Slug();
        $slugBabar->setSlug('foo-2');

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->once())
            ->method('findSlugLike')
            ->with($this->equalTo($slugViolation))
            ->will($this->returnValue([$slugFoo, $slugBar, $slugBaz]));

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
            ->method('createSlug')
            ->with($this->equalTo($entity), $this->equalTo(false))
            ->will($this->returnValue($slugViolation));
        $service->expects($this->once())
            ->method('setEntitySlug')
            ->with($this->equalTo($slugViolation), $this->equalTo($entity));

        $slugServiceManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface')->getMock();
        $slugServiceManager->expects($this->once())
            ->method('get')
            ->with($this->equalTo($entity))
            ->will($this->returnValue($service));


        $listener = new UniqueSlugViolationListener($slugManager, $slugServiceManager);
        $listener->uniqueSlugViolation($event);
        $this->assertEquals('foo-4', $slugViolation->getSlug());
    }
}
