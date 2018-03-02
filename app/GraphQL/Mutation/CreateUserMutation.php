<?php

namespace App\GraphQL\Mutation;

use GraphQL\Type\Definition\Type;
use GraphQL;
use Folklore\GraphQL\Support\Mutation;
use App\Models\User;

class CreateUserMutation extends Mutation
{
    protected $attributes = [
        'name' => 'CreateUser'
    ];

    public function type()
    {
        return GraphQL::type('users');
    }

    public function args()
    {
        return [
            'name' => [
                'name' => 'name',
                'type' => Type::nonNull(Type::string())
            ],
            'email' => [
                'name' => 'email',
                'type' => Type::nonNull(Type::string())
            ],
            'password' => [
                'name' => 'password',
                'type' => Type::nonNull(Type::string())
            ]
        ];
    }

    public function resolve($root, $args)
    {
        $args['password'] = bcrypt($args['password']);
        $user = User::create($args);
        if (!$user) {
            return null;
        }
        return $user;
    }
}
