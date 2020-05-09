<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 18:22
 */

namespace somov\settings\drivers;

use somov\common\classes\ConfigurationArrayFile;
use somov\common\helpers\FileHelper;
use somov\settings\DriverInterface;
use somov\settings\SettingsInterface;
use yii\base\BaseObject;
use yii\helpers\Inflector;
use yii\helpers\ReplaceArrayValue;
use yii\helpers\StringHelper;

/**
 * Class ArrayConfig
 * @package somov\settings\drivers
 */
class ArrayConfig extends BaseObject implements DriverInterface
{
    /**
     * @var string
     */
    public $basePath = '@runtime/settings';

    /**
     * @var string
     */
    public $suffix = '_';

    /**
     * @var bool
     */
    public $isAddShaSuffix = true;

    /**
     * @param SettingsInterface $settings
     * @return bool|string
     */
    protected function getFileName(SettingsInterface $settings)
    {
        $class = get_class($settings);
        $file = $this->suffix . Inflector::camel2id(StringHelper::basename($class), '_');

        if ($this->isAddShaSuffix) {
            $file .= '_' . sha1($class);
        }

        $file = \Yii::getAlias($this->basePath . '/' . $file . '.php');

        FileHelper::createDirectory(dirname($file));

        return $file;
    }

    /**
     * @param SettingsInterface $settings
     * @return array
     */
    public function read(SettingsInterface $settings)
    {
        return (new ConfigurationArrayFile($this->getFileName($settings)))->asArray();
    }

    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function write(SettingsInterface $settings)
    {
        $config = (new ConfigurationArrayFile($this->getFileName($settings)))
            ->clear()
            ->mergeWith($settings->settingsAttributes())
            ->write();
        
        return $config->count() > 0;
    }

    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function delete(SettingsInterface $settings)
    {
        $fileName = $this->getFileName($settings);

        if (file_exists($fileName)) {
            return unlink($fileName);
        }

        return false;
    }
}