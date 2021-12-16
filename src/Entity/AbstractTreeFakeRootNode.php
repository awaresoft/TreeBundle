<?php

namespace Awaresoft\TreeBundle\Entity;

/**
 * AbstractTreeNodeFakeRoot class
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class AbstractTreeFakeRootNode implements TreeNodeInterface
{
    /**
     * Returns title according to tree level
     *
     * @return string
     */
    public function getTreeTitleByLevel()
    {
        $str = ' ';
        $call = call_user_func(array($this, 'get' . $this->getTitleFieldName()));

        if ($this->getLevel() > 0) {
            $str = str_repeat('—', ($this->getLevel() - 1) * 1);
        }

        if ($this->getLevel() == 1) {
            $call = '<b>' . $call . '</b>';
        }

        return sprintf(
            '%s %s',
            $str,
            $call
        );
    }

    /**
     * Returns title for use in select
     *
     * @return string
     */
    public function getTreeTitleByLevelInSelect()
    {
        return sprintf(
            '%s %s',
            $this->getLevel() > 0 ? str_repeat('—', ($this->getLevel() - 1) * 1): ' ',
            call_user_func(array($this, 'get' . $this->getTitleFieldName()))
        );
    }
}
