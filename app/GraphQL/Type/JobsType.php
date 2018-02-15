<?php
namespace App\GraphQL\Type;

use App\Models\Job;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class JobsType extends GraphQLType
{
    protected $attributes = [
        'name' => 'jobs',
        'description' => '工作',
        'model' => Job::class
    ];


    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => '工作id'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => '工作名'
            ],
            'description' => [
                'type' => Type::string(),
                'description' => '工作职责描述'
            ]
        ];
    }
}
