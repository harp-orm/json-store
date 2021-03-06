<?php

namespace Harp\JsonStore\Test;

use CL\EnvBackup\Env;
use CL\EnvBackup\FileParam;
use Harp\JsonStore\AbstractJsonRepo;
use Harp\Core\Model\AbstractModel;
use Harp\Core\Model\Models;
use Harp\JsonStore\Test\Repo;
use Harp\JsonStore\Test\Model;
use InvalidArgumentException;

class AbstractJsonRepoTest extends AbstractTestCase
{
    public function getRepo()
    {
        return $this->getMock(
            __NAMESPACE__.'\Repo\User',
            ['setContents', 'getContents'],
            [__NAMESPACE__.'\Model\User', TEST_DIR.'/User.json']
        );
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::__construct
     * @expectedException InvalidArgumentException
     */
    public function testConstructWrongFile()
    {
        $repo = new Repo\User(__NAMESPACE__.'\Model\User', '/test/something else');
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::__construct
     * @covers Harp\JsonStore\AbstractJsonRepo::getFile
     */
    public function testConstructNormal()
    {

        $repo = new Repo\User(__NAMESPACE__.'\Model\User', TEST_DIR.'/test.json');

        $this->assertEquals(TEST_DIR.'/test.json', $repo->getFile());
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::findAll
     */
    public function testFindAll()
    {
        $repo = $this->getRepo();

        $result = $repo->findAll();

        $this->assertInstanceOf('Harp\JsonStore\Find', $result);
        $this->assertSame($repo, $result->getRepo());
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::getContents
     * @covers Harp\JsonStore\AbstractJsonRepo::setContents
     */
    public function testContents()
    {
        $env = new Env([
            new FileParam(TEST_DIR.'/User.json', '{"1":{"id":"1","name":"name 1"}}')
        ]);
        $env->apply();

        $repo = new Repo\User(__NAMESPACE__.'\Model\User', TEST_DIR.'/User.json');

        $contents = $repo->getContents();

        $expected = [
            1 => [
                'id' => '1',
                'name' => 'name 1',
            ]
        ];

        $this->assertEquals($expected, $contents);

        $contents = [
            5 => [
                'id' => '5',
                'name' => 'name 5',
            ]
        ];

        $repo->setContents($contents);

        $result = json_decode(file_get_contents($repo->getFile()), true);

        $this->assertEquals($contents, $result);

        $env->restore();
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::update
     */
    public function testUpdate()
    {
        $repo = $this->getRepo();

        $model3 = new Model\User([
            'id' => 3,
            'name' => 'test 3',
            'password' => null,
            'addressId' => null,
            'isBlocked' => false,
            'class' => __NAMESPACE__.'\Model\User',
        ]);

        $model5 = new Model\User([
            'id' => 5,
            'name' => 'test 5',
            'password' => null,
            'addressId' => null,
            'isBlocked' => true,
            'class' => __NAMESPACE__.'\Model\User',
        ]);

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                3 => $model3->getProperties(),
                5 => $model5->getProperties(),
            ]));

        $model3->name = 'changed';
        $model3->isBlocked = true;

        $model5->password = 'pass';
        $model5->name = '200';

        $repo
            ->expects($this->once())
            ->method('setContents')
            ->with($this->equalTo([
                3 => [
                    'id' => 3,
                    'name' => 'changed',
                    'password' => null,
                    'addressId' => null,
                    'isBlocked' => true,
                    'class' => __NAMESPACE__.'\Model\User'
                ],
                5 => [
                    'id' => 5,
                    'name' => '200',
                    'password' => 'pass',
                    'addressId' => null,
                    'isBlocked' => true,
                    'class' => __NAMESPACE__.'\Model\User'
                ],
            ]));

        $repo->update(new Models([$model3, $model5]));
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::delete
     */
    public function testDelete()
    {
        $repo = $this->getRepo();

        $model3 = new Model\User(['id' => 3]);

        $model5 = new Model\User(['id' => 5]);

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                3 => $model3->getProperties(),
                5 => $model5->getProperties(),
            ]));

        $repo
            ->expects($this->once())
            ->method('setContents')
            ->with($this->equalTo([
                3 => $model3->getProperties(),
            ]));

        $repo->delete(new Models([$model5]));
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::insert
     */
    public function testInsertEmpty()
    {
        $repo = $this->getRepo();

        $model3 = new Model\User(['name' => 'test 3']);

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([]));

        $repo
            ->expects($this->once())
            ->method('setContents')
            ->with($this->equalTo([
                1 => [
                    'id' => 1,
                    'name' => 'test 3',
                    'password' => null,
                    'addressId' => null,
                    'isBlocked' => false,
                    'class' => __NAMESPACE__.'\Model\User',
                ],
            ]));

        $repo->insert(new Models([$model3]));
    }

    /**
     * @covers Harp\JsonStore\AbstractJsonRepo::insert
     */
    public function testInsertNotEmpty()
    {
        $repo = $this->getRepo();

        $model3 = new Model\User(['name' => 'test 3']);

        $repo
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue([
                5 => [
                    'id' => 5,
                    'name' => '200',
                    'password' => 'pass',
                    'addressId' => null,
                    'isBlocked' => true,
                    'class' => __NAMESPACE__.'\Model\User',
                ],
            ]));

        $repo
            ->expects($this->once())
            ->method('setContents')
            ->with($this->equalTo([
                5 => [
                    'id' => 5,
                    'name' => '200',
                    'password' => 'pass',
                    'addressId' => null,
                    'isBlocked' => true,
                    'class' => __NAMESPACE__.'\Model\User'
                ],
                6 => [
                    'id' => 6,
                    'name' => 'test 3',
                    'password' => null,
                    'addressId' => null,
                    'isBlocked' => false,
                    'class' => __NAMESPACE__.'\Model\User'
                ],
            ]));

        $repo->insert(new Models([$model3]));
    }
}
