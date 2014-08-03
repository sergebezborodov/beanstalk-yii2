#Beanstalk Worker App for Yii2

## What is it?
Queries is important part for big project. I used Gearman for years in my past projects and when I started develop hotwork.ru I read a lot of articles about different query system. Finnaly I chosed beanstalk - It was inspired by memcache protocol, it's simple, fast and stable. Beanstalk works at my server more than three month without restarting. For rabbitmq it will be unbelievable. 

## How to install?

Install it by composer, add to composer.json

```
"sergebezborodov/beanstalk": "dev-master",
```

## How to use?
Create worker file, same as console:

```php
#!/usr/bin/env php
<?php

$shared = require '...'; // load shared config of your app
$worker = require '..../worker.php'; // worker app config

$config = yii\helpers\ArrayHelper::merge($shared, $worker);

$application = new sergebezborodov\beanstalk\Application($config);
return $application->run();
```

Worker config example:
```php
<?php
/**
 * Config for beanstalk workers
 */
return [
    'on beforeAction' => function () {
        Yii::$app->db->open();
    },
    'on afterAction' => function () {
        Yii::$app->db->close();
    },

    'exitOnDbException' => true, // sometimes db gone away, it will be good to restart worker

    'components' => [
        'router' => [
            'class' => 'sergebezborodov\beanstalk\Router',
            'routes' => [ // routes list, example
                'import' => 'import/worker/import',
                'mail'   => 'mail/worker/mail',
            ],
        ],
    ],
];
```
