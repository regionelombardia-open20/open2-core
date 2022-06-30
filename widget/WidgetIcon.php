<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\widget
 * @category   CategoryName
 */

namespace open20\amos\core\widget;

use open20\amos\core\icons\AmosIcons;
use Yii;
use yii\db\ActiveQuery;

class WidgetIcon extends WidgetAbstract
{
    public
        $url,
        $post,
        $icon                  = 'linmodulo',
        $iconFramework         = 'dash',
        $namespace,
        $classLi               = [],
        $classA                = [],
        $classSpan             = ['color-primary'],
        $targetUrl             = '',
        $bulletCount           = '',
        $dataPjaxZero          = 'data-pjax="0"',
        $attributes            = '', // @var string $attributes - additional attributes for html tag <a>
        $disableBulletCounters = false,
        $saveMicrotime         = true,
        $active                = false

    ;
    const EVENT_AFTER_COUNT = 'EVENT_AFTER_COUNT';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (isset(\Yii::$app->params['disableBulletCounters']) && (\Yii::$app->params['disableBulletCounters'] === true)) {
            $this->disableBulletCounters = true;
        }
    }

    /**
     * 
     * @return string
     */
    public function run()
    {
        return ($this->isVisible()) ? $this->getHtml() : '';
    }

    /**
     * 
     * @param type $module
     */
    public function checkScope($module = null)
    {
        $moduleCwh = \Yii::$app->getModule('cwh');

        if (isset($moduleCwh)) {
            $scope = $moduleCwh->getCwhScope();
            if (!empty($scope)) {
                if (isset($scope[$module])) {
                    return $scope[$module];
                }
            }
        }

        return false;
    }

    /**
     * 
     * @return type
     */
    public function getHtml()
    {
        $controller = \Yii::$app->controller;
        $moduleL    = \Yii::$app->getModule('layout');

        if (!empty($moduleL)) {
            $assetBundle = \open20\amos\layout\assets\BaseAsset::register($controller->getView());
        } else {
            $assetBundle = \open20\amos\core\views\assets\AmosCoreAsset::register($controller->getView());
        }

        $view = '@vendor/open20/amos-core/widget/views/icon';
        if ($this->getEngine() == WidgetAbstract::ENGINE_ROWS) {
            $view = '@vendor/open20/amos-core/widget/views/icon_rows';
        }

        return $this->render(
                $view, [
                'asset' => $assetBundle,
                'widget' => $this
                ]
        );
    }

    /**
     * 
     * @return type
     */
    public function getOptions()
    {
        return [
            'isVisible' => $this->isVisible(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
            'code' => $this->getCode(),
            'url' => $this->getUrl(),
            'post' => $this->getPost(),
            'moduleName' => $this->getModuleName(),
            'icon' => $this->getIcon(),
            'namespace' => $this->getNamespace(),
            'iconFramework' => $this->getIconFramework(),
            'classSpan' => $this->getClassSpan(),
            'attributes' => $this->getAttributes(),
            'active' => $this->getActive(),
        ];
    }

    /**
     * @return string
     */
    public function getBulletCount()
    {
        return $this->bulletCount;
    }

    /**
     * @param string $bulletCount
     */
    public function setBulletCount($bulletCount)
    {
        $this->bulletCount = $bulletCount;
    }

    /**
     * Reset bc only if the current page contain it
     */
    public function resetBulletCount()
    {
        return in_array(
            '/'.\Yii::$app->controller->action->getUniqueId(), $this->getUrl()
        );
    }

    /**
     * Return the name of the relative WidgetIconFOO to used by cron script
     * 
     * @return type
     */
    public static function getWidgetIconName()
    {
        $parts = explode('\\', self::classname());

        if (is_array($parts)) {
            return array_pop($parts);
        }

        return null;
    }

    /**
     * Return not read record of $className object for the current logged user, 
     * based on amos-notify so bullet count is update in right way and 
     * disapper when the relative object is actived
     * 
     * @param int $userId
     * @param string $className
     * @param ActiveQuery $externalQuery
     * @return int
     */
    public function makeBulletCounter($userId = null, $className = null, $externalQuery = null)
    {
        if (($this->disableBulletCounters == true) || ($userId == null) || ($className == null)) {
            return 0;
        }

        $count    = 0;
        $notifier = Yii::$app->getModule('notify');
        if ($notifier) {
            $count = $notifier->countNotRead(
                $userId, $className, $externalQuery
            );
        }

        return $count;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return mixed
     */
    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * @return mixed
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * @param mixed $targetUrl
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * @return array
     */
    public function getClassLi()
    {
        return $this->classLi;
    }

    /**
     * @param array $classLi
     */
    public function setClassLi($classLi)
    {
        $this->classLi = $classLi;
    }

    /**
     * @return array
     */
    public function getClassA()
    {
        return $this->classA;
    }

    /**
     * @param array $classA
     */
    public function setClassA($classA)
    {
        $this->classA = $classA;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getIconFramework()
    {
        return $this->iconFramework;
    }

    /**
     * @param mixed $iconFramework
     */
    public function setIconFramework($iconFramework)
    {
        $this->iconFramework = $iconFramework;
    }

    /**
     * @return array
     */
    public function getClassSpan()
    {
        return $this->classSpan;
    }

    /**
     * @param array $classSpan
     */
    public function setClassSpan($classSpan)
    {
        $this->classSpan = $classSpan;
    }

    /**
     * @return string
     */
    public function getDataPjaxZero()
    {
        return $this->dataPjaxZero;
    }

    /**
     * @param string $dataPjaxZero
     */
    public function setDataPjaxZero($dataPjaxZero)
    {
        $this->dataPjaxZero = $dataPjaxZero;
    }

    /**
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     *
     */
    public function enableDashboardModal()
    {
        $this->classA[] = 'open-modal-dashboard';
    }
}