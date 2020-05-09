<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.04.20
 * Time: 12:34
 */

use yii\base\Event;

/**
 * Class Component
 */
class Component extends \yii\base\Component
{
    const  EVENT_INIT = 'init';

    /**
     * @var
     */
    public $test;

    public function init()
    {
        $this->trigger(self::EVENT_INIT, new Event());
    }
}