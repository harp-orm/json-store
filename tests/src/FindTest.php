<?php

namespace CL\LunaJsonStore\Test;

use CL\LunaJsonStore\Test\Model;
use CL\LunaJsonStore\Test\Repo;
use CL\LunaJsonStore\Find;
use CL\LunaJsonStore\Not;
use CL\LunaCore\Model\AbstractModel;
use CL\LunaCore\Model\State;

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
     * @covers CL\LunaJsonStore\Find::isConditionMatch
     * @dataProvider dataIsConditionMatch
     */
    public function testIsConditionMatch($value, $conditon, $expected)
    {
        $this->assertEquals($expected, Find::isConditionMatch($value, $conditon));
    }

    /**
     * @covers CL\LunaJsonStore\Find::__construct
     * @covers CL\LunaJsonStore\Find::getRepo
     */
    public function testConstruct()
    {
        $repo = $this->getRepo();

        $find = new Find($repo);

        $this->assertSame($repo, $find->getRepo());
    }

    /**
     * @covers CL\LunaJsonStore\Find::where
     * @covers CL\LunaJsonStore\Find::whereIn
     * @covers CL\LunaJsonStore\Find::whereNot
     * @covers CL\LunaJsonStore\Find::limit
     * @covers CL\LunaJsonStore\Find::getLimit
     * @covers CL\LunaJsonStore\Find::offset
     * @covers CL\LunaJsonStore\Find::getOffset
     * @covers CL\LunaJsonStore\Find::getConditions
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
     * @covers CL\LunaJsonStore\Find::isMatch
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
     * @covers CL\LunaJsonStore\Find::newModel
     * @dataProvider dataNewModel
     */
    public function testNewModel($properties, $repo, $expected)
    {
        $find = new Find($repo);

        $this->assertEquals($expected, $find->newModel($properties));
    }

    /**
     * @covers CL\LunaJsonStore\Find::execute
     */
    public function testExecute()
    {
        $repo = $this->getMock(
            __NAMESPACE__.'\Repo\User',
            ['getContents'],
            [__NAMESPACE__.'\Model\User', TEST_DIR.'/User.json']
        );

        $find = $this->getMock(
            'CL\LunaJsonStore\Find',
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
