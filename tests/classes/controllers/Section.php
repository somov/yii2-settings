<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 11.08.19
 * Time: 22:08
 */

class Section extends \yii\web\Controller
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
                        'model' => TestSettingsModel::class,
                        'viewName' => '@app/tests/views/base',
                    ]
                ],
                'isPartialRenderSections' => true
            ]
        ];
    }


    public function sectionsSettings()
    {
        return [
            [
                'model' => TestSettingsModel::class,
                'viewName' => '@app/tests/views/base',
            ]
        ];
    }

    public function testSuccess(array $models)
    {
        $this->testModel = reset($models);
    }

}