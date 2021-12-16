<?php

namespace Awaresoft\TreeBundle\Admin;

use Awaresoft\Sonata\AdminBundle\Admin\AbstractAdmin as AwaresoftAbstractAdmin;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Gedmo\Sluggable\Util as Sluggable;

/**
 * Class AbstractTreeAdmin
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class AbstractTreeAdmin extends AwaresoftAbstractAdmin
{
    /**
     * Default name of tree root
     *
     * @var string
     */
    protected $treeRootName = 'root';

    /**
     * Field of Admin object which will be title
     *
     * @var string
     */
    protected $titleField = null;

    /**
     * Maximal results per page
     *
     * @var int
     */
    protected $maxPerPage = 1000;

    /**
     * Maximal links for one page
     *
     * @var int
     */
    protected $maxPageLinks = 25;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @return EntityRepository|NestedTreeRepository
     */
    protected $repository;

    /**
     * Dedicated construct to resist inactive scope exceptions
     *
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     * @param ContainerInterface $container
     */
    public function __construct($code, $class, $baseControllerName, ContainerInterface $container)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->container = $container;

        // prevent before inactive scope
        if ($request = $this->container->get('request_stack')->getCurrentRequest()) {
            $this->setRequest($request);
        }

        $this->em = $this->container->get('doctrine')->getManager();
        $this->repository = $this->em->getRepository($this->getClass());
    }

    /**
     * @inheritdoc
     */
    public function initialize()
    {
        parent::initialize();

        if ($this->titleField == null) {
            throw new \Exception('"titleField" is not set correctly');
        }

        $reflection = new \ReflectionClass($this->getClass());

        if (!$reflection->getParentClass()->getParentClass() && (!$reflection->getParentClass() || $reflection->getParentClass()->getName() != 'Awaresoft\TreeBundle\Entity\AbstractTreeNode')) {
            throw new \Exception('A tree entity should extend "Awaresoft\TreeBundle\Entity\AbstractTreeNode"');
        }

        if ($reflection->getParentClass()->getParentClass() && $reflection->getParentClass()->getParentClass()->getName() != 'Awaresoft\TreeBundle\Entity\AbstractTreeNode') {
            throw new \Exception('A tree entity should extend "Awaresoft\TreeBundle\Entity\AbstractTreeNode"');
        }

        $rootNodes = $this->repository->getRootNodes();
        $sites = $this->container->get('sonata.page.manager.site')->findAll();

        if (count($rootNodes) == 0) {
            $className = $this->getClass();
            $root = new $className();

            if (count($sites) > 1 && method_exists($root, 'setSite')) {
                foreach ($sites as $site) {
                    $className = $this->getClass();
                    $root = new $className();
                    call_user_func([$root, 'set' . ucfirst($this->titleField)], $this->treeRootName);
                    call_user_func([$root, 'setSite'], $site);
                    $this->em->persist($root);
                }

                $this->em->flush();
            } else {
                call_user_func([$root, 'set' . $this->titleField], $this->treeRootName);
                $this->em->persist($root);
                $this->em->flush();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function createQuery($context = 'list')
    {
        $queryBuilder = $this->em
            ->createQueryBuilder()
            ->select('c')
            ->from($this->getClass(), 'c')
            ->addOrderBy('c.root', 'ASC')
            ->addOrderBy('c.left', 'ASC');

        $query = new ProxyQuery($queryBuilder);

        return $query;
    }

    /**
     * Move up in tree structure
     *
     * @return RedirectResponse
     */
    public function upAction()
    {
        $this->repository->moveUp($this->getSubject());

        return new RedirectResponse($this->generateUrl('list'));
    }

    /**
     * Move down in tree structure
     *
     * @return RedirectResponse
     */
    public function downAction()
    {
        $this->repository->moveDown($this->getSubject());

        return new RedirectResponse($this->generateUrl('list'));
    }

    /**
     * Return titleField of the object
     *
     * @param mixed $object
     *
     * @return string
     */
    public function toString($object)
    {
        return call_user_func([$object, 'get' . $this->titleField]);
    }

    /**
     * Gets normalized (letters and underscores only) code for this admin class, used for route names
     *
     * @return string
     */
    public function getNormalizedCode()
    {
        return strtolower(preg_replace('/[^A-Za-z0-9_]/', '_', $this->code));
    }

    /**
     * Get max level of menu tree
     *
     * @param $settingName
     *
     * @return null
     */
    protected function prepareMaxDepthLevel($settingName)
    {
        $maxDepthLevel = 999;
        $settingService = $this->configurationPool->getContainer()->get('awaresoft.setting');
        $setting = $settingService->get($settingName);

        if ($setting && $setting->isEnabled()) {
            $maxDepth = $setting->getFields()->get('MAX_DEPTH');

            if ($maxDepth && $maxDepth->isEnabled() && $maxDepth->getValue()) {
                $maxDepthLevel = $maxDepth->getValue();
            }

            if ($this->getSubject() && $this->getSubject()->getId()) {
                $maxDepth = $setting->getFields()->get('MAX_DEPTH_' . strtoupper(Sluggable\Urlizer::urlize($this->getSubject()->getName(), '_')));

                if ($maxDepth && $maxDepth->isEnabled() && $maxDepth->getValue()) {
                    $maxDepthLevel = $maxDepth->getValue();
                }
            }
        }

        return $maxDepthLevel;
    }

    /**
     * Default fields
     *
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->addParentField($formMapper);
        $formMapper->add($this->titleField);
    }

    /**
     * Add parent field to form fields
     *
     * @param FormMapper $formMapper
     * @param null $maxDepthLevel
     * @param null $site
     */
    protected function addParentField(FormMapper $formMapper, $maxDepthLevel = null, $site = null)
    {
        $id = $this->getSubject()->getId();

        $formMapper
            ->add('parent', null, [
                'choice_label' => 'treeTitleByLevelInSelect',
                'required' => false,
                'query_builder' => function ($repo) use ($id, $maxDepthLevel, $site) {
                    $qb = $repo->createQueryBuilder('c');

                    if ($maxDepthLevel !== null) {
                        $qb
                            ->where('c.level <= :level')
                            ->setParameter('level', $maxDepthLevel - 1);
                    }

                    if ($id) {
                        $qb
                            ->andWhere('c.id <> :id')
                            ->setParameter('id', $id);
                    }

                    if ($site) {
                        $qb
                            ->andWhere('c.site = :site')
                            ->setParameter('site', $site);
                    }

                    $qb
                        ->orderBy('c.root, c.left', 'ASC');

                    return $qb;
                },
                'data' => $this->getSubject()->getParent(),
            ]);
    }

    /**
     * Allow to extend standard list fields
     *
     * @param ListMapper $listMapper
     *
     * @return ListMapper
     */
    protected function configureListFieldsExtend(ListMapper $listMapper)
    {
        return $listMapper;
    }

    /**
     * Add tree change position controls to list
     *
     * @param ListMapper $listMapper
     */
    protected function addListTreeControls(ListMapper $listMapper)
    {
        $listMapper
            ->add('tree_position_up_down', 'text', ['label' => 'Up/Down', 'template' => 'AwaresoftTreeBundle:CRUD:list_change_position.html.twig']);
    }

    /**
     * Don't override this method.
     * If you want to extend list view use configureListFieldsExtend method
     *
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('treeTitleByLevel', null, ['label' => 'Name', 'template' => 'AwaresoftTreeBundle:CRUD:list_raw.html.twig']);
        $this->configureListFieldsExtend($listMapper);
        $this->addListTreeControls($listMapper);
        $listMapper
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);

        $collection->add(
            $this->getNormalizedCode() . '_up',
            $this->getRouterIdParameter() . '/up',
            ['_controller' => $this->code . ':upAction']
        );
        $collection->add(
            $this->getNormalizedCode() . '_down',
            $this->getRouterIdParameter() . '/down',
            ['_controller' => $this->code . ':downAction']
        );

        $collection->add('tree', 'tree');
    }
}
