<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 15.04.20
 * Time: 16:15
 */


class MultipleTest extends \Codeception\Test\Unit
{

    public function testWrite()
    {
        $model = new TestSettingsModelMultiple();

        $model->addItem(new TestSettingsModelMultiple(
            [
                'propertyString' => 'test1'
            ]
        ));

        $model->addItem(new TestSettingsModelMultiple(
            [
                'propertyString' => 'test2'
            ]
        ));

        $model->updateSettings();

        $models = $model->loadSettings();


        //$models = TestSettingsModelMultiple::instance();

        /** @var TestSettingsModelMultiple $m */
        $models[0]->propertyString = 'multiple';

        $models[0]->updateSettings();

    }

}