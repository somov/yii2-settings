<?php /** @noinspection PhpParamsInspection */

/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:16
 */

namespace somov\settings;

use somov\common\classInfo\ClassInfo;
use somov\common\classInfo\ClassInfoDataInterface;
use somov\common\classInfo\Property;
use somov\common\traits\ContainerCompositions;
use somov\settings\drivers\ArrayConfig;
use yii\base\ArrayableTrait;
use yii\base\Event;
use yii\base\Model;
use yii\base\StaticInstanceInterface;
use yii\base\StaticInstanceTrait;
use yii\helpers\ArrayHelper;

/**
 * Trait SettingsTrait
 * @package somov\settings
 *
 * @property-read SettingsInterface|$this $oldSettings
 * @property-read array|string $settingsDriver
 * @method $this defaultSettings()
 * @method void beforeUpdateSettings()
 * @method void afterUpdateSettings($this)
 * @method SettingsInterface[] getNestedModels
 */
trait SettingsTrait
{

    use StaticInstanceTrait, ArrayableTrait, ContainerCompositions;

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
     * @return SettingsTrait
     */
    protected function normalizeType()
    {

        $properties = $this->getSettingsProperties();

        foreach ($this->settingsAttributes() as $attribute => $value) {
            if (empty($properties[$attribute])) {
                continue;
            }

            $type = $properties[$attribute]->getType();

            if (isset($value) && $type->isSimple()) {
                settype($value, $type->getType());
                $this->$attribute = $value;
            }
        }
        return $this;
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

        $this->normalizeType();

        if ($result = $this->getDriver()->write($this)) {
            Event::trigger($this, self::EVENT_UPDATE);
        }

        if ($result && $this->hasMethod('afterUpdateSettings')) {
            $this->afterUpdateSettings($this->getOldSettings());
        }

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

        $attributes = $this->getDriver()->read($this);
        $this->loadDefaults()
            ->updateSettingsAttributes($attributes)
            //->normalizeType()
            ->setIsSettingsLoaded(true);

        $this->setOldSettings(clone $this);

        Event::trigger($this, self::EVENT_LOAD);

        return $this;
    }

    /**
     * @return Property[]
     */
    private function getSettingsProperties()
    {
        return ArrayHelper::index((new ClassInfo($this, null, [
            'processParents' => true
        ]))->getProperties(ClassInfoDataInterface::VISIBILITY_PUBLIC), 'name');
    }

    /**
     * @param array $attributes
     * @return SettingsInterface|$this
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


    /**
     * @param null $filter
     * @return array
     */
    public function defaultDifference($filter = null)
    {

        if (method_exists($this, 'defaultSettings')) {
            $attributes = $this->defaultSettings();
        } else {
            /** @var SettingsInterface $class */
            $class = static::class;
            $attributes = (new $class)->settingsAttributes();
        }

        $difference = array_diff_assoc($this->settingsAttributes(), $attributes);
        if (!empty($difference) && is_array($filter)) {
            return ArrayHelper::filter($difference, $filter);
        }

        return $difference;

    }


}