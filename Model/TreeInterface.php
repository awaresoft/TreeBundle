<?php

namespace Awaresoft\TreeBundle\Model;

use Awaresoft\Sonata\PageBundle\Entity\Site;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TreeInterface
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
interface TreeInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return self
     */
    public function getParent();

    /**
     * @param self $parent
     *
     * @return $this
     */
    public function setParent($parent);

    /**
     * @return bool
     */
    public function hasParent();

    /**
     * @return int
     */
    public function getLeft();

    /**
     * @param int $left
     *
     * @return $this
     */
    public function setLeft($left);

    /**
     * @return int
     */
    public function getRight();

    /**
     * @param int $right
     *
     * @return $this
     */
    public function setRight($right);

    /**
     * @return self
     */
    public function getRoot();

    /**
     * @param self $root
     *
     * @return $this
     */
    public function setRoot($root);

    /**
     * @return self[]
     */
    public function getChildren();

    /**
     * @param self[] $children
     *
     * @return $this
     */
    public function setChildren($children);

    /**
     * @return int
     */
    public function getLevel();

    /**
     * @param int $level
     *
     * @return $this
     */
    public function setLevel($level);

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @param boolean $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * @return boolean
     */
    public function isDeletable();

    /**
     * @param boolean $deletable
     *
     * @return $this
     */
    public function setDeletable($deletable);

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * @return SiteInterface
     */
    public function getSite();

    /**
     * @param SiteInterface $site
     *
     * @return $this
     */
    public function setSite($site);
}
