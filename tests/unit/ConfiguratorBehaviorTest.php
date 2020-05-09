<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.04.20
 * Time: 12:40
 */

use somov\settings\ConfiguratorBehavior;
use somov\settings\ConfiguratorBehaviorInterface;

class ConfiguratorBehaviorTest extends \Codeception\Test\Unit
{


    public function testConfigure()
    {

        $settings = TestSettingsModel::instance();
        $settings->propertyInteger = 7;

        /** @var Component|ConfiguratorBehaviorInterface $component */
        $component = Yii::createObject([
            'class' => Component::class,
            'as configurator' => [
                'class' => ConfiguratorBehavior::class,
                'settingModels' => TestSettingsModel::class,
                'attributesMap' => [
                    'test' => 'propertyInteger'
                ]
            ]
        ]);

        $properties = $component->configurableProperties();

        $this->assertSame(["test"=>7], $properties);

        $this->assertSame($settings->propertyInteger, $component->test);

    }

}
