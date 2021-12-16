<?php

namespace Awaresoft\TreeBundle\Entity;

use Awaresoft\TreeBundle\Model\TreeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass()
 * @Gedmo\Tree(type="nested")
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class AbstractTree extends AbstractTreeNode implements TreeInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="tree_left", type="integer")
     *
     * @Gedmo\TreeLeft
     *
     * @var int
     */
    protected $left;

    /**
     * @ORM\Column(name="tree_right", type="integer")
     *
     * @Gedmo\TreeRight
     *
     * @var int
     */
    protected $right;

    /**
     * Need to be overwrite
     *
     * @var self
     */
    protected $parent;

    /**
     * @ORM\Column(name="tree_root", type="integer", nullable=true)
     *
     * @Gedmo\TreeRoot
     *
     * @var self
     */
    protected $root;

    /**
     * @ORM\Column(name="tree_level", type="integer")
     *
     * @Gedmo\TreeLevel
     *
     * @var int
     */
    protected $level;

    /**
     * Need to be overwrite
     *
     * @var self[]
     */
    protected $children;

    /**
     * @ORM\ManyToOne(targetEntity="Awaresoft\Sonata\PageBundle\Entity\Site")
     *
     * @var SiteInterface
     */
    protected $site;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var boolean
     */
    protected $enabled;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $deletable;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->deletable = true;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasParent()
    {
        return $this->parent ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @inheritdoc
     */
    public function setLeft($left)
    {
        $this->left = $left;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @inheritdoc
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @inheritdoc
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @inheritdoc
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isDeletable()
    {
        return $this->deletable;
    }

    /**
     * @inheritdoc
     */
    public function setDeletable($deletable)
    {
        $this->deletable = $deletable;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @inheritdoc
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->id;
    }
}
