<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.08.19
 * Time: 22:08
 */

class Multiple extends \yii\web\Controller
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
                'onSuccess' => [$this, 'testSuccess'],
                'sections' => [
                    [
                        'model' => TestSettingsModelMultiple::class,
                        'viewName' => '@app/tests/views/base',
                    ]
                ],
                'isPartialRenderSections' => true
            ]
        ];
    }
    
    public function testSuccess(array $models)
    {
        $this->testModel = reset($models);
    }

}