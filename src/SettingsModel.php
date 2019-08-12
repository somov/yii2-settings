<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:23
 */

namespace somov\settings;


use yii\base\Model;

/**
 * Class SettingsModel
 * @package somov\settings
 *
 * @method SettingsInterface[] getNestedModels
 */
class SettingsModel extends Model implements SettingsInterface
{
    use SettingsTrait;

}