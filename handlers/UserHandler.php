<?php

namespace h0rseduck\LanguagesDispatcher\handlers;

use h0rseduck\LanguagesDispatcher\Component;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\web\User;

/**
 * Class UserHandler handles the languages by an authenticated user.
 *
 * @package h0rseduck\LanguagesDispatcher\handlers
 */
class UserHandler extends AbstractHandler
{
    /**
     * @var string the User component ID.
     */
    public $user = 'user';

    /**
     * @var string an attribute that contains a language.
     */
    public $languageAttribute = 'language_code';

    /**
     * @var User
     */
    protected $component;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->component = Yii::$app->get($this->user, false);
        if (!$this->component instanceof User) {
            throw new InvalidConfigException(sprintf(
                'The component with the specified ID "%s" must be an instance of "%s"',
                $this->user,
                User::className()
            ));
        }
        $identityClass = $this->component->identityClass;
        if ($identityClass !== null) {
            if (!is_subclass_of($identityClass, ActiveRecord::className())) {
                throw new InvalidConfigException(sprintf(
                    'The "%s::getIdentity()" method must return an instance of "%s"',
                    $this->component::className(),
                    ActiveRecord::className()
                ));
            }
            Yii::$app->on(Component::EVENT_AFTER_SETTING_LANGUAGE, [$this, 'saveAttribute']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getLanguages()
    {
        /** @var null|ActiveRecord $identity */
        $identity = $this->component->getIdentity();
        if ($identity === null || !$identity->hasProperty($this->languageAttribute)) {
            return [];
        }
        return [$identity->{$this->languageAttribute}];
    }

    /**
     * Saves the language attribute.
     */
    public function saveAttribute()
    {
        $language = current($this->getLanguages());
        $identity = $this->component->getIdentity();
        if ($language !== Yii::$app->language && $identity !== null && $identity->hasProperty($this->languageAttribute)) {
            $identity->{$this->languageAttribute} = Yii::$app->language;
            $identity->save(true, [$this->languageAttribute]);
        }
    }
}