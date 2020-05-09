<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 15.04.20
 * Time: 20:22
 */

namespace somov\settings;


use yii\base\Model;

/**
 * @method SettingsInterface[] getNestedModels
 */
class SettingsMultipleModel extends Model implements SettingsMultipleInterface
{
    use MultipleSettingsTrait;

}