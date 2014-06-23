<?php

namespace Harp\JsonStore\Test\Model;

use Harp\Core\Model\AbstractModel;
use Harp\Core\Model\InheritedTrait;
use Harp\JsonStore\Test\Repo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class User extends AbstractModel {

    use InheritedTrait;

    public $id;
    public $name;
    public $password;
    public $addressId;
    public $isBlocked = false;

    public function getRepo()
    {
        return Repo\User::get();
    }

    public function getAddress()
    {
        return $this->getLink('address')->get();
    }

    public function setAddress(Address $address)
    {
        $this->getLink('address')->set($address);

        return $this;
    }

    public function getPosts()
    {
        return $this->getLink('posts');
    }
}
