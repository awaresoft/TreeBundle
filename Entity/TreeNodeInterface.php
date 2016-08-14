<?php

namespace Awaresoft\TreeBundle\Entity;

/**
 * Interface TreeNodeInterface
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
interface TreeNodeInterface
{
    /**
     * This method determine which column will be title
     *
     * @return string
     */
    public function getTitleFieldName();
}