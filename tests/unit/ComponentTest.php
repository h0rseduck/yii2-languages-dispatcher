<?php

namespace h0rseduck\LanguagesDispatcher\tests;

use h0rseduck\LanguagesDispatcher\Component;
use h0rseduck\LanguagesDispatcher\handlers\AbstractHandler;
use h0rseduck\LanguagesDispatcher\handlers\CookieHandler;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Application;

class ComponentTest extends AbstractUnitTest
{
    public function testBootstrap()
    {
        /**
         * @var $ld \h0rseduck\LanguagesDispatcher\Component
         */
        $this->tester->expectException(
            new InvalidConfigException(
                'The "languages" property must be an array or callable function that returns an array'
            ),
            function () {
                $this->mockWebApplication([
                    'components' => [
                        'ld' => [
                            'languages' => null,
                        ],
                    ],
                ]);
            }
        );

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                ],
            ],
        ]);
        $ld = Yii::$app->get('ld');
        $this->tester->assertSame($this->languages, $ld->languages);

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => function () {
                        return $this->languages;
                    },
                ],
            ],
        ]);
        $ld = Yii::$app->get('ld');
        $this->tester->assertSame($this->languages, $ld->languages);

        $this->tester->expectException(
            new InvalidConfigException(sprintf(
                'The handler must be an instance of "%s"',
                AbstractHandler::className()
            )),
            function () {
                $this->mockWebApplication([
                    'components' => [
                        'ld' => [
                            'handlers' => [
                                'yii\web\Request',
                            ],
                        ],
                    ],
                ]);
            }
        );

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                    'handlers' => [
                        'h0rseduck\LanguagesDispatcher\handlers\QueryParamHandler',
                        [
                            'class' => 'h0rseduck\LanguagesDispatcher\handlers\SessionHandler',
                        ],
                        new CookieHandler()
                    ]
                ],
            ],
        ]);
        $ld = Yii::$app->get('ld');
        foreach ($ld->handlers as $handler) {
            $this->tester->assertInstanceOf(AbstractHandler::className(), $handler);
        }

        $this->mockWebApplication();
        $this->tester->assertTrue(Yii::$app->hasEventHandlers(Application::EVENT_BEFORE_ACTION));

        $this->tester->expectException(
            new InvalidConfigException(
                'The "appendSetLanguageHandler" property must be a boolean'
            ),
            function () {
                $this->mockWebApplication([
                    'components' => [
                        'ld' => [
                            'languages' => $this->languages,
                            'appendSetLanguageHandler' => 'non-sense'
                        ],
                    ],
                ]);
            }
        );
    }

    public function testSetLanguage()
    {
        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                    'handlers' => [
                        [
                            'class' => 'h0rseduck\LanguagesDispatcher\handlers\DefaultLanguageHandler',
                            'language' => 'ru'
                        ],
                    ]
                ],
            ],
        ]);
        Yii::$app->trigger(Application::EVENT_BEFORE_ACTION);
        $this->tester->assertSame('ru', Yii::$app->language);

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                    'handlers' => [
                        'h0rseduck\LanguagesDispatcher\handlers\QueryParamHandler',
                        [
                            'class' => 'h0rseduck\LanguagesDispatcher\handlers\DefaultLanguageHandler',
                            'language' => 'ru'
                        ],
                    ]
                ],
            ],
        ]);
        Yii::$app->getRequest()->setQueryParams(['language' => 'en']);
        Yii::$app->trigger(Application::EVENT_BEFORE_ACTION);
        $this->tester->assertSame('en', Yii::$app->language);

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                    'handlers' => [
                        'h0rseduck\LanguagesDispatcher\handlers\QueryParamHandler',
                        [
                            'class' => 'h0rseduck\LanguagesDispatcher\handlers\DefaultLanguageHandler',
                            'language' => 'ru'
                        ],
                    ]
                ],
            ],
        ]);
        Yii::$app->getRequest()->setQueryParams(['language' => 'unknown']);
        Yii::$app->trigger(Application::EVENT_BEFORE_ACTION);
        $this->tester->assertSame('ru', Yii::$app->language);

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'handlers' => [
                        'h0rseduck\LanguagesDispatcher\handlers\SessionHandler',
                    ]
                ],
            ],
        ]);
        $this->tester->assertTrue(Yii::$app->hasEventHandlers(Component::EVENT_AFTER_SETTING_LANGUAGE));

        $this->mockWebApplication([
            'components' => [
                'ld' => [
                    'languages' => $this->languages,
                    'appendSetLanguageHandler' => false,
                    'handlers' => [
                        'h0rseduck\LanguagesDispatcher\handlers\SessionHandler',
                    ],
                ],
            ],
        ]);
        Yii::$app->trigger(Application::EVENT_BEFORE_ACTION);
        $this->tester->assertTrue(Yii::$app->hasEventHandlers(Component::EVENT_AFTER_SETTING_LANGUAGE));

        $this->mockWebApplication(
            [
                'components' => [
                    'ld' => [
                        'languages' => $this->languages,
                        'handlers' => [
                            'h0rseduck\LanguagesDispatcher\handlers\AcceptLanguageHeaderHandler',
                        ],
                    ],
                ],
            ]
        );
        Yii::$app->getRequest()->getHeaders()->add('Accept-Language', 'pl,ru,en,de');
        Yii::$app->trigger(Application::EVENT_BEFORE_ACTION);
        $this->tester->assertSame('ru', Yii::$app->language);
    }
}