<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 13.04.20
 * Time: 23:17
 */

namespace somov\settings;


use somov\settings\drivers\ActiveColumn;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\StaticInstanceTrait;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ActiveColumnSettings
 * @package somov\settings
 */
class ActiveColumnSettings extends SettingsModel
{

    /**
     * @var  ActiveRecord
     */
    private $_record;

    /**
     * @var string
     */
    private $_attribute;

    /**
     * Mp4Settings constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->_record = ArrayHelper::remove($config, 'record');
        $this->_attribute = ArrayHelper::remove($config, 'attribute');

        if (empty($this->_record) || empty($this->_attribute)) {
            throw new InvalidCallException('Property record and attribute required');
        }


        parent::__construct($config);

    }


    /**
     * @param bool $refresh
     * @return void
     */
    public static function instance($refresh = false)
    {
        throw new InvalidCallException('Static instance method not supported ' . static::class);
    }

    /**
     * @return array
     */
    public function getSettingsDriver()
    {
        return [
            'class' => ActiveColumn::class,
            'model' => $this->_record,
            'attribute' => $this->_attribute
        ];
    }

}