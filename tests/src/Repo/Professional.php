<?php

namespace Harp\JsonStore\Test\Repo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Professional extends User {

    private static $instance;

    /**
     * @return User
     */
    public static function get()
    {
        if (! self::$instance) {
            self::$instance = new Professional(
                'Harp\JsonStore\Test\Model\Professional',
                TEST_DIR.'/User.json'
            );
        }

        return self::$instance;
    }

    public function initialize()
    {
        parent::initialize();
    }
}
