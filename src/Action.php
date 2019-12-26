<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 10.08.19
 * Time: 14:53
 */

namespace somov\settings;


use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class Action
 * @package somov\settings
 */
class Action extends \yii\base\Action
{
    /**
     * @var string|SettingsInterface
     */
    public $modelClass;

    public $viewName = 'settings';

    /**
     * @var string|\Closure
     */
    public $viewContent;

    /**
     * @var string|array
     */
    public $redirectRoute;


    /**
     * @var array
     * (
     * 'model' => 'TestSettingsModel',
     * 'viewName' => 'string|null',
     * 'viewContent' => string|null,
     * )
     */
    public $sections = [];

    /**
     * @var  string|boolean
     */
    public $anchor;

    /**
     * @var callable|null
     */
    public $onSuccess;

    /**
     * @var callable|null
     */
    public $onError;

    /**
     * @var bool formation of presentation content in parts
     */
    public $isPartialRenderSections = false;


    /**
     * @return \Closure|mixed|string
     */
    public function run()
    {

        $sections = $this->controllerSections();
        $models = [];

        if (isset($this->modelClass)) {
            array_unshift($sections, [
                'model' => $this->modelClass,
                'viewName' => $this->viewName,
                'viewContent' => $this->viewContent
            ]);
        }

        if (empty($sections)) {
            throw new InvalidConfigException('Properties $model or $sections required ');
        }

        foreach ($sections as &$section) {
            if (empty($section['model'])) {
                throw new InvalidConfigException('$model attribute required ');
            }

            $model =  $this->instantiateModel($section['model']);
            $section['model'] = $model;
            $models[] = $model;
        }


        if (\Yii::$app->request->isPost) {
            if ($this->processModels($models)) {
                $this->onSuccess($models);
            } else {
                $this->onError($models);
            }

        }

        $content = '';

        if ($this->isPartialRenderSections) {
            foreach ($sections as $section) {
                if (isset($section['viewContent'])) {
                    if (is_callable($section['viewContent'])) {
                        $content .= call_user_func($this->viewContent, $section['model']);
                    }
                } else {
                    if (empty($section['viewName'])) {
                        throw new InvalidConfigException('Attribute viewName or viewContent required for ' . get_class($section['model']));
                    }
                    $content .= $this->controller->render($section['viewName'], ['model' => $section['model']]);
                }
            }
            return $content;
        }

        return $this->controller->render($this->viewName, [
            'sections' => $sections,
            'model' => reset($models)
        ]);

    }

    /**
     * @param SettingsInterface[] $models
     */
    protected function onSuccess($models)
    {
        if (isset($this->onSuccess) && is_callable($this->onSuccess)) {
            call_user_func($this->onSuccess, $models);
        }

        if ($this->anchor) {
            $id = \Yii::$app->request->post($this->anchor);
            if (isset($this->redirectRoute)) {
                $route = (array)$this->redirectRoute;
                if ($id) {
                    $route['#'] = $id;
                }
                $this->controller->redirect($route)->send();
                \Yii::$app->end();
            }

            if ($id) {
                $this->controller->redirect(['', '#' => $id])->send();
                \Yii::$app->end();
            }
        }

    }

    /**
     * @param SettingsInterface[] $models
     */
    protected function onError($models)
    {
        $models = array_filter($models, function ($m) {
            if (method_exists($m, 'hasErrors')) {
                return call_user_func([$m, 'hasErrors']);
            }
            return false;
        });

        if (isset($this->onError) && is_callable($this->onError)) {
            call_user_func($this->onError, $models);
        }

        /** @var Model $model */
        $model = reset($models);

        if (isset($model) && $model instanceof Model) {
            $attribute = array_keys($model->getFirstErrors());
            $attribute = reset($attribute);
            \Yii::$app->view->registerJs(
            /** @lang JavaScript */
                "$('html, body').animate({scrollTop: $('#" . Html::getInputId($model,
                    $attribute) . "').offset().top}, 1500);"
            );

        }
    }

    /**
     * @return array
     */
    protected function controllerSections()
    {
        $sections = $this->sections;
        $method = 'settingsSections' . ucfirst($this->controller->action->id);

        if ($this->controller->hasMethod($method)) {
            $sections = ArrayHelper::merge($sections, call_user_func([$this->controller, $method]));
        }

        return $sections;
    }

    /**
     * @param array|Model|SettingsInterface[] $models
     * @return bool
     */
    protected function processModels(array &$models)
    {
        $result = true;

        foreach ($models as &$model) {
            $model = $this->instantiateModel($model);

            if ($model->hasMethod('getNestedModels')) {
                $nestedModels = $model->getNestedModels();
                if (!$this->processModels($nestedModels)) {
                    $models = $nestedModels;
                    return false;
                }
            }

            if ($model instanceof Model) {
                if ($model->load(\Yii::$app->request->post())) {
                    if ($model->validate()) {
                        $result = $model->updateSettings();
                    } else {
                        $result = false;
                    }
                }
            } else {
                $result = $model->updateSettings();
            }
        }
        return $result;
    }

    /**
     * @param string|Model|SettingsInterface $class
     * @return Model|SettingsInterface|null
     */
    private function instantiateModel($class)
    {
        if (empty($class)) {
            return null;
        }

        if (is_object($class)) {
            return $class;
        }

        return $class::instance();
    }


}