<?php

namespace Loconox\EntityRoutingBundle\Admin;

use Loconox\EntityRoutingBundle\Form\Transformer\SettingsTransformer;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManagerInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class SlugAdmin extends Admin
{

	/**
	 * @var SlugServiceManagerInterface
	 */
	protected $slugServiceManager;

	/**
	 * {@inheritdoc}
	 */
	protected function configureShowFields(ShowMapper $showMapper)
	{
		$showMapper
			->add('slug')
			->add('type')
			->add('new', null, ['associated_property' => 'slug'])
			->add('old', null, ['associated_property' => 'slug'])
			->add('updatedAt')
			->add('createdAt');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function configureFormFields(FormMapper $formMapper)
	{
		$types = [];
		$services = $this->slugServiceManager->getAll();
		foreach ($services as $service) {
			$types[$service->getAlias()] = $service->getAlias();
		}

		$formMapper
			->add('slug')
			->add('type', 'choice', ['choices' => $types])
			->add('entityId', null, array('required' => false))
            ->add('new', 'sonata_type_model', ['property' => 'slug', 'required' => false])
        ;
	}

	// Fields to be shown on filter forms
	protected function configureDatagridFilters(DatagridMapper $datagridMapper)
	{
		$types = [];
		$services = $this->slugServiceManager->getAll();
		foreach ($services as $service) {
			$types[] = $service->getAlias();
		}
		$datagridMapper
			->add('slug')
			->add('type', 'doctrine_orm_choice', [], 'choice', ['choices' => $types]);
	}

	// Fields to be shown on lists
	protected function configureListFields(ListMapper $listMapper)
	{
		$listMapper
			->addIdentifier('slug')
            ->add('new', null, ['associated_property' => 'slug'])
			->add('type')
			->add('redirection', 'boolean')
        ;
	}

	/**
	 * @param SlugServiceManagerInterface $slugServiceManager
	 */
	public function setSlugServiceManager($slugServiceManager)
	{
		$this->slugServiceManager = $slugServiceManager;
	}

	public function validate(ErrorElement $errorElement, $object)
	{
		$service = $this->slugServiceManager->get($object->getType());
		// Type
		if (!$service) {
			$services = [];
			foreach ($this->slugServiceManager->getAll() as $service) {
				$services[] = $service->getName();
			}
			$errorElement
				->with('type')
				->addViolation(
					sprintf(
						'Unexpected value, get %s and should be one of these %s',
						$object->getType(),
						implode(', ', $services)
					)
				)
				->end();
		}
	}

    public function toString($slug)
    {
        return $slug->getSlug();
    }
}