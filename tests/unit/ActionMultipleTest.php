<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.08.19
 * Time: 21:59
 */

class ActionMultipleTest extends Codeception\TestCase\Test
{

    private $time;
    /**
     * @inheritdoc
     */
    protected function _before()
    {
        $this->time = time();
        $_SERVER['REQUEST_METHOD'] = 'post';

        $_POST = [
            'TestSettingsModelMultiple' => [
                'propertyInteger' => $this->time,
                '0' =>['propertyString' => 'm1'],
                '1' =>['propertyString' => 'm2']
            ]
        ];

        parent::_before();
    }

    public function testBase()
    {
        $controller = new Multiple('multiple', Yii::$app);

        $controller->runAction('settings');

        $model = $controller->testModel;

        $this->assertInstanceOf(TestSettingsModelMultiple::class, $model);


        $test  = [];
        foreach ($model as  $index=>$item) {
            $test[]  = $item->propertyString;
        }

        $this->assertSame(['m1', 'm2'], $test);

        $this->assertSame($this->time, $model->propertyInteger);

    }
    


}