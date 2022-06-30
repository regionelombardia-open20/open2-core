<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\views\widgets
 * @category   CategoryName
 */

use kv4nt\owlcarousel\OwlCarouselWidget;

/**
 * @var \open20\amos\core\forms\AmosOwlCarouselWidget $widget
 * @var string $owlCarouselContent
 * @var array $containerOptions
 */

$owlCarouselId = $widget->owlCarouselId;
$owlCarouselJSOptions = $widget->owlCarouselJSOptions;

$js = <<< JS
    $('#$owlCarouselId').owlCarousel($owlCarouselJSOptions);
JS;
$this->registerJs($js);

?>

<?php OwlCarouselWidget::begin([
    'container' => 'div',
    'containerOptions' => $containerOptions,
]); ?>
<?= $owlCarouselContent; ?>
<?php OwlCarouselWidget::end(); ?>
