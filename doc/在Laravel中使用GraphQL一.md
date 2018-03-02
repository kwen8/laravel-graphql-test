# 在 Laravel 中使用 GraphQL

## 什么是GraphQL？

> GraphQL 是一种 API 查询语言，GraphQL 对你的 API 中的数据提供了一套易于理解的完整描述，使得客户端能够准确地获得它需要的数据，而且没有任何冗余，也让 API 更容易地随着时间推移而演进，还能用于构建强大的开发者工具。

简单来说，GraphQL 不同于REST API，REST API 请求多个资源时得载入多个 URL，而 GraphQL 可以通过一次请求就获取你应用所需的所有数据。这样一来，即使是比较慢的移动网络连接下，使用 GraphQL 的应用也能表现得足够迅速。查询方式类似下面这样子：

```json
{
    user {
    	id
    	name
        job {
			name
    		description
    	}
    }
}

// 查询得到的数据格式是：

{
  "data": {
    "users": [
      {
        "id": 1,
        "name": "kwen",
        "job": [
          {
            "name": "前端开发工程师",
            "description": "前端前端"
          }
        ]
      },
      {
        "id": 2,
        "name": "kwen1",
        "job": [
          {
            "name": "PHP开发工程师",
            "description": "PHP"
          }
        ]
      }
    ]
  }
}
```


你可以在 [这里](http://graphql.cn/) 查看更多关于 GraphQL 的信息



## 在 Laravel 中使用 GraphQL

> 以下我会用一个简单的demo来演示如何使用

### 1、安装 Laravel

```bash
$ composer global require "laravel/installer"
$ laravel new laravel-graphql-test
$ cd laravel-graphql-test
```

我这里使用的是[valet](https://d.laravel-china.org/docs/5.5/valet) 作为开发环境，详细的安装也可以到[文档](https://d.laravel-china.org/docs/5.5/installation)中查看

### 2、安装 [laravel-graphql](https://github.com/rebing/graphql-laravel) package

#### 修改composer.json

```json
{
    "require": {
    	"rebing/graphql-laravel": "~1.7"
  	}
}
```

#### 更新 composer

```bash
$ composer install
// 或者
$ composer update
```

#### 添加 service provider 

```php
// 添加到app/config/app.php
Rebing\GraphQL\GraphQLServiceProvider::class,
```

#### 并添加 facade

```
'GraphQL' => 'Rebing\GraphQL\Support\Facades\GraphQL',
```

#### 生成配置文件

```bash
$ php artisan vendor:publish --provider="Rebing\GraphQL\GraphQLServiceProvider"
```

然后就可以到 `config/graphql.php `查看配置信息了

### 3、创建数据模型

#### 生成模型和数据库表迁移文件

```bash
$ php artisan make:model Job -m

Model created successfully.
Created Migration: 2018_02_14_152840_create_jobs_table
```

#### 建立模型关系

```php
// app/User.php
...
class User extends Authenticatable 
{
    ...
    public function job()
    {
        return $this->hasMany('App\Models\Job');
    }
}
```

```php
// app/Job.php
...
class Job extends Model
{
    public function user()
    {
        return $this->belongsTo("App\Models\User");
    }
}
```

#### 修改migration

```php
// xxx_create_jobs_table.php
...
class CreateJobsTable extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
	...
}
```

#### 迁移 migration

```php
$ php artisan migrate
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table
Migrating: 2018_02_14_152840_create_jobs_table
Migrated:  2018_02_14_152840_create_jobs_table
```

###  4、创建 GraphQL 的 Query 和 Type

> GraphQL 是一个基于类型系统来执行查询的，所以需要定义好暴露的查询接口 (Query) 以及 接口的类型 (Type)
>
> Type 会帮助我们格式化查询结果的类型，一般为boolean、string、float、int等，另外还可以定义自定义类型

#### 目录结构

![GraphQL目录结构](https://ws3.sinaimg.cn/large/006tNc79gy1fohcutj3k0j30du0fyq3m.jpg)

​                                                        			图为GraphQL目录结构

#### 定义Type

```php
// app/GraphQL/Type/UsersType.php

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

```

```php
// app/GraphQL/Type/JobsType.php

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

```

#### 定义查询接口 Query

```php
// app/GraphQL/Query/UsersQuery.php

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

```

```php
// app/GraphQL/Query/JobsQuery.php

<?php

namespace App\GraphQL\Query;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;

class JobsQuery extends Query
{
    protected $attributes = [
        'name' => 'jobs'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('jobs'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::int()],
            'name' => ['name' => 'name', 'type' => Type::string()],
        ];
    }
}

```

### 5、测试结果

接下来就可以开始测试了

#### 填充测试数据

因为数据库里面什么数据都没有，所以首先需要填充测试数据，这里使用的是 [seed](https://d.laravel-china.org/docs/5.5/seeding) 进行填充

```php
// database/seeds/UsersTableSeeder.php

...
class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'kwen',
            'email' => 'email@email.com',
            'password' => bcrypt('123456'),
        ]);
        DB::table('users')->insert([
            'name' => 'kwen1',
            'email' => 'email1@email.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
```

```php
// database/seeds/JobsTableSeeder.php

...
class JobsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('jobs')->insert([
            'user_id' => 1,
            'name' => '前端开发工程师',
            'description' => '前端前端'
        ]);
        DB::table('jobs')->insert([
            'user_id' => 2,
            'name' => 'PHP开发工程师',
            'description' => 'PHP'
        ]);
    }
}
```

```php
// database/seeds/DatabaseSeeder.php

...
class DatabaseSeeder extends Seeder
{
    public function run()
    {
         $this->call(UsersTableSeeder::class);
         $this->call(JobsTableSeeder::class);
    }
}
```



修改完这两个文件之后使用 artisan 命令进行填充

```bash
$ php artisan db:seed
Seeding: UsersTableSeeder
Seeding: JobsTableSeeder
```

#### 安装测试工具

这里使用的是 GraphQL 可视化调试工具，专门针对 Laravel 的 [noh4ck/laravel-graphiql](https://github.com/noh4ck/laravel-graphiql)

1、安装 laravel-graphiql

```bash
$ composer require "noh4ck/graphiql:@dev"
```

2、打开 `config/app.php` 并添加以下代码到 `providers ` 中

```php
Graphiql\GraphiqlServiceProvider::class
```

3、发布这个包并生成 `config/graphiql.php `配置文件

```bash
$ php artisan graphiql:publish
```

#### 测试数据

运行 `php artisan serve` 然后打开[http://127.0.0.1:8000/graphql-ui](http://127.0.0.1:8000/graphql-ui) 就可以打开测试工具的界面了

![测试数据](https://ws3.sinaimg.cn/large/006tNc79gy1fohcv9x0mwj30yi0q6gnw.jpg)



如果要查询某个特定 id 或者特定参数的 user ，则可以带参数进行查询

![带参数查询](https://ws1.sinaimg.cn/large/006tNc79gy1fohcvk87u3j30ug0i2wg3.jpg)



限定 2 个用户

![限定用户的查询](https://ws3.sinaimg.cn/large/006tNc79gy1fohcw0xh1jj30y20qiacc.jpg)

所传参数的设置可以在`app/GraphQL/Query/UsersType.php` 中设置，`resolve` 方法里面就是设置参数逻辑的

## 总结

这篇文章简单地介绍了如何在 Laravel 中使用 GraphQL 作为 API 查询语言了，使用的是 [noh4ck/laravel-graphiql](https://github.com/noh4ck/laravel-graphiql) ，但是使用中觉得这个包的还不够完美，如果在生成 Type 或者 Query的时候能用 artisan 命令就好了，我已经在github 上面提了 issue 并得到相关回复，应该很快就可以使用这些功能了。

有人说传统Restful API已死，GraphQL永生，GraphQL解决的就是Restful 的缺点，但同时GraphQL也存在很多性能的问题，GraphQL 真正要完全替代 Restful API 还有很长一段路要走，让我们拭目以待吧！

往后文章中还会继续介绍这个包的更多用法，例如如何修改数据、增加数据、删除数据还有授权认证authenticated等等，第一次写文章，希望能多多支持。



本文 demo 可以到 [github](https://github.com/kwen8/laravel-graphql-test) 上查看

## 参考

[博客](https://medium.com/skyshidigital/easy-build-api-using-laravel-and-graphql-67e2c5c5e150)

[GraphQL官网](http://graphql.cn/)

