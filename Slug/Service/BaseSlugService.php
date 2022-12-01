<?php


namespace Loconox\EntityRoutingBundle\Slug\Service;

use Loconox\EntityRoutingBundle\Entity\SlugManager;
use Loconox\EntityRoutingBundle\Model\SlugInterface;
use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlug;
use Loconox\EntityRoutingBundle\Validator\Constraints\UniqueSlugValidator;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseSlugService implements SlugServiceInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var SlugManager
     */
    protected $slugManager;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct($class, SlugManager $slugManager, ValidatorInterface $validator)
    {
        $this->class = $class;
        $this->slugManager = $slugManager;
        $this->cache = [];
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): array|string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function incrementSlug($entity, SlugInterface $oldSlug): SlugInterface
    {
        $newSlug = $this->createSlug($entity);
        $newSlug->setOld($oldSlug);
        $oldSlug->setNew($newSlug);
        $this->slugManager->save($newSlug);

        return $newSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function createSlug($entity, bool $save = true): SlugInterface
    {
        $slug = $this->slugManager->create();
        $this->setValues($slug, $entity);
        if ($save) {
            $this->slugManager->save($slug);
        }

        return $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSlug($entity): SlugInterface
    {
        $slug = $this->findSlug($entity);

        if ($this->hasChanged($entity)) {
            $id               = $this->getEntityId($entity);
            $this->cache[$id] = $this->incrementSlug($entity, $slug);

            return $this->cache[$id];
        }
        $this->setValues($slug, $entity);
        $this->slugManager->save($slug);

        return $slug;
    }

    /**
     * {@inheritdoc}
     */
    public function findSlug($entity, $create = false, $optional = false): ?SlugInterface
    {
        $id = $this->getEntityId($entity);
        if (!isset($this->cache[$id])) {
            $slug = $this->slugManager->findLastBy(
                [
                    'type' => $this->alias,
                    'entityId' => $this->getEntityId($entity),
                ]
            );
            if (!$slug && $create) {
                $slug = $this->createSlug($entity);
            }
            $this->cache[$id] = $slug;
        }

        return $this->cache[$id];
    }

    public function setValues(SlugInterface $slug, $entity)
    {
        $slug->setType($this->alias);
        $text = $this->slugify($this->getEntitySlug($entity));
        $slug->setSlug($text);
        $slug->setEntityId($this->getEntityId($entity));
    }

    public function validate($value): ConstraintViolationListInterface
    {
         return $this->validator->validate($value, $this->constraints());
    }

    public function saveSlug(SlugInterface $slug): void
    {
        $this->slugManager->save($slug);
    }

    protected function constraints()
    {
        return [
            new UniqueSlug(),
        ];
    }

    /**
     * Transform a string in slug, compatible with url
     *
     * @param $text
     * @return string
     */
    protected function slugify($text): string
    {
        $text = trim($text);
        // this code is for BC
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        /*if (function_exists('iconv')) {
            $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        }*/
        $text = transliterator_transliterate("Latin-ASCII", $text);
        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        return preg_replace('~[^-\w]+~', '', $text);
    }
}
