<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.08.19
 * Time: 22:08
 */

class Base extends \yii\web\Controller
{

    /**
     * @var TestSettingsModel
     */
    public $testModel;

    public function actions()
    {
        return [
            'settings' => [
                'class' => \somov\settings\Action::class,
                'modelClass' => TestSettingsModel::class,
                'viewName' => '@app/tests/views/base',
                'onSuccess' => [$this, 'testSuccess']
            ]
        ];
    }


    public function testSuccess(array $models)
    {
        $this->testModel = reset($models);
    }

}