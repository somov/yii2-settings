<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:37
 */

class TestSettingsModel extends \somov\settings\SettingsModel
{
    /**
     * @var string
     */
    public $propertyString;

    /**
     * @var integer
     */
    public $propertyInteger;

    /**
     * @var array
     */
    public $propertyArray;

    /**
     * @var string
     */
    public $propertyDefault = 'default';


    public $propertyNonAnointed;

    /**
     * @var boolean
     */
    public $propertyBoolean;


    public function rules()
    {
        return [
            [['propertyString', 'propertyBoolean', 'propertyDefault', 'propertyInteger'], 'safe']
        ];
    }
}