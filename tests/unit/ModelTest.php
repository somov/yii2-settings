<?php

use somov\settings\SettingsInterface;

/**
 * Created by PhpStorm.
 * User: web
 * Date: 05.08.19
 * Time: 17:53
 */

class ModelTest extends Codeception\TestCase\Test
{

    public function testInstance()
    {
        $model = TestSettingsModel::instance();
        $this->assertInstanceOf(SettingsInterface::class, $model);
    }

    public function testUpdate()
    {
        $model  = TestSettingsModel::instance();
        $model->propertyString = 'test';
        $model->propertyInteger = '0';
        $model->propertyArray = ['a', 'b', 'c'];

        $model->propertyNonAnointed = 0.25;
        $model->propertyBoolean = 1;

        $r = $model->updateSettings();
        $this->assertTrue($r);
    }

    public function testRead(){
        $model = (new TestSettingsModel())->loadSettings();
        $this->assertSame('test', $model->propertyString);
        $this->assertSame(0, $model->propertyInteger);
        $this->assertSame(['a', 'b', 'c'], $model->propertyArray);
        $this->assertSame(true, $model->propertyBoolean);
    }

    public function testReset(){

        $model = TestSettingsModel::instance(true);
        $model->propertyDefault = 'changed';
        $model->reset();

        $this->assertSame(null, $model->propertyString);
        $this->assertSame(null, $model->propertyInteger);
        $this->assertSame(null, $model->propertyArray);
        $this->assertSame('default', $model->propertyDefault);
    }


    public function testDelete(){
        $r = (new TestSettingsModel())->deleteSettings();
        $this->assertTrue($r);
    }


}
