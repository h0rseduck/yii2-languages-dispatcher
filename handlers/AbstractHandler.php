<?php

namespace h0rseduck\LanguagesDispatcher\handlers;

use yii\base\BaseObject;

/**
 * Class AbstractHandler is a simple handler implementation that other handlers can inherit from.
 *
 * @package h0rseduck\LanguagesDispatcher\handlers
 */
abstract class AbstractHandler extends BaseObject
{
    /**
     * Returns the list of languages detected by the handler.
     *
     * @return array list of languages.
     */
    public function getLanguages()
    {
        return [];
    }
}
