<?php

namespace Loconox\EntityRoutingBundle\Tests\Validator\Constraints;

use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlug;
use Loconox\EntityRoutingBundle\Entity\Slug;
use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlugValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UniqueSlugValidatorTest extends TestCase
{
    public function testValidate()
    {
        $entity     = new \stdClass();
        $constraint = new UniqueSlug();

        $slug = new Slug();
        $slug->setEntityId(42);
        $sameSlug = new Slug();
        $sameSlug->setEntityId(42);

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
                ->method('createSlug')
                ->with($this->equalTo($entity))
                ->will($this->returnValue($slug));

        $slugServiceManager = $this->getMockBuilder(
            'Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface'
        )->getMock();
        $slugServiceManager->expects($this->once())
                           ->method('get')
                           ->with($entity)
                           ->will($this->returnValue($service));

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->atLeastOnce())
                    ->method('findBySlug')
                    ->with($this->equalTo($slug))
                    ->will($this->returnValue([$sameSlug]));

        $validator = $this->getMockBuilder('Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlugValidator')
                          ->setMethods(['buildViolation'])
                          ->setConstructorArgs([$slugServiceManager, $slugManager])
                          ->getMock();
        $validator->expects($this->never())
                  ->method('buildViolation');

        $validator->validate($entity, $constraint);
    }

    public function testValidateEmpty()
    {
        $entity     = new \stdClass();
        $constraint = new UniqueSlug();

        $slug = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugInterface')->getMock();

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
                ->method('createSlug')
                ->with($this->equalTo($entity))
                ->will($this->returnValue($slug));

        $slugServiceManager = $this->getMockBuilder(
            'Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface'
        )->getMock();
        $slugServiceManager->expects($this->once())
                           ->method('get')
                           ->with($entity)
                           ->will($this->returnValue($service));

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->atLeastOnce())
                    ->method('findBySlug')
                    ->with($this->equalTo($slug))
                    ->will($this->returnValue([]));

        $validator = $this->getMockBuilder('Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlugValidator')
                          ->setMethods(['buildViolation'])
                          ->setConstructorArgs([$slugServiceManager, $slugManager])
                          ->getMock();
        $validator->expects($this->never())
                  ->method('buildViolation');

        $validator->validate($entity, $constraint);
    }

    public function testValidateOtherEntityId()
    {
        $entity     = new \stdClass();
        $constraint = new UniqueSlug();

        $slug = new Slug();
        $slug->setEntityId(42);
        $slug->setSlug('foo');
        $otherSlug = new Slug();
        $otherSlug->setEntityId(43);

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
                ->method('createSlug')
                ->with($this->equalTo($entity))
                ->will($this->returnValue($slug));

        $slugServiceManager = $this->getMockBuilder(
            'Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface'
        )->getMock();
        $slugServiceManager->expects($this->once())
                           ->method('get')
                           ->with($entity)
                           ->will($this->returnValue($service));

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->atLeastOnce())
                    ->method('findBySlug')
                    ->with($this->equalTo($slug))
                    ->will($this->returnValue([$otherSlug]));

        $builder = $this->getMockBuilder(
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface'
        )->getMock();
        $builder->expects($this->once())
                  ->method('setInvalidValue')
                  ->with($slug->getSlug())
                  ->will($this->returnSelf());
        $builder->expects($this->once())
                  ->method('addViolation');

        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects($this->once())
                ->method('buildViolation')
                ->willReturn($builder);

        $validator = new UniqueSlugValidator($slugServiceManager, $slugManager);
        $validator->initialize($context);

        $validator->validate($entity, $constraint);
    }

    public function testValidateMoreThanOne()
    {
        $entity     = new \stdClass();
        $constraint = new UniqueSlug();

        $slug = new Slug();
        $slug->setEntityId(42);
        $slug->setSlug('foo');
        $otherSlug = new Slug();
        $otherSlug->setEntityId(43);
        $sameSlug = new Slug();
        $sameSlug->setEntityId(42);

        $service = $this->getMockBuilder('Loconox\EntityRoutingBundle\Slug\Service\SlugServiceInterface')->getMock();
        $service->expects($this->once())
                ->method('createSlug')
                ->with($this->equalTo($entity))
                ->will($this->returnValue($slug));

        $slugServiceManager = $this->getMockBuilder(
            'Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface'
        )->getMock();
        $slugServiceManager->expects($this->once())
                           ->method('get')
                           ->with($entity)
                           ->will($this->returnValue($service));

        $slugManager = $this->getMockBuilder('Loconox\EntityRoutingBundle\Model\SlugManagerInterface')->getMock();
        $slugManager->expects($this->atLeastOnce())
                    ->method('findBySlug')
                    ->with($this->equalTo($slug))
                    ->will($this->returnValue([$sameSlug, $otherSlug]));

        $builder = $this->getMockBuilder(
            'Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface'
        )->getMock();
        $builder->expects($this->once())
                ->method('setInvalidValue')
                ->with($slug->getSlug())
                ->will($this->returnSelf());
        $builder->expects($this->once())
                ->method('addViolation');

        $context = $this->getMockBuilder(ExecutionContextInterface::class)->getMock();
        $context->expects($this->once())
                ->method('buildViolation')
                ->willReturn($builder);

        $validator = new UniqueSlugValidator($slugServiceManager, $slugManager);
        $validator->initialize($context);

        $validator->validate($entity, $constraint);
    }
}
