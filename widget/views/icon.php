<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\widget\views
 * @category   CategoryName
 */

/**
 * @var $this \yii\web\View
 * @var $widget \lispa\amos\core\widget\WidgetIcon
 * @var $asset \yii\web\AssetBundle
 */

$classSpanStr = @join(' ', $widget->classSpan);
$classSpanLi = @join(' ', $widget->classLi);
$classSpanA = @join(' ', $widget->classA);
$className = $widget::className();

$userAgent = (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/') > -1 || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? 'ie' : '';

$url = is_array($widget->url) ? \yii\helpers\Url::to($widget->url) : $widget->url;
$target = ((strlen($widget->targetUrl) > 0) ? 'target="' . $widget->targetUrl . '" ' : '');
$dataModule = $widget->moduleName;
?>

<div class="item-widget col-custom" data-code="<?= $className ?>">
    <?php if (strlen($url)): ?>
    <a data-module = "<?=$dataModule?>" class="<?=$classSpanA?>" href="<?= $url ?>" <?= $target ?>title="<?= $widget->description ?>" role="menuitem" class="sortableOpt1" <?= $widget->dataPjaxZero ?> <?= $widget->attributes ?>>
        <?php endif; ?>
        <?php if (strlen($url) && ($widget->targetUrl == '_blank')): ?>
            <span class="sr-only"><?= Yii::t('amoscore', 'Questo link verrà aperto in una nuova pagina') ?></span>
        <?php endif; ?>
        <span class="badge"><?= $widget->bulletCount ? $widget->bulletCount : '' ?></span>
        <span class="<?= $classSpanStr ?>">
            <?= \lispa\amos\core\icons\AmosIcons::show($widget->icon, [], $widget->iconFramework) ?>
            <!--span class="svg-container">
                <svg title="< ?= $widget->description ?>" role="img" class="svg-content">
                  <use xlink:href="< ?= $asset->baseUrl ?>/svg/icone< ?= vv ?>.svg#< ?= $widget->icon ?>"></use>
                </svg>
            </span-->
        <span class="icon-dashboard-name pluginName"><?= $widget->label ?></span>
    </span>
        <?php if (strlen($url)): ?>
    </a>
<?php endif; ?>
</div>
