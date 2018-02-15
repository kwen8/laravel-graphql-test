<?php

namespace App\GraphQL\Query;

use GraphQL;
use App\Models\User;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class UsersQuery extends Query
{
    protected $attributes = [
        'name' => 'users'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('users'));
    }

    /**
     * 接收参数的类型定义
     * @return array
     */
    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'email' => ['name' => 'email', 'type' => Type::string()],
            'limit' => ['name' => 'limit', 'type' => Type::int()],
        ];
    }

    /**
     * @param $root
     * @param $args 传入参数
     *
     * 处理请求的逻辑
     * @return mixed
     */
    public function resolve($root, $args)
    {
        $user = new User;

        if(isset($args['limit']) ) {
            $user =  $user->limit($args['limit']);
        }

        if(isset($args['id']))
        {
            $user = $user->where('id' , $args['id']);
        }

        if(isset($args['email']))
        {
            $user = $user->where('email', $args['email']);
        }

        return $user->get();
    }
}
