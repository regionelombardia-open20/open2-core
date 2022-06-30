<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\utility
 * @category   CategoryName
 */

namespace open20\amos\core\components;

use yii2tech\spreadsheet\Spreadsheet;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQueryInterface;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use yii\helpers\ArrayHelper;

class AmosSpreadSheet extends Spreadsheet {

    /**
     * @var \yii2tech\spreadsheet\Spreadsheet|null spreadsheet document representation instance.
     */
    private $_document;
    
    private $_exportType;

    /**
     * @return \yii2tech\spreadsheet\Spreadsheet spreadsheet document representation instance.
     */
    public function getDocument() {
        if (!is_object($this->_document)) {
            $this->_document = new Spreadsheet();
        }
        return $this->_document;
    }

    /**
     * @param \yii2tech\spreadsheet\Spreadsheet|null $document spreadsheet document representation instance.
     */
    public function setDocument($document) {
        $this->_document = $document;
    }

    /**
     * @param array $config Excel grid configuration.
     * @return Spreadsheet Excel grid instance.
     */
    function createSpreadsheet(array $config = []) {
        if (isset($config['models']) && isset($config['columns'])) {
            $config = $this->prepareConfig($config);
        }

//        pr($config['columns'][0]);
//        die();
        if (!isset($config['dataProvider']) && !isset($config['query'])) {
            $config['dataProvider'] = new ArrayDataProvider();
        }

//        $this->setDocument(new Spreadsheet($this->setDefaultStyles($config)));
        $this->setDocument(new Spreadsheet($config));
        return $this->getDocument();
    }

    /**
     * 
     * @param type $config
     */
    private function prepareConfig($config) {
        $reconfig = [];
        $modelconf = $config['models'];

        if ($modelconf instanceof ActiveDataProvider || $modelconf instanceof ActiveQueryInterface) {
            $reconfig = [
                'query' => $config['models'],
                'columns' => $config['columns'],
            ];
        } else {
            $reconfig = [
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $config['models'],
                        ]),
                'columns' => $config['columns'],
            ];
        }
        if (isset($config['pagination'])) {
            $config = $this->setPagination($reconfig, $config['pagination']);
        } else {
            $config = $reconfig;
        }
        return $config;
    }

    /**
     * 
     * @param type $conf
     * @param type $pagination
     * @return type
     */
    private function setPagination($conf, $pagination) {

        if (isset($conf['dataProvider'])) {
            $conf['dataProvider']->setPagination(['pageSize' => $pagination]);
        } else if (isset($conf['query'])) {
            $conf['batchSize'] = $pagination;
        }
        return $conf;
    }

    /**
     *  Sets default styles
     * 
     * @param type $config
     * @return type
     */
    public function setDefaultStyles($config) {
        foreach ($config['columns'] as $key => $column) {
            $config['columns'][$key]['dimensionOptions'] = [
                'autoSize' => true
            ];
            $config['columns'][$key]['headerOptions'] = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => [
                        'argb' => 'FFE5E5E5',
                    ],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['argb' => Color::COLOR_BLACK],
                    ],
                ],
            ];
            $config['columns'][$key]['contentOptions'] = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_DOTTED,
                        'color' => ['argb' => Color::COLOR_BLACK],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
        }
        return $config;
    }
    
    /**
     * Save the rendered content as a file
     * 
     * @param type $exporter
     * @param type $filename
     * @return type
     */
    function renderAndSave($exporter, $filename) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $exporter->render();

        ob_end_clean();
        return $exporter->save($filename);
    }

    /**
     * Save and stream output to the browser.
     * 
     * @param type $exporter
     * @param type $filename
     */
    function saveAndStream($exporter, $filename) {
       
        $exporter->save($filename);
        ob_end_clean();

        $this->setHttpHeaders($filename);
        readfile($filename);
        exit();
    }

    /**
     * Sends the rendered content as a file to the browser.
     * 
     * @param type $exporter
     * @param type $filename
     * @return type
     */
    function renderAndSend($exporter, $filename) {
        ob_end_clean();
        return $exporter->send($filename);
    }
    
    /**
     * 
     * @param type $exporter
     * @param type $filename
     * @return type
     */
    function renderAndSendPdf($exporter, $filename) {

        \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        ob_end_clean();
        return $exporter->send($filename);
    }

    /**
     * Destroys PhpSpreadsheet Object Instance
     */
    public function destroySpreadsheet() {
        unset($this->_document);
    }

    /**
     * Set HTTP headers for download
     */
    protected function setHttpHeaders($filename) {
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') == false) {
            //You are using Internet Explorer
            header('Cache-Control: no-cache');
            header('Pragma: no-cache');
        } else {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        header('Expires: Sat, 26 Jul 1979 05:00:00 GMT');
        header("Content-Encoding: utf-8");
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"".$filename."\"");
        header('Cache-Control: max-age=0');
    }



    /**
     * Cleans up the export file and current object instance
     *
     * @param string $file the file exported
     * @param array  $config the export configuration
     */
    protected function cleanup($file, $config) {
        if ($this->raiseEvent('onGenerateFile', [$config['extension'], $this]) === false) {
            return;
        }
        if ($this->stream || $this->deleteAfterSave) {
            @unlink($file);
        }
        $this->destroyPhpSpreadsheet();
    }

}
