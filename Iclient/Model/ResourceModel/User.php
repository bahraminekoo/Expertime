<?php

namespace Expertime\Iclient\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class User extends AbstractDb
{
    /**
     * User Abstract Resource Constructor
     * @return void
     */
    protected function _construct()
    {
        $this->_init('expertime_api_users', 'id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $select = $this->getConnection()->select()->from($this->getMainTable())->where('imported=?', 0);
        return $select;
    }
}