<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:06
 */

namespace somov\settings;


use yii\base\StaticInstanceInterface;

/**
 * Interface SettingsInterface
 * @package somov\settings
 *
 *
 * @method SettingsInterface|$this defaultSettings()
 * @method void beforeUpdateSettings()
 * @method void afterUpdateSettings($this)
 * @method SettingsInterface|$this reset()
 * @method SettingsInterface[] getNestedModels
 */
interface SettingsInterface extends StaticInstanceInterface
{

    const EVENT_LOAD = 'loadSettings';
    const EVENT_UPDATE = 'updateSettings';
    const EVENT_DELETE = 'deleteSettings';
    const EVENT_RESET = 'deleteSettings';

    /**
     * @return array
     */
    public function settingsAttributes();

    /**
     * @return boolean
     */
    public function deleteSettings();

    /**
     * @return boolean
     */
    public function updateSettings();

    /**
     * @param bool $refresh
     * @return SettingsInterface|$this
     */
    public function loadSettings($refresh = false);


}