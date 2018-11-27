<?php
namespace Expertime\Iclient\Api\Data;

interface UserInterface
{
    const ID = 'id';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const AVATAR = 'avatar';
    const IMPORTED = 'imported';

    public function getFirstName();

    public function getLastName();

    public function getAvatar();

    public function getId();

    public function setFirstName($firstname);

    public function setLastName($lastname);

    public function setAvatar($avatar);

    public function setId($id);

    public function getImported();

    public function setImported($value);
}