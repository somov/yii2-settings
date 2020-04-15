<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.04.20
 * Time: 12:26
 */

namespace somov\settings;


use somov\common\helpers\ArrayHelper;
use yii\base\Behavior;
use yii\base\Model;

/**
 * Class ConfiguratorBehavior
 * @package somov\settings
 *
 * Поведение автоматической настройки компонента из массива классов [[SettingsInterface]]
 *
 */
class ConfiguratorBehavior extends Behavior implements ConfiguratorBehaviorInterface
{
    /** Имя события
     * @var string|false если лож - автоматическая настройка владельца отключена
     */
    public $eventName = 'init';

    /**
     * Классы моделей с настройками
     * @var SettingsInterface[]|string
     */
    public $settingModels;

    /**
     * @var array Карта настройки свойство владельца => свойство настроек
     */
    public $attributesMap;

    /**
     * Исполнительная процедура настройки. По умолчанию Yii::configure
     * Передаются аргументы $object собственник поведения и массив с свойствами => значениями
     * @var array|callable
     */
    public $configurator = ['\Yii', 'configure'];

    /**
     * @inheritdoc
     */
    public function events()
    {
        if ($this->eventName) {
            return [
                $this->eventName => '_configure'
            ];
        }
        return [];
    }

    /**
     * @internal
     */
    public function _configure()
    {
        call_user_func($this->configurator, $this->owner, $this->configurableProperties());
    }

    /**
     * @return array
     */
    public function configurableProperties()
    {
        $attributes = [];

        $map = array_flip((array)$this->attributesMap);

        /** @var SettingsInterface $settings */
        foreach ((array)$this->settingModels as $settings) {

            $settings = $settings::instance();

            $settingsAttributes = $settings->settingsAttributes();

            if ($settings instanceof Model) {
                foreach ($map as $settingsAttribute =>  $attribute) {
                    if ($settings->hasProperty($settingsAttribute)) {
                        $settingsAttributes[$attribute] = $settings->{$settingsAttribute};
                    }
                }
            }

            foreach ($settingsAttributes as $name => $value) {
                $attribute = ArrayHelper::getValue( $map, $name, $name);
                if ($this->owner->hasProperty($attribute)) {
                    $attributes[$attribute] = $value;
                }
            }
        }

        return $attributes;
    }


}