<?php

namespace Harp\JsonStore\Test\Repo;

use Harp\JsonStore\Rel;
use Harp\JsonStore\AbstractJsonRepo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Post extends AbstractJsonRepo {

    public static function newInstance()
    {
        return new Post('Harp\JsonStore\Test\Model\Post', TEST_DIR.'/Post.json');
    }

    public function initialize()
    {
        $this->addRel(new Rel\One('user', $this, Post::get()));
    }
}
