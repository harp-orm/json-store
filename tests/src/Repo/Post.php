<?php

namespace CL\LunaJsonStore\Test\Repo;

use CL\LunaJsonStore\Rel;
use CL\LunaJsonStore\AbstractJsonRepo;

/**
 * @author     Ivan Kerin
 * @copyright  (c) 2014 Clippings Ltd.
 * @license    http://www.opensource.org/licenses/isc-license.txt
 */
class Post extends AbstractJsonRepo {

    private static $instance;

    /**
     * @return User
     */
    public static function get()
    {
        if (! self::$instance) {
            self::$instance = new Post(
                'CL\LunaJsonStore\Test\Model\Post',
                TEST_DIR.'/Post.json'
            );
        }

        return self::$instance;
    }

    public function initialize()
    {
        $this
            ->setRels([
                new Rel\One('user', $this, Post::get()),
            ]);
    }
}
