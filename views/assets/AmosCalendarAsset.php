<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\assets
 * @category   CategoryName
 */

namespace open20\amos\core\views\assets;

use yii\web\AssetBundle;

class AmosCalendarAsset extends AssetBundle
{
    /**
     * the language the calender will be displayed in
     * @var string ISO2 code for the wished display language
     */
    public $language = NULL;

    /**
     * [$autoGenerate description]
     * @var boolean
     */
    public $autoGenerate = true;

    /**
     * tell the calendar, if you like to render google calendar events within the view
     * @var boolean
     */
    public $googleCalendar = false;

    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';

    public $css = [
    ];

    public $js = [
        'js/AmosCalendar.js'
    ];
    public $depends = [
    ];
}
