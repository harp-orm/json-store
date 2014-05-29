<?php

namespace Harp\JsonStore\Test;

use Harp\JsonStore\Test\Model;
use Harp\JsonStore\Test\Repo;
use Harp\JsonStore\Find;
use Harp\JsonStore\Not;
use Harp\Core\Model\AbstractModel;
use Harp\Core\Model\State;

/**
 * @coversDefaultClass Harp\JsonStore\Find
 */
class FindTest extends AbstractTestCase
{
    public function getRepo()
    {
        return new Repo\User(
            __NAMESPACE__.'\Model\User',
            TEST_DIR.'/User.json'
        );
    }

    public function dataIsConditionMatch()
    {
        return [
            ['10', '10', true],
            ['10', new Not('10'), false],
            ['10', new Not('14'), true],
            ['10', '20', false],
            ['10', ['10', '20'], true],
            ['10', ['20'], false],
        ];
    }

    /**
     * @covers ::isConditionMatch
     * @dataProvider dataIsConditionMatch
     */
    public function testIsConditionMatch($value, $conditon, $expected)
    {
        $this->assertEquals($expected, Find::isConditionMatch($value, $conditon));
    }

    /**
     * @covers ::__construct
     * @covers ::getRepo
     */
    public function testConstruct()
    {
        $repo = $this->getRepo();

        $find = new Find($repo);

        $this->assertSame($repo, $find->getRepo());
    }

    /**
     * @covers ::where
     * @covers ::whereIn
     * @covers ::whereNot
     * @covers ::limit
     * @covers ::getLimit
     * @covers ::offset
     * @covers ::getOffset
     * @covers ::getConditions
     * @covers ::clearWhere
     * @covers ::clearLimit
     * @covers ::clearOffset
     */
    public function testConditions()
    {
        $find = new Find($this->getRepo());

        $this->assertEmpty($find->getConditions());

        $find
            ->where('name', '10')
            ->whereIn('id', [8, 29])
            ->whereNot('test', '21')
            ->limit(9)
            ->offset(3);

        $expected = [
            'name' => '10',
            'id' => [8, 29],
            'test' => new Not('21'),
        ];

        $this->assertEquals($expected, $find->getConditions());
        $this->assertEquals(9, $find->getLimit());
        $this->assertEquals(3, $find->getOffset());

        $find->clearWhere();
        $this->assertEmpty($find->getConditions());

        $find->clearLimit();
        $this->assertEmpty($find->getLimit());

        $find->clearOffset();
        $this->assertEmpty($find->getOffset());
    }

    public function dataIsMatch()
    {
        return [
            [['id' => 8, 'name' => '10'], true],
            [['id' => 29, 'name' => '10'], true],
            [['id' => 8, 'test' => '10'], false],
            [['id' => 9, 'name' => '10'], false],
        ];
    }

    /**
     * @covers ::isMatch
     * @dataProvider dataIsMatch
     */
    public function testIsMatch($properties, $expected)
    {
        $find = new Find($this->getRepo());

        $this->assertEmpty($find->getConditions());

        $find
            ->where('name', '10')
            ->where('id', [8, 29]);

        $this->assertSame($expected, $find->isMatch($properties));
    }

    public function dataNewModel()
    {
        return [
            [
                ['id' => 8, 'name' => '10', 'class' => __NAMESPACE__.'\Model\User'],
                Repo\User::get(),
                new Model\User(['id' => 8, 'name' => '10'], State::SAVED)
            ],
            [
                ['id' => 29, 'name' => '10', 'class' => __NAMESPACE__.'\Model\Professional'],
                Repo\User::get(),
                new Model\Professional(['id' => 29, 'name' => '10'], State::SAVED)
            ],
            [
                ['id' => 4],
                Repo\Post::get(),
                new Model\Post(['id' => 4], State::SAVED),
            ],
        ];
    }

    /**
     * @covers ::newModel
     * @dataProvider dataNewModel
     */
    public function testNewModel($properties, $repo, $expected)
    {
        $find = new Find($repo);

        $this->assertEquals($expected, $find->newModel($properties));
    }

    /**
     * @covers ::execute
     */
    public function testExecute()
    {
        $repo = $this->getMock(
            __NAMESPACE__.'\Repo\User',
            ['getContents'],
            [__NAMESPACE__.'\Model\User', TEST_DIR.'/User.json']
        );

        $find = $this->getMock(
            'Harp\JsonStore\Find',
            ['isMatch'],
            [$repo]
        );

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                3 => ['id' => 3, 'class' => __NAMESPACE__.'\Model\User'],
                5 => ['id' => 5, 'class' => __NAMESPACE__.'\Model\User'],
                8 => ['id' => 8, 'class' => __NAMESPACE__.'\Model\User'],
            ]));

        $find
            ->expects($this->exactly(3))
            ->method('isMatch')
            ->will($this->returnValueMap([
                [['id' => 3, 'class' => __NAMESPACE__.'\Model\User'], true],
                [['id' => 5, 'class' => __NAMESPACE__.'\Model\User'], false],
                [['id' => 8, 'class' => __NAMESPACE__.'\Model\User'], true],
            ]));

        $result = $find->execute();

        $this->assertCount(2, $result);

        $this->assertInstanceOf(__NAMESPACE__.'\Model\User', $result[0]);
        $this->assertTrue($result[0]->isSaved());
        $this->assertEquals(3, $result[0]->id);

        $this->assertInstanceOf(__NAMESPACE__.'\Model\User', $result[1]);
        $this->assertTrue($result[1]->isSaved());
        $this->assertEquals(8, $result[1]->id);
    }
}
