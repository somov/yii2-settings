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
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class ActiveColumnSettings
 * @package somov\settings
 */
trait ActiveColumnSettingsTrait
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