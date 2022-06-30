<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\core\module\BaseAmosModule;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class EmailForm extends Model
{
    /**
     * @var string $message - custom message insert by user
     */
    public $message;

    /**
     * @var string $templatePath - email template path, leave null to use default template
     */
    public $templatePath;

    /**
     * @var string $attributeTo - model attribute specifying the recipient email address
     */
    public $attributeTo;

    /**
     * @var integer $userIdTo - User id of the mail recipient
     */
    public $userIdTo;

    /**
     * @var string $subject - email subject, leave null to use the default one
     */
    public $subject;

    public function rules()
    {
        return [
            [['message', 'templatePath', 'attributeTo', 'subject'], 'string'],
            ['userIdTo', 'integer'],
            ['message', 'required']
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'message' => BaseAmosModule::t('amoscore', '#message'),
        ]);
    }
}
