<?php

namespace Expertime\Iclient\Model\ResourceModel\User;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
       $this->_init('Expertime\Iclient\Model\User','Expertime\Iclient\Model\ResourceModel\User');
    }

    protected function _beforeLoad()
    {
        $this->addFieldToFilter('imported', 0);
        return $this;
    }

    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])->where('imported=?', 0);
        return $this;
    }
}