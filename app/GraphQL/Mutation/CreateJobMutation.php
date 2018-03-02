<?php

namespace App\GraphQL\Mutation;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL;
use Folklore\GraphQL\Support\Mutation;
use App\Models\Job;

class CreateJobMutation extends Mutation
{
    protected $attributes = [
        'name' => 'CreateJob'
    ];

    public function type()
    {
        return GraphQL::type('jobs');
    }

    public function args()
    {
        return [
            'userId' => [
                'name' => 'userId',
                'type' => Type::id()
            ],
            'name' => [
                'name' => 'name',
                'type' => Type::nonNull(Type::string())
            ],
            'description' => [
                'name' => 'description',
                'type' => Type::nonNull(Type::string())
            ]
        ];
    }

    public function resolve($root, $args)
    {
        $job = new Job([
            'name' => $args['name'],
            'description' => $args['description'],
        ]);
        $user = User::find($args['userId']);
        if (!$user) return null;
        $user->job()->save($job);
        return $job;
    }
}
