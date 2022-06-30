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

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\module\BaseAmosModule;
use yii\base\BaseObject;

/**
 * Class ZipUtility
 * @package open20\amos\core\utilities
 */
class ZipUtility extends BaseObject
{
    /**
     * @var string $destinationFolder
     */
    private $destinationFolder = '';

    /**
     * @var string $zipFileName
     */
    private $zipFileName = '';

    /**
     * @var string $zipFileExtension It can be possible to specify the compressed file extension (without dot like "zip", not ".zip"). Default to zip.
     */
    private $zipFileExtension = 'zip';

    /**
     * @var string|string[] $filesToZip
     */
    private $filesToZip = '';

    /**
     * @var bool $filesToZipIsArray For internal use. True if the filesToZip property is an array.
     */
    private $filesToZipIsArray = false;

    /**
     * @var string $password
     */
    private $password = '';

    /**
     * @var string $baseCommand
     */
    private $baseCommand = '7z';

    /**
     * @var string $commandToExec The command to be executed. It will be composed by a public method.
     */
    private $commandToExec = '';

    /**
     * @var string $execError
     */
    private $execError = '';

    /**
     * @var int $rawExecErrorCode
     */
    private $rawExecErrorCode = -1;

    /**
     * @var array $rawExecOutput
     */
    private $rawExecOutput = [];

    /**
     * @var bool
     */
    private $isWindows = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->isWindows = (strtoupper(PHP_OS) == 'WINNT');
    }

    /**
     * @return string
     */
    public function getDestinationFolder()
    {
        return $this->destinationFolder;
    }

    /**
     * @param string $destinationFolder
     */
    public function setDestinationFolder($destinationFolder)
    {
        $this->destinationFolder = $destinationFolder;
    }

    /**
     * @return string
     */
    public function getZipFileName()
    {
        return $this->zipFileName;
    }

    /**
     * @param string $zipFileName
     */
    public function setZipFileName($zipFileName)
    {
        $this->zipFileName = $zipFileName;
    }

    /**
     * @return string
     */
    public function getZipFileExtension()
    {
        return $this->zipFileExtension;
    }

    /**
     * @param string $zipFileExtension
     */
    public function setZipFileExtension($zipFileExtension)
    {
        $this->zipFileExtension = $zipFileExtension;
    }

    /**
     * @return string|array
     */
    public function getFilesToZip()
    {
        return $this->filesToZip;
    }

    /**
     * @param string|array $filesToZip
     */
    public function setFilesToZip($filesToZip)
    {
        $this->filesToZip = $filesToZip;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Return true if the execution has error.
     * @return bool
     */
    public function hasError()
    {
        return (strlen($this->getError()) > 0);
    }

    /**
     * This method return the execution error.
     * @return string
     */
    public function getError()
    {
        return $this->execError;
    }

    /**
     * This method return the raw execution error code.
     * @return string
     */
    public function getRawErrorCode()
    {
        return $this->rawExecErrorCode;
    }

    /**
     * This method create a compressed file with all the files you provide to the class.
     * You can set some class properties to create the compressed file compliant with your needs.
     * @return bool
     * @throws AmosException
     */
    public function createZip()
    {
        $this->checksBeforeZip();

        $this->commandToExec = $this->baseCommand . ' a ';

        $this->addCmdPartPassword();
        $this->addCmdPartDestinationFolder();
        $this->addCmdPartFileNameAndExtension();
        $this->addCmdPartFilesToZip();

        $output = null;
        $executionResult = null;
        exec($this->commandToExec, $output, $executionResult);
        $ok = $this->checkExecOutput($output, $executionResult);
        return $ok;
    }

    /**
     * This method unzip the  compressed file name you provide. You can set some class properties to
     * unzip the file in a specific folder or other actions compliant with your needs.
     * @return bool
     * @throws AmosException
     */
    public function unZip()
    {
        $this->checksBeforeUnzip();

        $this->commandToExec = $this->baseCommand . ' e ';

        $this->addCmdPartFileNameAndExtension();
        $this->addCmdPartUnzipDestinationFolder();
        $this->addCmdPartPassword();

        $output = null;
        $executionResult = null;
        exec($this->commandToExec, $output, $executionResult);
        $ok = $this->checkExecOutput($output, $executionResult);
        return $ok;
    }

    /**
     * This method executes all the checks before compose and launch the zip command.
     * These checks are
     * @throws AmosException
     */
    protected function checksBeforeZip()
    {
        $this->checkTypesBeforeExec();
        $this->checkEmptiesBeforeZip();
    }

    /**
     * This method executes all the checks before compose and launch the unzip command.
     * @throws AmosException
     */
    protected function checksBeforeUnzip()
    {
        $this->checkTypesBeforeExec();
        $this->checkEmptiesBeforeUnzip();
    }

    /**
     * This method checks the properties types before execute the zip or unzip command.
     * @throws AmosException
     */
    private function checkTypesBeforeExec()
    {
        // Check destination folder
        if (!is_string($this->destinationFolder)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_property_destination_folder'));
        }

        // Check zip file name
        if (!is_string($this->zipFileName)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_property_zip_file_name'));
        }

        // Check zip file extension
        if (!is_string($this->zipFileExtension)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_property_zip_file_extension'));
        }

        // Check files to zip
        $this->filesToZipIsArray = is_array($this->filesToZip);
        if (!is_string($this->filesToZip) && !$this->filesToZipIsArray) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_or_array_property_file_to_zip'));
        }
        if ($this->filesToZipIsArray && !ArrayUtility::isStringArray($this->filesToZip)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_array_property_file_to_zip'));
        }

        // Check password
        if (!is_string($this->password)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_not_string_property_password'));
        }
    }

    /**
     * This method check the empty properties before zip the files.
     * @throws AmosException
     */
    private function checkEmptiesBeforeZip()
    {
        if (!strlen($this->zipFileName)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_missing_zip_file_name'));
        }

        if (!strlen($this->zipFileExtension)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_missing_zip_file_extension'));
        }

        if (!$this->filesToZipIsArray && !strlen($this->filesToZip)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_missing_file_to_zip'));
        }

        if ($this->filesToZipIsArray && empty($this->filesToZip)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_missing_file_to_zip'));
        }
    }

    /**
     * This method check the empty properties before unzip the file.
     * @throws AmosException
     */
    private function checkEmptiesBeforeUnzip()
    {
        if (!strlen($this->zipFileName)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ZipUtility_missing_zip_file_name'));
        }
    }

    /**
     * This method manage the execution output and save all output to specific properties you can read and show to users.
     * @param array $output
     * @param int $executionResult
     * @return bool
     */
    protected function checkExecOutput($output, $executionResult)
    {
        $ok = false;
        $this->rawExecErrorCode = $executionResult;
        $this->rawExecOutput = $output;
        switch ($executionResult) {
            case 0: // No error
                $ok = true;
                break;
            case 1: // Warning (Non fatal error(s))
                $this->execError = BaseAmosModule::t('amoscore', '#ZipUtility_exec_error_warning');
                break;
            case 2: // Fatal error
                $this->execError = BaseAmosModule::t('amoscore', '#ZipUtility_exec_error_fatal_error');
                break;
            case 7: // Command line error
                $this->execError = BaseAmosModule::t('amoscore', '#ZipUtility_exec_error_command_line_error');
                break;
            case 8: // Not enough memory for operation
                $this->execError = BaseAmosModule::t('amoscore', '#ZipUtility_exec_error_not_enough_memory');
                break;
            case 255: // User stopped the process
                $this->execError = BaseAmosModule::t('amoscore', '#ZipUtility_exec_error_user_stop');
                break;
        }
        return $ok;
    }

    /**
     * This method adds the password part to the command to be executed.
     */
    protected function addCmdPartPassword()
    {
        $password = trim($this->password);
        if (strlen($password) > 0) {
            $this->commandToExec .= '-p' . $password . ' ';
        }
    }

    /**
     * This method adds the zip destination folder part to the command to be executed.
     */
    protected function addCmdPartDestinationFolder()
    {
        $destinationFolder = trim($this->destinationFolder);
        if (strlen($destinationFolder) > 0) {
            $this->commandToExec .= $destinationFolder . ((substr($destinationFolder, -1) != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '');
        }
    }

    /**
     * This method adds the unzip destination folder part to the command to be executed.
     */
    protected function addCmdPartUnzipDestinationFolder()
    {
        $destinationFolder = trim($this->destinationFolder);
        if (strlen($destinationFolder) > 0) {
            $this->commandToExec .= '-o';
            $this->addCmdPartDestinationFolder();
            $this->commandToExec .= ' ';
        }
    }

    /**
     * This method adds the filename and extension part to the command to be executed. If the file name
     * already contains the "dot", it doesn't add the extension because it means that is already present.
     */
    protected function addCmdPartFileNameAndExtension()
    {
        $zipFileName = trim($this->zipFileName);
        $this->commandToExec .= $zipFileName . ((strpos($zipFileName, '.') === false) ? '.' . $this->zipFileExtension : '') . ' ';
    }

    /**
     * The method adds to the command to be executed the single file to zip if the class
     * property "filesToZip" is a string or all files to zip if "filesToZip" is an array of string.
     */
    protected function addCmdPartFilesToZip()
    {
        if ($this->filesToZipIsArray) {
            if ($this->isWindows) {
                foreach ($this->filesToZip as $fileToZip) {
                    $this->commandToExec .= '"' . $fileToZip . '" ';
                }
            } else {
                foreach ($this->filesToZip as $fileToZip) {
                    $this->commandToExec .= "'" . $fileToZip . "' ";
                }
            }
        } else {
            if ($this->isWindows) {
                $this->commandToExec .= '"' . $this->filesToZip . '" ';
            } else {
                $this->commandToExec .= "'" . $this->filesToZip . "' ";
            }
        }
    }
}
