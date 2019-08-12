<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 18:23
 */

namespace somov\settings;


interface DriverInterface
{
    /**
     * @param SettingsInterface $settings
     * @return array
     */
    public function read(SettingsInterface $settings);

    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function write(SettingsInterface $settings);


    /**
     * @param SettingsInterface $settings
     * @return bool
     */
    public function delete(SettingsInterface $settings);
}