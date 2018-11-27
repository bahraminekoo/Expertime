<?php

namespace Expertime\Iclient\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Expertime\Iclient\Api\Data\UserInterface;

class User extends AbstractModel implements UserInterface, IdentityInterface
{

    const CACHE_TAG = 'expertime_import_client';

    protected function _construct()
    {
        $this->_init('Expertime\Iclient\Model\ResourceModel\User');
    }

    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME);
    }

    public function getLastName()
    {
        return $this->getData(self::LAST_NAME);
    }

    public function getAvatar()
    {
        return $this->getData(self::AVATAR);
    }

    public function getImported()
    {
        return $this->getData(self::IMPORTED);
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setFirstName($firstname)
    {
        return $this->setData(self::FIRST_NAME, $firstname);
    }

    public function setLastName($lastname)
    {
        return $this->setData(self::LAST_NAME, $lastname);
    }

    public function setAvatar($avatar)
    {
        return $this->setData(self::AVATAR, $avatar);
    }

    public function setImported($value)
    {
        return $this->setData(self::IMPORTED, $value);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
}