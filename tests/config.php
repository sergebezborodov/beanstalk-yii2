<?php


return [
    'id' => 'beanstalk-app',
    'basePath' => __DIR__,
    'controllerNamespace' => 'app\controllers',

    'components' => [
        'router' => [
            'class' => '\sergebezborodov\beanstalk\Router',
            'routes' => [
                'test' => 'worker/test',
            ],
        ],
        'beanstalk' => ['class' => '\sergebezborodov\beanstalk\Beanstalk'],
    ],
];