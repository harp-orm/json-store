<?php

namespace CL\LunaJsonStore\Test;

use CL\EnvBackup\Env;
use CL\EnvBackup\FileParam;
use CL\LunaJsonStore\AbstractJsonRepo;

class IntegrationTest extends AbstractTestCase
{
    private $env;

    public function getEnv()
    {
        return $this->env;
    }

    public function setUp()
    {
        parent::setUp();

        $this->env = new Env([
            new FileParam(TEST_DIR.'/Address.json', '{
                "1": {
                    "id": 1,
                    "name": null,
                    "zipCode": "1000",
                    "location": "test location"
                }
            }'),
            new FileParam(TEST_DIR.'/Post.json', '{
                "1": {
                    "id": 1,
                    "name": "post 1",
                    "body": "my post 1",
                    "userId": 1
                },
                "2": {
                    "id": 2,
                    "name": "post 2",
                    "body": "my post 2",
                    "userId": 1
                }
            }'),
            new FileParam(TEST_DIR.'/User.json', '{
                "1": {
                    "id": 1,
                    "name": "name",
                    "password": null,
                    "addressId": 1,
                    "isBlocked": true
                }
            }'),
            new FileParam(TEST_DIR.'/BlogPost.json', '{}'),
        ]);

        $this->env->apply();

        Repo\User::get()->getIdentityMap()->clear();
        Repo\Address::get()->getIdentityMap()->clear();
        Repo\Post::get()->getIdentityMap()->clear();
    }

    public function tearDown()
    {
        if ($this->env) {
            $this->env->restore();
        }
    }

    public function testPersist()
    {
        $user1 = Repo\User::get()->find(1);

        $user1->name = 'changed name';

        $user2 = new Model\User(['name' => 'new name', 'password' => 'test']);
        $user2
            ->setAddress(new Model\Address(['location' => 'here']))
            ->getPosts()
                ->add(new Model\Post(['name' => 'post name', 'body' => 'some body']))
                ->add(new Model\Post(['name' => 'news', 'body' => 'some other body']));

        $address = new Model\Address(['name' => 'new name', 'location' => 'new location']);

        Repo\User::get()
            ->persist($user1)
            ->persist($user2);

        Repo\Address::get()->persist($address);

        $addresses = Repo\Address::get()->findAll()->load();

        $this->assertCount(3, $addresses);

        $posts = Repo\Post::get()->findAll()->where('userId', 1)->load();

        $this->assertCount(2, $posts);

        $expectedAddressContent = [
            1 => [
                'id' => 1,
                'name' => null,
                'location' => 'test location',
                'zipCode' => '1000',
            ],
            2 => [
                'id' => 2,
                'name' => null,
                'location' => 'here',
                'zipCode' => null,
            ],
            3 => [
                'id' => 3,
                'name' => 'new name',
                'location' => 'new location',
                'zipCode' => null,
            ],
        ];

        $this->assertEquals($expectedAddressContent, Repo\Address::get()->getContents());

        $expectedPostContent = [
            1 => [
                'id' => 1,
                'name' => 'post 1',
                'body' => 'my post 1',
                'userId' => 1,
            ],
            2 => [
                'id' => 2,
                'name' => 'post 2',
                'body' => 'my post 2',
                'userId' => 1,
            ],
            3 => [
                'id' => 3,
                'name' => 'post name',
                'body' => 'some body',
                'userId' => 2,
            ],
            4 => [
                'id' => 4,
                'name' => 'news',
                'body' => 'some other body',
                'userId' => 2,
            ],
        ];

        $this->assertEquals($expectedPostContent, Repo\Post::get()->getContents());

        $expectedUserContent = [
            1 => [
                'id' => 1,
                'name' => 'changed name',
                'password' => null,
                'addressId' => 1,
                'isBlocked' => true,
            ],
            2 => [
                'id' => 2,
                'name' => 'new name',
                'password' => 'test',
                'addressId' => 2,
                'isBlocked' => false,
            ],
        ];

        $this->assertEquals($expectedUserContent, Repo\User::get()->getContents());
    }
}
