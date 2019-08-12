<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.08.19
 * Time: 21:59
 */

class ActionTest extends Codeception\TestCase\Test
{


    protected function _before()
    {
        $_SERVER['REQUEST_METHOD'] = 'post';

        $_POST = [
            'TestSettingsModel' => [
                'propertyString' => 'test'
            ]
        ];

        parent::_before();
    }

    public function testBase()
    {
        $controller = new Base('base', Yii::$app);

        $controller->runAction('settings');
        $this->assertInstanceOf(TestSettingsModel::class, $controller->testModel);
    }
    
    
    public function testSections(){
        $controller = new Section('Section', Yii::$app);
        $controller->runAction('settings');
        $this->assertInstanceOf(TestSettingsModel::class, $controller->testModel);
    }

}