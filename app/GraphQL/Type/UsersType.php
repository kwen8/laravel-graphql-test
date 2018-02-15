<?php
namespace App\GraphQL\Type;

use App\Models\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class UsersType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Users',
        'description' => '用户',
        'model' => User::class
    ];

    /**
     * 定义返回的字段接口
     * @return array
     */
    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => '用户id'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => '用户名'
            ],
            'email' => [
                'type' => Type::string(),
                'description' => '用户的email'
            ],
            'job' => [
                'type' => Type::listOf(GraphQL::type('jobs')),
                'description' => '用户的工作字段'
            ]
        ];
    }
}
