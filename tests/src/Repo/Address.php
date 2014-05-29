<?php

namespace Harp\JsonStore\Test\Repo;

use Harp\JsonStore\Rel;
use Harp\JsonStore\AbstractJsonRepo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Address extends AbstractJsonRepo {

    private static $instance;

    /**
     * @return User
     */
    public static function get()
    {
        if (! self::$instance) {
            self::$instance = new Address(
                'Harp\JsonStore\Test\Model\Address',
                TEST_DIR.'/Address.json'
            );
        }

        return self::$instance;
    }

    public function initialize()
    {
        $this->addRel(new Rel\One('user', $this, Address::get()));
    }
}
