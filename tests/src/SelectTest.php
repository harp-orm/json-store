<?php

namespace CL\LunaJsonStore\Test;

use CL\EnvBackup\Env;
use CL\EnvBackup\FileParam;
use CL\LunaJsonStore\Select;
use CL\LunaCore\Model\AbstractModel;

class SelectTest extends AbstractTestCase
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
            ['10', '20', false],
            ['10', ['10', '20'], true],
            ['10', ['20'], false],
        ];
    }

    /**
     * @covers CL\LunaJsonStore\Select::isConditionMatch
     * @dataProvider dataIsConditionMatch
     */
    public function testIsConditionMatch($value, $conditon, $expected)
    {
        $this->assertEquals($expected, Select::isConditionMatch($value, $conditon));
    }

    /**
     * @covers CL\LunaJsonStore\Select::__construct
     * @covers CL\LunaJsonStore\Select::getRepo
     */
    public function testConstruct()
    {
        $repo = $this->getRepo();

        $select = new Select($repo);

        $this->assertSame($repo, $select->getRepo());
    }

    /**
     * @covers CL\LunaJsonStore\Select::where
     * @covers CL\LunaJsonStore\Select::getConditions
     */
    public function testConditions()
    {
        $select = new Select($this->getRepo());

        $this->assertEmpty($select->getConditions());

        $select
            ->where('name', '10')
            ->where('id', [8, 29]);

        $expected = [
            'name' => '10',
            'id' => [8, 29]
        ];

        $this->assertSame($expected, $select->getConditions());
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
     * @covers CL\LunaJsonStore\Select::isMatch
     * @dataProvider dataIsMatch
     */
    public function testIsMatch($properties, $expected)
    {
        $select = new Select($this->getRepo());

        $this->assertEmpty($select->getConditions());

        $select
            ->where('name', '10')
            ->where('id', [8, 29]);

        $this->assertSame($expected, $select->isMatch($properties));
    }

    public function testLoadRaw()
    {
        $repo = $this->getMock(
            __NAMESPACE__.'\Repo\User',
            ['getContents'],
            [__NAMESPACE__.'\Model\User', TEST_DIR.'/User.json']
        );

        $select = $this->getMock(
            'CL\LunaJsonStore\Select',
            ['isMatch'],
            [$repo]
        );

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                3 => ['id' => 3],
                5 => ['id' => 5],
                8 => ['id' => 8],
            ]));

        $select
            ->expects($this->exactly(3))
            ->method('isMatch')
            ->will($this->returnValueMap([
                [['id' => 3], true],
                [['id' => 5], false],
                [['id' => 8], true],
            ]));

        $result = $select->loadRaw();

        $this->assertCount(2, $result);

        $this->assertInstanceOf(__NAMESPACE__.'\Model\User', $result[0]);
        $this->assertTrue($result[0]->isPersisted());
        $this->assertEquals(3, $result[0]->id);

        $this->assertInstanceOf(__NAMESPACE__.'\Model\User', $result[1]);
        $this->assertTrue($result[1]->isPersisted());
        $this->assertEquals(8, $result[1]->id);
    }

    public function testLoad()
    {
        $repo = Repo\User::get();
        $repo->getIdentityMap()->clear();

        $select = $this->getMock(
            'CL\LunaJsonStore\Select',
            ['loadRaw'],
            [$repo]
        );

        $model1 = new Model\User(['id' => 1], AbstractModel::PERSISTED);
        $model2 = new Model\User(['id' => 2], AbstractModel::PERSISTED);
        $model3 = new Model\User(['id' => 1], AbstractModel::PERSISTED);
        $model4 = new Model\User(['id' => 2], AbstractModel::PERSISTED);

        $select
            ->expects($this->exactly(2))
            ->method('loadRaw')
            ->will($this->onConsecutiveCalls(
                [$model1, $model2],
                [$model3, $model4]
            ));

        $result = $select->load();

        $this->assertSame([$model1, $model2], $result);

        $result = $select->load();

        $this->assertSame([$model1, $model2], $result);
    }
}
