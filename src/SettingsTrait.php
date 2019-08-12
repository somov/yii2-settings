<?php /** @noinspection PhpParamsInspection */

/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:16
 */

namespace somov\settings;

use Common\ModelReflection\ModelClass;
use Common\ModelReflection\ModelProperty;
use somov\common\traits\ContainerCompositions;
use somov\settings\drivers\ArrayConfig;
use yii\base\ArrayableTrait;
use yii\base\Event;
use yii\base\StaticInstanceInterface;
use yii\helpers\ArrayHelper;

/**
 * Trait SettingsTrait
 * @package somov\settings
 *
 * @property-read object|SettingsInterface $oldSettings
 * @property-read array|string $settingsDriver
 * @method $this defaultSettings()
 * @method void beforeUpdateSettings()
 * @method void afterUpdateSettings($this)
 */
trait SettingsTrait
{

    use ArrayableTrait, ContainerCompositions;

    /**
     * @var array
     */
    private $_settingsDriver;

    /**
     * @var bool
     */
    private $_isSettingsLoaded = false;

    /**
     * @var self
     */
    private $_oldSettings;

    /**
     * @param bool $refresh
     * @return SettingsInterface|static
     */
    public static function instance($refresh = false)
    {
        /** @var SettingsInterface $instance */
        $instance = parent::instance($refresh);
        return $instance->loadSettings($refresh);
    }

    /**
     * @return DriverInterface|object
     */
    private function getDriver()
    {
        return \Yii::createObject($this->settingsDriver);
    }

    /**
     * @return array
     */
    public function settingsAttributes()
    {
        return $this->toArray();
    }

    /**
     * @return boolean
     */
    public function deleteSettings()
    {
        $result = $this->getDriver()->delete($this);
        Event::trigger($this, self::EVENT_UPDATE);
        return $result;
    }

    /**
     * @return boolean
     */
    public function updateSettings()
    {

        if ($this->hasMethod('beforeUpdateSettings')) {
            if ($this->beforeUpdateSettings() === true) {
                return true;
            }
        }

        $properties = $this->getSettingsProperties();

        foreach ($this->settingsAttributes() as $attribute => $value) {
            if (empty($properties[$attribute])) {
                continue;
            }
            $type = $properties[$attribute]->getType();

            if (isset($value) && $type->getAnnotatedType() !== 'any' && gettype($value) !== $type->getAnnotatedType()) {
                settype($value, $type->getAnnotatedType());
                $this->$attribute = $value;
            }

        }

        $result = $this->getDriver()->write($this);

        if ($result && $this->hasMethod('afterUpdateSettings')) {
            $this->afterUpdateSettings($this->getOldSettings());
        }

        Event::trigger($this, self::EVENT_UPDATE);

        return $result;
    }

    /**
     * @param bool $refresh
     * @return StaticInstanceInterface|$this
     */
    public function loadSettings($refresh = false)
    {

        if ($this->isSettingsLoaded() && !$refresh) {
            return $this;
        }

        $old = clone $this;

        $this->loadDefaults()
            ->updateSettingsAttributes($this->getDriver()->read($this))
            ->setIsSettingsLoaded(true);

        $this->setOldSettings($old);

        Event::trigger($this, self::EVENT_LOAD);

        return $this;
    }

    /**
     * @return ModelProperty[]
     */
    private function getSettingsProperties()
    {
        return $this->getCompositionFromFactory(function () {

            $properties = ArrayHelper::index((new ModelClass($this))->getProperties(), function ($item) {
                /** @var ModelProperty|mixed $item */
                return $item->getName();
            });

            return $properties;
        }, ModelProperty::class);

    }

    /**
     * @param array $attributes
     * @return SettingsTrait
     */
    protected function updateSettingsAttributes(array $attributes)
    {
        $settingsAttributes = $this->settingsAttributes();

        foreach ($attributes as $attribute => $value) {
            if (array_key_exists($attribute, $settingsAttributes)) {
                $this->$attribute = $value;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function loadDefaults()
    {
        if ($this->hasMethod('defaultSettings')) {
            if ($attributes = $this->defaultSettings()) {
                $this->updateSettingsAttributes($attributes);
            }
        }
        return $this;
    }

    /**
     * Настройки на значение по умолчанию
     * @return $this
     */
    public function reset()
    {

        $this->updateSettingsAttributes((new static())->settingsAttributes())
            ->loadDefaults();

        Event::trigger($this, self::EVENT_RESET);

        return $this;
    }

    /**
     * @param bool $update
     *
     * @deprecated use off overriding defaultSettings method
     * @return  $this
     */
    public function setDefaultSettings($update = true)
    {

        $this->reset();

        if ($update) {
            $this->updateSettings();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isSettingsLoaded()
    {
        return $this->_isSettingsLoaded;
    }

    /**
     * @param bool $isSettingsLoaded
     */
    protected function setIsSettingsLoaded($isSettingsLoaded)
    {
        $this->_isSettingsLoaded = $isSettingsLoaded;
    }

    /**
     * @return self
     */
    public function getOldSettings()
    {
        return $this->_oldSettings;
    }

    /**
     * @param self $oldSettings
     */
    protected function setOldSettings($oldSettings)
    {
        $this->_oldSettings = $oldSettings;
    }


    /**
     * @return array
     */
    public function getSettingsDriver()
    {
        if (empty($this->_settingsDriver)) {
            return [
                'class' => ArrayConfig::class
            ];
        }
        return $this->_settingsDriver;
    }

    /**
     * @param array $settingsDriver
     */
    public function setSettingsDriver($settingsDriver)
    {
        $this->_settingsDriver = $settingsDriver;
    }


}