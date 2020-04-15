<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 13.04.20
 * Time: 23:20
 */

namespace somov\settings\drivers;


use somov\settings\DriverInterface;
use somov\settings\SettingsInterface;
use yii\base\BaseObject;
use yii\db\ActiveRecord;

/**
 * Class ActiveColumn
 * @package somov\settings\drivers
 */
class ActiveColumn extends BaseObject implements DriverInterface
{
    /**
     * @var ActiveRecord
     */
    public $model;

    /**
     * @var string
     */
    public $attribute;

    /**
     * @param SettingsInterface $settings
     * @return array
     */
    public function read(SettingsInterface $settings)
    {
        $value = $this->model->{$this->attribute};

        if (is_string($value)) {
            return unserialize($value);
        }
        return [];
    }

    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function write(SettingsInterface $settings)
    {
        return $this->model->updateAttributes([
                $this->attribute => serialize($settings->settingsAttributes())
            ]) > 0;
    }

    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function delete(SettingsInterface $settings)
    {
        return $this->model->updateAttributes([
                $this->attribute => null
            ]) > 0;
    }
}