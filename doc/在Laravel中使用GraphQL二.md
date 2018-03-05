# 在Laravel中使用GraphQL 二【修改数据】

在[上一章节](https://laravel-china.org/articles/8115/using-graphql-one-in-laravel-get-data)主要介绍了在 Laravel 中如何搭建 GraphQL 环境，如何使用 GraphQL 进行基础的查询数据还有如何使用 Graphiql 进行测试数据，本章继续探讨如何在 Laravel 中使用 GraphQL 进行**修改删除**数据，修改删除数据主要使用的是 [Mutations](http://graphql.cn/learn/queries/#mutations)

## 什么是 Mutations（变更）

> GraphQL 的大部分讨论集中在数据获取，但是任何完整的数据平台也都需要一个改变服务端数据的方法。

在 REST 中，约定了不要使用 **GET** 请求来修改数据，GraphQL 也是如此，在技术上而言，任何查询都可以看成是数据写入。因此需要建立一个约定来规范任何导致写入的操作都应该通过**变更（mutation）**来发送。

## 在 Laravel 中使用 Mutation

### 新增数据

首先在 `GraphQL` 目录下面如图创建所需 Mutation，这里分别创建了 `CreateUserMutation.php` 和 `CreateJobMutation.php` ，一个是创建用户的，一个是创建工作的

![目录结构](https://ws2.sinaimg.cn/large/006tNc79gy1foyyj9dqzvj30is0gujst.jpg)

代码部分...

```php
// CreateUserMutation.php

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

```

```php
// CreateJobMutation.php

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

```

增加了两个 Mutation 之后不要忘了在 `graphql.php` 中注册这两个 Mutation

```php
   // graphql.php
   ...
   'schemas' => [
        'default' => [
            'query' => [
                'users' => App\GraphQL\Query\UsersQuery::class,
                'jobs' => App\GraphQL\Query\JobsQuery::class,
            ],
            'mutation' => [
                'createUser' => App\GraphQL\Mutation\CreateUserMutation::class,
                'createJob' => App\GraphQL\Mutation\CreateJobMutation::class,
            ]
        ]
    ],
    ...
```

然后就可以创建用户了

![createUser](https://ws3.sinaimg.cn/large/006tNc79ly1foyyfgw7itj31460f440x.jpg)



接下来创建 Job，创建完之后查询一遍 user，即可发现 User 和 Job 都新增进去了



![queryUser](https://ws2.sinaimg.cn/large/006tNc79gy1foyyts2pr4g30hs0fr1kz.gif)

### 修改数据

下面来看看如何修改数据，原理和新增数据差不多，只不过是修改一下修改数据时候的逻辑

```php
// App/GraphQL/UpdateUserMutation.php

<?php

namespace App\GraphQL\Mutation;

use GraphQL\Type\Definition\Type;
use GraphQL;
use Folklore\GraphQL\Support\Mutation;
use App\Models\User;

class UpdateUserMutation extends Mutation
{
    protected $attributes = [
        'name' => 'UpdateUser'
    ];

    public function type()
    {
        return GraphQL::type('users');
    }

    public function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::int())
            ],
            'name' => [
                'name' => 'name',
                'type' => Type::nonNull(Type::string())
            ],
            'email' => [
                'name' => 'email',
                'type' => Type::nonNull(Type::string())
            ]
        ];
    }

    public function resolve($root, $args)
    {
        $user = User::find($args['id']); // 获取传入参数的id
        if (!$user) {
            return null;
        }
        // 通过获取到的user直接修改值
        $user->name = $args['name'];
        $user->email = $args['email'];
        $user->save();

        return $user;
    }
}
 
```

然后就可以看到效果



![update](https://ws1.sinaimg.cn/large/006tNc79gy1fp2dkq4qoqg30dc0feqv9.gif)





## 直接请求地址获取数据

如果你不想通过gui，也就是 graphiql 进行可视化测试，你也可以直接请求地址的形式进行访问，例如：

请求 [http://127.0.0.1:8000/graphql?query=query+FetchUsers{users{id,email}}](http://127.0.0.1:8000/graphql?query=query+FetchUsers{users{id,email}})

![postman](https://ws2.sinaimg.cn/large/006tNc79gy1fp2dw7rmdoj30yg0usgox.jpg)



基本上在使用 graphql 作为 API 语言后，所请求的接口只有一个，就是 `/graphql` ，除非你在自定义别的接口。

一般请求的 url 规则如下：

>  请求url:端口/graphql?query=请求方式（query还是mutation）+ 方法名{返回字段或者是请求参数}



## 总结

这篇文章简单介绍了如何在 Laravel 中使用 GraphQL 进行新增和更新数据，其实还有删除数据，但是原理都一样，相信你们都知道应该怎么写，如果对 GraphQL 不熟悉的朋友可以看我上一篇文章 [在 Laravel 中使用 GraphQL 一【获取数据】](https://laravel-china.org/articles/8115/using-graphql-one-in-laravel-get-data),在之后的文章中还会简单实现配合 [jwt-auth](https://github.com/tymondesigns/jwt-auth) 进行用户认证，在最后还会使用 Laravel +  GraphQL + Vue 开发一个博客项目，也算记录我学习 graphQL 的总结吧。



github: [https://github.com/kwen8/laravel-graphql-test](https://github.com/kwen8/laravel-graphql-test) 