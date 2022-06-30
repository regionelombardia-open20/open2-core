<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use Yii;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use \PhpOffice\PhpSpreadsheet\Shared\Date;
use yii\log\Logger;
use open20\amos\core\record\RecordDynamicModel;

/**
 * Class SpreadSheetFactory
 * @package open20\amos\core\utilities
 */
class SpreadSheetFactory
{
    const SHEET_TO_EXPORT_FIRST = 'first__sheet';
    const SHEET_TO_EXPORT_ALL   = 'all_sheets';
    const UPDATE_INCREMENTAL    = 1;
    const UPDATE_DIFFERENTIAL   = 2;
    const UPDATE_OVERRIDE       = 3;

    /**
     * Returns a new instance of the PhpSpreadsheet class.
     *
     * @param null|string $filename     If set, uses the IOFactory to return the spreadsheet located at $filename
     *                                  using automatic type resolution per \PhpOffice\PhpSpreadsheet\IOFactory.
     *
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function createSpreadsheet($filename = null)
    {
        return (is_null($filename) ? new Spreadsheet() : IOFactory::load($filename));
    }

    /**
     * Returns the PhpSpreadsheet IWriter instance to save a file.
     *
     * @param Spreadsheet $spreadsheet
     * @param             $type
     *
     * @return \PhpOffice\PhpSpreadsheet\Writer\IWriter
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function createWriter(Spreadsheet $spreadsheet, $type)
    {
        return IOFactory::createWriter($spreadsheet, $type);
    }

    /**
     *
     * @param array $config
     * @return yii2tech\spreadsheet\Spreadsheet
     */
    public static function createSpreadSheetExport(array $config = [])
    {
        $writer = Yii::$app->spreadsheet->createSpreadsheet($config);
        return $writer;
    }

    /**
     *
     * @param type $type Reader class to create.
     * @return \open20\amos\core\utilities\readerClass  Returns a IReader of the given type if found.
     * @throws \InvalidArgumentException
     */
    public static function createReader($type)
    {
        $readerClass = '\\PhpOffice\\PhpSpreadsheet\\Reader\\'.$type;
        if (!class_exists($readerClass)) {
            throw new \InvalidArgumentException('The reader ['.$type.'] does not exist or is not supported by PhpSpreadsheet.');
        }
        return new $readerClass();
    }

    /**
     * Reading the file.
     * Identified the filetype before creation of Reader.
     *
     *
     * @param type $fileName
     * @param type $model_name
     * @param type $rowToStart
     * @param type $colToStart
     */
    public static function createImportAndSave($fileName, $model_name, $rowToStart = 2, $colToStart = 1,
                                               $chunkSize = 1000)
    {

        //TODO aggiungi parametro chunksize
        //array $config = []
//        //$config = [
//           'filename' => filename fullyqualified,
//           'modelname' => model name,
//           'rowToStart' => 2,
//           'colToStart' => 1,
//           'chunkSize' => 1000,
//           ];

        $format       = IOFactory::identify($fileName);
        $objectreader = IOFactory::createReader($format);
        $objectreader->setReadDataOnly(TRUE);

        // Create a new Instance of our Read Filter
        $chunkFilter = new ChunkReadFilter();
        // Tell the Reader that we want to use the Read Filter that we've Instantiated
        $objectreader->setReadFilter($chunkFilter);

        $worksheetData = $objectreader->listWorksheetInfo($fileName);

        Yii::getLogger()->log('start: '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);

        // Loop to read our worksheet in "chunk size" blocks
        foreach ($worksheetData as $worksheet) {
            $highestRow = $worksheet['totalRows'];

            for ($startRow = $rowToStart; $startRow <= $highestRow; $startRow += $chunkSize) {
                // Tell the Read Filter, the limits on which rows we want to read this iteration
                $chunkFilter->setRows($startRow, $chunkSize);
                // Load only the rows that match our filter from $inputFileName to a PhpSpreadsheet Object
                $spreadsheet = $objectreader->load($fileName);
                $worksheet   = $spreadsheet->getActiveSheet();
                $maxRows     = $worksheet->getHighestRow(); // e.g. 10

                $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 5

                for ($row = $startRow; $row <= $maxRows; ++$row) {
                    $model = new $model_name();

                    for ($col = $colToStart; $col <= $highestColumnIndex; ++$col) {

                        $key  = mb_strtolower($worksheet->getCellByColumnAndRow($col, 1)->getValue());
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);

                        $dateattr = 'data_';
                        $pos      = strpos($key, $dateattr);
                        if ($pos !== false) {
                            $worksheet->getStyle($cell->getColumn().$row)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                        }
                        $value = $cell->getValue();
                        if (Date::isDateTime($cell)) {
                            $value       = Date::excelToDateTimeObject($value);
                            $model->$key = date_format($value, 'Y-m-d H:i:s');
                        } else {
                            $model->$key = $value;
                        }
                        unset($key);
                        unset($cell);
                    }
                    $model->save(false);
                }
                self::freeMemory($spreadsheet);
                gc_collect_cycles();
            }
        }
        Yii::getLogger()->log('end '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);
        die();
    }

    /**
     *
     * @param type $fileName
     * @param type $model_name
     * @param type $rowToStart
     * @param array $colToSave
     */
    public static function createImportAndSaveRangeColumn($fileName, $model_name, $rowToStart = 2, $chunkSize = 1000,
                                                          array $colToSave = [])
    {

        if (!(is_array($colToSave) && isset($colToSave))) {
            return self::createImportAndSave($fileName, $model_name, $rowToStart);
        }

        $format       = IOFactory::identify($fileName);
        $objectreader = IOFactory::createReader($format);
        $objectreader->setReadDataOnly(TRUE);

        // Create a new Instance of our Read Filter
        $chunkFilter = new ChunkReadFilter();
        // Tell the Reader that we want to use the Read Filter that we've Instantiated
        $objectreader->setReadFilter($chunkFilter);

        $worksheetData = $objectreader->listWorksheetInfo($fileName);

        Yii::getLogger()->log('start: '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);
        // Loop to read our worksheet in "chunk size" blocks
        foreach ($worksheetData as $worksheet) {
            $highestRow = $worksheet['totalRows'];

            for ($startRow = $rowToStart; $startRow <= $highestRow; $startRow += $chunkSize) {
                // Tell the Read Filter, the limits on which rows we want to read this iteration
                $chunkFilter->setRows($startRow, $chunkSize);
                // Load only the rows that match our filter from $inputFileName to a PhpSpreadsheet Object
                $spreadsheet = $objectreader->load($fileName);
                $worksheet   = $spreadsheet->getActiveSheet();
                $maxRows     = $worksheet->getHighestRow(); // e.g. 10

                for ($row = $startRow; $row <= $maxRows; ++$row) {
                    $model = new $model_name();
                    foreach ($colToSave as $index => $colindex) {
                        $key  = mb_strtolower($worksheet->getCellByColumnAndRow($colindex, 1)->getValue());
                        $cell = $worksheet->getCellByColumnAndRow($colindex, $row);

                        $dateattr = 'data_';
                        $pos      = strpos($key, $dateattr);
                        if ($pos !== false) {
                            $worksheet->getStyle($cell->getColumn().$row)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                        }

                        $value = $cell->getValue();

                        if (Date::isDateTime($cell)) {
                            $value       = Date::excelToDateTimeObject($value);
                            $model->$key = date_format($value, 'Y-m-d H:i:s');
                        } else {
                            $model->$key = $value;
                        }
                        unset($key);
                        unset($cell);
                    }
                    $model->save(false);
                }
                self::freeMemory($spreadsheet);
            }
            Yii::getLogger()->log('end '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);
        }
    }

    /**
     *
     * @param type $filename
     * @param array $config
     * @return type
     */
    public static function createExportAndSend($filename, array $config = [])
    {

        $writer = self::createSpreadSheetExport($config);
        return Yii::$app->spreadsheet->renderAndSend($writer, $filename);
    }

    /**
     *
     * @param type $filename
     * @param array $config
     * @return type
     */
    public static function createExportPdfAndSend($filename, array $config = [])
    {
        $writer = self::createSpreadSheetExport($config);
        return Yii::$app->spreadsheet->renderAndSendPdf($writer, $filename);
    }

    /**
     *
     * @param type $filename
     * @param array $config
     * @return type
     */
    public static function createExportAndSave($filename, array $config = [])
    {

        $writer = self::createSpreadSheetExport($config);
        return Yii::$app->spreadsheet->renderAndSave($writer, $filename);
    }

    /**
     * Save and stream output to the browser.
     *
     * @param type $filename
     * @param array $config
     * @return type
     */
    public static function createExportSaveAndStream($filename, $defaultStyle, array $config = [])
    {

        if ($defaultStyle) {
            $writer = self::createSpreadSheetExport(Yii::$app->spreadsheet->setDefaultStyles($config));
        } else {
            $writer = self::createSpreadSheetExport($config);
        }
        return Yii::$app->spreadsheet->saveAndStream($writer, $filename);
    }

    /**
     * Create array from file (excel)
     * @param string $fileName
     * @param int|null $numRowToExport Number of rows that it will export in the array
     * @param array $colToExport Columns will export in the array, the format is [1,3,4] where the numbers are the number of the column
     * @param int $rowToStart
     * @param string $sheetToExport Name of the sheet will export in the array, default is 'first__sheet'. 'first__sheet' export only the first sheet, 'all__sheets' export all the sheets. If you set another value (e.g. 'Sheet1') it will export exactly the sheet with it name.
     * @return array Array with this format [
     *                                          ['Sheet1'] => [
     *                                              [column_1] => [[0] => 'text' => [1] => 'text']]
     *                                              [column_2] => [[0] => 'text' => [1] => 'text']]
     *                                          ]
     *                                      ]
     */
    public static function createArrayFromNumberOfRows($fileName, $numRowToExport = null, array $colToExport = [],
                                                       $rowToStart = 2, $sheetToExport = self::SHEET_TO_EXPORT_FIRST)
    {

        $file_array    = [];
        $format        = IOFactory::identify($fileName);
        $objectreader  = IOFactory::createReader($format);
        $objectreader->setReadDataOnly(TRUE);
        $worksheetData = $objectreader->listWorksheetInfo($fileName);

        foreach ($worksheetData as $k => $worksheet) {
            $highestRow    = $worksheet['totalRows'];
            $worksheetName = $worksheet['worksheetName'];
            $maxColumns    = $worksheet['totalColumns'];
            if ($sheetToExport == self::SHEET_TO_EXPORT_ALL || $sheetToExport == self::SHEET_TO_EXPORT_FIRST || $sheetToExport
                == $worksheetName) {
                $numRows = $numRowToExport + $rowToStart - 1;
                $maxRows = (!empty($numRowToExport) ? (($numRows > $highestRow) ? $highestRow : $numRows) : $highestRow);
                for ($startRow = $rowToStart; $startRow <= $maxRows; $startRow ++) {
                    // Load only the rows that match our filter from $inputFileName to a PhpSpreadsheet Object
                    $spreadsheet    = $objectreader->load($fileName);
                    $allSheet       = $spreadsheet->getAllSheets();
                    $worksheet      = $allSheet[$k];
                    $worksheetTitle = $allSheet[$k]->getTitle();

                    $columns = (!empty($colToExport) ? $colToExport : range(1, $maxColumns, 1));
                    foreach ($columns as $index => $colindex) {
                        $key  = mb_strtolower($worksheet->getCellByColumnAndRow($colindex, 1)->getValue());
                        $cell = $worksheet->getCellByColumnAndRow($colindex, $startRow);

                        $dateattr = 'data_';
                        $pos      = strpos($key, $dateattr);
                        if ($pos !== false) {
                            $worksheet->getStyle($cell->getColumn().$row)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                        }

                        $value = $cell->getValue();
                        if (Date::isDateTime($cell)) {
                            $value                               = Date::excelToDateTimeObject($value);
                            $file_array[$worksheetTitle][$key][] = date_format($value, 'Y-m-d H:i:s');
                        } else {
                            $file_array[$worksheetTitle][$key][] = $value;
                        }
                        unset($key);
                        unset($cell);
                    }
                    SpreadSheetFactory::freeMemory($spreadsheet);
                }
                if ($sheetToExport == self::SHEET_TO_EXPORT_FIRST) {
                    break;
                }
            }
        }
        return $file_array;
    }

    /**
     *
     * @param string $fileName
     * @return array
     */
    public static function getWorksheetsFromExcel($fileName)
    {
        $worksheets   = [];
        $format       = IOFactory::identify($fileName);
        $objectreader = IOFactory::createReader($format);
        $objectreader->setReadDataOnly(TRUE);
        $worksheets   = $objectreader->listWorksheetInfo($fileName);
        return $worksheets;
    }

    /**
     *
     */
    public static function setClearCacheMethod()
    {
        $cacheMethod = Settings::getCache();
        if (!empty($cacheMethod)) {
            $cacheMethod->clear();
        }
    }

    public static function reset()
    {
        \Yii::$app->cache->flush();
    }

    public static function freeMemory($spreadsheet)
    {
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    function formatBytes($size, $precision = 2)
    {
        $base     = log($size, 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(pow(1024, $base - floor($base)), $precision).' '.$suffixes[floor($base)];
    }

    /**
     * Reading the file.
     * Identified the filetype before creation of Reader.
     *
     * @param string $sheet
     * @param string $fileName
     * @param string $table
     * @param array $attributes
     * @param int $typeUpdate
     * @param int $rowToStart
     * @param string $db
     * @param string $driver
     * @param int $colToStart
     * @param int $chunkSize
     * @return int
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public static function createImportAndSaveDynamic($sheet, $fileName, $table, $attributes,
                                                      $typeUpdate = self::UPDATE_INCREMENTAL, $rowToStart = 2,
                                                      $db = 'db',
                                                      $driver = 'open20\\amos\\core\\record\\drivers\\Mysql',
                                                      $colToStart = 1, $chunkSize = 1000)
    {
        $format       = IOFactory::identify($fileName);
        $objectreader = IOFactory::createReader($format);
        $objectreader->setReadDataOnly(TRUE);

        // Create a new Instance of our Read Filter
        $chunkFilter = new ChunkReadFilter();
        // Tell the Reader that we want to use the Read Filter that we've Instantiated
        $objectreader->setReadFilter($chunkFilter);

        $worksheetData = $objectreader->listWorksheetInfo($fileName);

        Yii::getLogger()->log('start: '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);
        $rowToStart = self::getRowToStart($rowToStart, $typeUpdate);
        $indx       = $rowToStart;
        // Loop to read our worksheet in "chunk size" blocks
        foreach ($worksheetData as $worksheet) {
            $highestRow    = $worksheet['totalRows'];
            $worksheetName = $worksheet['worksheetName'];
            $maxColumns    = $worksheet['totalColumns'];
            if ($sheet == $worksheetName) {
                for ($startRow = $rowToStart; $startRow <= $highestRow; $startRow += $chunkSize) {
                    // Tell the Read Filter, the limits on which rows we want to read this iteration
                    $chunkFilter->setRows($startRow, $chunkSize);
                    // Load only the rows that match our filter from $inputFileName to a PhpSpreadsheet Object
                    $spreadsheet = $objectreader->load($fileName);
                    $worksheet   = $spreadsheet->getActiveSheet();
                    $maxRows     = $worksheet->getHighestRow(); // e.g. 10

                    $highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
                    $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 5

                    for ($row = $startRow; $row <= $maxRows; ++$row) {
                        $indx++;
                        $model = new RecordDynamicModel($attributes);
//                        $model->addRule($attributes, 'safe'); // Commentata per adeguarsi agli unsafeAttributes del RecordDynamicModel. Se la riga è scommentata non va e crea la tabella con la sola colonna id.
                        $model->setDb($db);
                        $model->setDriver($driver);
                        $model->setTableName($table);

                        for ($col = $colToStart; $col <= $highestColumnIndex; ++$col) {

                            $key  = mb_strtolower($worksheet->getCellByColumnAndRow($col, 1)->getValue());
                            $cell = $worksheet->getCellByColumnAndRow($col, $row);

                            $dateattr = 'data_';
                            $pos      = strpos($key, $dateattr);
                            if ($pos !== false) {
                                $worksheet->getStyle($cell->getColumn().$row)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
                            }
                            $value = $cell->getValue();
                            if (Date::isDateTime($cell)) {
                                $value       = Date::excelToDateTimeObject($value);
                                $model->$key = date_format($value, 'Y-m-d H:i:s');
                            } else {
                                $model->$key = $value;
                            }
                            unset($key);
                            unset($cell);
                        }
                        $model->save();
                    }
                    self::freeMemory($spreadsheet);
                    gc_collect_cycles();
                }
            }
        }
        Yii::getLogger()->log('end '.date("d-m-Y h:i:s"), Logger::LEVEL_ERROR);
        $indx = self::getRowToStart($indx, $typeUpdate);
        return $indx;
    }

    /**
     *
     * @param integer $rowToStart
     * @param integer $typeUpdate
     * @return integer
     */
    protected static function getRowToStart($rowToStart, $typeUpdate)
    {
        $indx = 2;
        if ($typeUpdate == self::UPDATE_DIFFERENTIAL) {
            $indx = $rowToStart;
        }
        return $indx;
    }
}