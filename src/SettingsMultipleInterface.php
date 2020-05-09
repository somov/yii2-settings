<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 15.04.20
 * Time: 20:20
 */

namespace somov\settings;


use app\components\interfaces\SettingsModelInterface;

/**
 * Interface SettingsInterfaceMultiple
 * @package somov\settings
 */
interface SettingsMultipleInterface extends SettingsInterface
{
    /**
     * @param SettingsInterface $settings
     * @param string $index
     * @return static
     */
    //public function addItem(SettingsInterface $settings, $index = null);

    /**
     * @return static
     */
    public function clearItems();

    /**
     * @param string $index
     * @param string|array $type
     * @return SettingsInterface|null
     */
    public function getItem($index, $type = null);


    /**
     * @param  string $index
     * @return boolean
     */
    public function hasItem($index);


    /**
     * @return SettingsModelInterface
     */
    public function getOwner();
}