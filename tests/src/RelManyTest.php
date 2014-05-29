<?php

namespace Harp\JsonStore\Test;

use Harp\JsonStore\Rel;
use Harp\Core\Model\AbstractModel;
use Harp\Core\Model\Models;
use Harp\Core\Repo\LinkMany;

/**
 * @coversDefaultClass Harp\JsonStore\Rel\Many
 */
class RelManyTest extends AbstractTestCase
{
    public function getRepo()
    {
        return new Repo\User(
            __NAMESPACE__.'\Model\User',
            TEST_DIR.'/User.json'
        );
    }

    /**
     * @covers ::__construct
     * @covers ::getKey
     */
    public function testConstruct()
    {
        $user = Repo\User::get();
        $post = Repo\Post::get();

        $rel = new Rel\Many('test', $user, $post);

        $this->assertSame($user, $rel->getRepo());
        $this->assertSame($post, $rel->getForeignRepo());
        $this->assertSame('userId', $rel->getKey());

        $rel = new Rel\Many('test', $user, $post, ['key' => 'testId']);

        $this->assertSame($user, $rel->getRepo());
        $this->assertSame($post, $rel->getForeignRepo());
        $this->assertSame('testId', $rel->getKey());
    }

    /**
     * @covers ::areLinked
     */
    public function testAreLinked()
    {
        $rel = new Rel\Many('test', Repo\User::get(), Repo\Post::get());

        $user = new Model\User(['id' => 2]);
        $post = new Model\Post(['id' => 5]);

        $this->assertFalse($rel->areLinked($user, $post));

        $post->userId = 2;

        $this->assertTrue($rel->areLinked($user, $post));
    }

    public function dataHasForeign()
    {
        return [
            [[new Model\User(), new Model\User()], false],
            [[new Model\User(['id' => 2]), new Model\User(['id' => 3])], true],
        ];
    }

    /**
     * @covers ::hasForeign
     * @dataProvider dataHasForeign
     */
    public function testHasForeign($models, $expected)
    {
        $rel = new Rel\Many('test', Repo\User::get(), Repo\Post::get());

        $this->assertEquals($expected, $rel->hasForeign(new Models($models)));
    }

    /**
     * @covers ::loadForeign
     */
    public function testLoadForeign()
    {
        $repo = $this->getMock(
            __NAMESPACE__.'\Repo\Post',
            ['getContents'],
            [__NAMESPACE__.'\Model\Post', TEST_DIR.'/Post.json']
        );

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                3 => ['id' => 3, 'userId' => 2],
                5 => ['id' => 5, 'userId' => 2],
                8 => ['id' => 8, 'userId' => 4],
                9 => ['id' => 9, 'userId' => 8],
            ]));

        $rel = new Rel\Many('test', Repo\User::get(), $repo);

        $models = new Models([
            new Model\User(['id' => 2]),
            new Model\User(['id' => 4]),
            new Model\User(['id' => null]),
        ]);

        $result = $rel->loadForeign($models);

        $this->assertCount(3, $result);

        foreach ([0 => 3, 1 => 5, 2 => 8] as $index => $id) {
            $this->assertInstanceOf(__NAMESPACE__.'\Model\Post', $result[$index]);
            $this->assertTrue($result[$index]->isSaved());
            $this->assertEquals($id, $result[$index]->id);
        }
    }

    /**
     * @covers ::update
     */
    public function testUpdate()
    {
        $rel = new Rel\Many('test', Repo\User::get(), Repo\Post::get());

        $user = new Model\User(['id' => 2]);
        $post1 = new Model\Post(['id' => 3, 'userId' => 2]);
        $post2 = new Model\Post(['id' => 5]);

        $link = new LinkMany($rel, [$post1]);
        $link->remove($post1)->add($post2);

        $rel->update($user, $link);

        $this->assertEquals(null, $post1->userId);
        $this->assertEquals(2, $post2->userId);
    }
}
