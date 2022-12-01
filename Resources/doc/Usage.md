Usage
=====

# Configuration

Assuming you have a `Product` class:

```php
#[ORM\Entity]
class Product
{
    #[ORM\Column(length: 255)]
    private string $name;
    
    #[ORM\Column(length: 255)]
    private string $slug;
    // ...
}
```

Within you `ProductController`, create a route with the new annotation class `Loconox\EntityRoutingBundle\Annotation\Route`

```php
use Loconox\EntityRoutingBundle\Annotation\Route;
// ...

class ProductController extends AbstractController
{

    #[Route('/{product}', name: 'product')]
    public function product($product)
    {
        // ...
    }
}
```

Create a `ProductSlugService` class and implement the different functions corresponding to your needs.

```php
<?php

namespace App\Slug\Service;


use App\Entity\Product;
use Doctrine\ORM\EntityManager;
use Loconox\EntityRoutingBundle\Model\SlugInterface;
use Loconox\EntityRoutingBundle\Slug\Service\BaseSlugService;

class ProductSlugService extends BaseSlugService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Get the entity linked to the slug
     *
     * @param SlugInterface $slug
     *
     * @return mixed
     */
    public function getEntity(SlugInterface $slug)
    {
        return $this->em->getRepository(Product::class)->find($slug->getEntityId());
    }

    /**
     * Set the slug of the entity
     *
     * @param Product $product
     * @param SlugInterface $slug
     */
    public function setEntitySlug(SlugInterface $slug, $product): void
    {
        $product->setSlug($slug->getSlug());
    }

    /**
     * Get the slug linked to the entity
     *
     * @param Product $entity
     *
     * @return string
     */
    public function getEntitySlug($product): string
    {
        if (!$product->getSlug()) {
            $slug = $this->slugify($product->getName());
            $product->setSlug($slug);
        }

        return $product->getSlug();
    }

    /**
     * Returns true if the entity has changed, false otherwise
     *
     * @param $entity
     *
     * @return bool
     */
    public function hasChanged($entity): bool
    {
        $slug = $this->findSlug($entity);
        if (!$slug) {
            return false;
        }
        $oldSlug = $slug->getSlug();
        $newSlug = $this->getEntitySlug($entity);

        return $oldSlug !== $newSlug;
    }

    /**
     * Returns the entity id
     *
     * @param Product $entity
     *
     * @return mixed
     */
    public function getEntityId($entity)
    {
        return $entity->getId();
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }
}
```

Declare the service:

```yaml
# config/services.yaml
    App\Slug\Service\ProductSlugService:
        arguments:
            - App\Entity\Product
            - "@loconox_entity_routing.manager.slug"
        calls:
            - [ setEntityManager, [ "@loconox_entity_routing.entity_manager" ]]
        tags:
            - { name: loconox_entity_routing.slug.service, alias: product }
```

Note that the alias is used to match the entity class in routes.

# Twig

There is a twig function `slug` to get slug form entity

```html
{{ slug(product) }}
```

Generate route :

```html
<a href="{{ path('product', {'product': product}) }}">{{ product.name }}</a>
```

# Slug creation

To trigger the creation of a slug, you can do it manually.

```php
<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Loconox\EntityRoutingBundle\Slug\SlugServiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product/new/{name}', name: 'newProduct')]
    public function new(ManagerRegistry $doctrine, SlugServiceManager $slugServiceManager, $name)
    {
        $product = new Product();
        $product->setName($name);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);

        $service = $slugServiceManager->get(Product::class);
        $service->createSlug($product);

        $entityManager->flush();
        // ...
    }
}
```

Or by using the `Events::ACTION_CREATE_SLUG` event. This way, it automatically handles slug collisions.

```php
<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Loconox\EntityRoutingBundle\Event\SlugEvent;
use Loconox\EntityRoutingBundle\Events;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    #[Route('/product/new/{name}', name: 'newProduct')]
    public function new(ManagerRegistry $doctrine, EventDispatcherInterface $dispatcher, $name)
    {
        // ...
        $product = new Product();
        $product->setName($name);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($product);

        $event = new SlugEvent($product);
        $dispatcher->dispatch($event, Events::ACTION_CREATE_SLUG);

        $entityManager->flush();
        
        // ...
    }
}
```
