<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 15.04.20
 * Time: 16:30
 */

namespace somov\settings;


use ArrayIterator;
use somov\common\helpers\ArrayHelper;
use yii\base\Event;
use yii\base\NotSupportedException;

/**
 * Trait MultipleSettingsTrait
 * @package somov\settings
 */
trait MultipleSettingsTrait
{
    use SettingsTrait {
        settingsAttributes as parentSettingsAttributes;
        updateSettings as parentUpdateSettings;
    }

    /**
     * @var SettingsInterface[]
     */
    private $_items;

    /**
     * @var SettingsInterface
     */
    private $_owner;

    /**
     * @return array
     */
    public function settingsAttributes()
    {
        $attributes = array_diff_key($this->parentSettingsAttributes(), ['items']);

        if (isset($this->_items)) {
            return array_merge(
                $attributes,
                ['items' => array_map(function ($item) {
                    /** @var static $item */
                    $item->normalizeType();
                    return [
                        'class' => get_class($item),
                        'attributes' => $item->settingsAttributes()
                    ];
                }, $this->_items)]
            );

        }
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function updateSettings()
    {
        if (empty($this->_owner)) {
            return $this->parentUpdateSettings();
        }
        return $this->_owner->updateSettings();
    }

    /**
     * @return SettingsInterface
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * @param bool $refresh
     * @return $this
     */
    public function loadSettings($refresh = false)
    {

        if ($this->isSettingsLoaded() && $refresh === false) {
            return $this;
        }

        $attributes = $this->getDriver()->read($this);

        if ($items = ArrayHelper::remove($attributes, 'items', false)) {

            $this->_items = array_map(function ($item) {
                /** @var static $model */
                $model = \Yii::createObject($item['class']);
                $model->updateSettingsAttributes($item['attributes']);
                $model->_owner = $this;
                return $model;
            }, $items);
        }

        $this->loadDefaults();
        $this->updateSettingsAttributes($attributes);

        $this->setIsSettingsLoaded(true);

        Event::trigger($this, self::EVENT_LOAD);

        return $this;

    }

    /**
     * @param SettingsInterface|object $settings
     * @param string $index
     * @return MultipleSettingsTrait
     */
    public function addItem(SettingsInterface $settings, $index = null)
    {
        $settings->_owner = $this;

        if (isset($index)) {
            $this->_items[$index] = $settings;
            return $this;
        }
        $this->_items[] = $settings;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearItems()
    {
        $this->_items = null;
        return $this;
    }

    /**
     * @param string $index
     * @param null|string|callable $type
     * @return SettingsInterface|object
     */
    public function getItem($index, $type = null)
    {

        if ($this->hasItem($index)) {
            return $this->_items[$index];
        }

        if (isset($this->_owner)) {
            return $this;
        }

        if (empty($type)) {
            $type = get_class($this);
        }

        $item = \Yii::createObject($type);
        $item->loadDefaults();

        $this->addItem($item, $index);

        return $item;
    }

    /**
     * @param $index
     * @return bool
     */
    public function hasItem($index)
    {
        return $this->offsetExists($index);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator(isset($this->_items) ? $this->_items : []);
    }


    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->_items[$offset]);
    }

    /**
     * @param mixed $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset)
    {
        return $this->_items[$offset];
    }

    /**
     * @param int $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset, $item)
    {
        throw new NotSupportedException('');
    }

    /**
     * Sets the element value at the specified offset to null.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `unset($model[$offset])`.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset)
    {
        throw new NotSupportedException('');
    }
}