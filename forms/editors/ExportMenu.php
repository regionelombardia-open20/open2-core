<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors;

use open20\amos\core\forms\editors\assets\ExportMenuAsset;
use open20\amos\core\utilities\StringUtils;
use kartik\base\BootstrapInterface;
use kartik\base\BootstrapTrait;
use kartik\dialog\Dialog;
use kartik\export\ExportColumnAsset;
use kartik\export\ExportMenu as KartikExportMenu;
use ReflectionClass;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Export menu widget. Export tabular data to various formats using the `\PhpOffice\PhpSpreadsheet\Spreadsheet library
 * by reading data from a dataProvider - with configuration very similar to a GridView.
 *
 * @since  1.0
 */
class ExportMenu extends KartikExportMenu
{  
    /**
     * @var string the view file for rendering the export form
     */
    public $exportFormView = '';

    /**
     * @var string the view file for rendering the columns selection
     */
    public $exportColumnsView = '';

    public $afterSaveView = '';


    public $batchSize = 500;

    /**
     *
     * @param type $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $reflector = new ReflectionClass('\kartik\export\ExportMenu');
        $fn = dirname($reflector->getFileName());
        $vendor = Yii::getAlias('@vendor');
        $fn = '@vendor' . StringUtils::replace(StringUtils::replace($fn, '\\', '/'), StringUtils::replace($vendor, '\\', '/'), '');

        $this->exportFormView = $fn . '/views/_form';
        $this->exportColumnsView = $fn . '/views/_columns';
        $this->afterSaveView = $fn . '/views/_view';
    }

    /**
     * Initializes export settings
     */
    public function initExport()
    {
        if (!$this->dataProvider->pagination) {
            $this->dataProvider->setPagination(['pageSize' => $this->batchSize]);
        }
        parent::initExport();
    }
    
}
