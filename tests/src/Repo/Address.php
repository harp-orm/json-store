<?php

namespace CL\LunaJsonStore\Test\Repo;

use CL\LunaJsonStore\Rel;
use CL\LunaJsonStore\AbstractJsonRepo;

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
                'CL\LunaJsonStore\Test\Model\Address',
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
