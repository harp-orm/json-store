<?php

namespace Harp\JsonStore\Test\Repo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Professional extends User {

    public static function newInstance()
    {
        return new Professional('Harp\JsonStore\Test\Model\Professional', TEST_DIR.'/User.json');
    }

    public function initialize()
    {
        parent::initialize();
    }
}
