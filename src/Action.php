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
use yii\helpers\Inflector;
use yii\helpers\UnsetArrayValue;

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

    /**
     * @var string
     */
    public $viewName = 'settings';

    /**
     * @var array
     */
    public $viewParams = [];

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
     * @var bool
     */
    public $scrollToError = true;


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
            if (!empty($section['model'])) {
                $model = $this->instantiateModel($section['model']);
                $section['model'] = $model;
                $models[] = $model;
            }
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
                        $content .= call_user_func($section['viewContent'], $section['model']);
                    }
                } else {
                    if (empty($section['viewName'])) {
                        throw new InvalidConfigException('Attribute viewName or viewContent required for ' . get_class($section['model']));
                    }
                    $content .= $this->controller->render($section['viewName'], ArrayHelper::merge($this->viewParams, ['model' => $section['model']]));
                }
            }
            return $content;
        }

        return $this->controller->render($this->viewName, ArrayHelper::merge($this->viewParams, [
            'sections' => $sections,
            'model' => reset($models)
        ]));

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
     * @param SettingsInterface[]|Model[] $models
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

        if ($this->scrollToError) {
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
    }

    /**
     * @return array
     */
    protected function controllerSections()
    {
        $sections = $this->sections;

        $method = 'settingsSections' . ucfirst(Inflector::id2camel($this->controller->action->id));

        if ($this->controller->hasMethod($method)) {
            $sections = ArrayHelper::merge($sections, call_user_func([$this->controller, $method], $this));
        }

        return $sections;
    }

    /**
     * @param Model[]|SettingsInterface[] $models
     * @return bool
     */
    protected function processModels(array &$models)
    {

        $request = \Yii::$app->request;
        $result = false;

        foreach ($models as &$model) {
            $model = $this->instantiateModel($model);

            if ($model instanceof SettingsMultipleInterface) {
                $success = false;

                if ($model->load($request->post())) {
                    $model->validate();
                }

                foreach ($request->post($model->formName(), []) as $index => $attributes) {
                    /** @var SettingsInterface|Model $sub */
                    if (is_array($attributes) && !$model->hasProperty($index)) {

                        $sub = $model->getItem($index);
                        $success = $sub->load($attributes, '') && $sub->validate();

                        $request->setBodyParams(ArrayHelper::merge($request->getBodyParams(), [
                            $model->formName() => [
                                $index => new UnsetArrayValue()
                            ]
                        ]));
                    }
                }

                if ($success && !$model->hasErrors()) {
                    return $model->updateSettings();
                }
            }

            if ($model->load($request->post())) {
                $result = $model->validate() && $model->updateSettings();
            }

            if ($model->hasMethod('getNestedModels')) {
                $nestedModels = $model->getNestedModels();
                if (!$this->processModels($nestedModels)) {
                    $models = $nestedModels;
                    return $result;
                }
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