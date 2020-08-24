<?php

namespace h0rseduck\LanguagesDispatcher\tests;

use Codeception\Test\Unit;
use yii\helpers\ArrayHelper;
use yii\web\Application;

abstract class AbstractUnitTest extends Unit
{
    /**
     * @var \h0rseduck\LanguagesDispatcher\tests\UnitTester
     */
    protected $tester;

    protected $languages = [
        'en',
        'ru',
        'de'
    ];

    protected function mockWebApplication($config = [])
    {
        new Application(ArrayHelper::merge(
            [
                'id' => 'test-app',
                'basePath' => __DIR__,
                'bootstrap' => ['ld'],
                'components' => [
                    'ld' => [
                        'class' => 'h0rseduck\LanguagesDispatcher\Component',
                    ],
                    'request' => [
                        'cookieValidationKey' => 'cookieValidationKey'
                    ]
                ]
            ],
            $config
        ));
    }
}