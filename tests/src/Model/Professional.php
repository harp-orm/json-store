<?php

namespace Harp\JsonStore\Test\Model;

use Harp\JsonStore\Test\Repo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Professional extends User {

    public function getRepo()
    {
        return Repo\Professional::get();
    }
}
