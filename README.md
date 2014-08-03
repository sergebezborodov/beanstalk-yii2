#Beanstalk Worker App for Yii2

## What is it?
Queries is important part for any big project. I used Gearman for years in my past projects and when I started develop hotwork.ru I read a lot of articles about different query system. Finnaly I chosed beanstalk - It was inspired by memcache protocol, it's simple, fast and stable. Beanstalk works at my server more than three month without restarting. For rabbitmq it will be unbelievable. 

## How to install

Install it by composer, add to composer.json

```
"sergebezborodov/beanstalk": "dev-master",
```

## How to configure
For work with any query system you must create worker application. It must work permanently (at screen or supervisor) and listeting beastalk server. Client application set task to server and server call worker application for execution.

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

It's very important to close connection to database after worker completes task. Add all db connection to `'on beforeAction'` and `'on afterAction'` events.

##How to use
You must start worker with argument of tube name, for example:
`./worker import` - it will handle only tasks from import tube.
`./worker` - it will handle all tasks from all available tubes.

##Handling terminate signals
Terminate some tasks at execution will cause big problems, as solution app handle SIGINT signal (Ctrl+C) and wait for end of active task. You can disable with behavior by setting `handleSignals` property to false. PHP must be compiled with `pcntl`.
