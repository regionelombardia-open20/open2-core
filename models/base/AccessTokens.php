<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    amos-mobile-bridge
 * @category   CategoryName
 */
namespace open20\amos\core\models\base;

use open20\amos\core\module\BaseAmosModule;
use Yii;

/**
 * This is the model class for table "access_tokens".
 *
 */
class AccessTokens extends \open20\amos\core\record\Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'logout_by', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['device_info'], 'string'],
            [['logout_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['access_token', 'ip'], 'string', 'max' => 32],
            [['location'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'access_token' => BaseAmosModule::t('amoscore', 'Access Token'),
            'user_id' => BaseAmosModule::t('amoscore', 'User id'),
            'device_info' => BaseAmosModule::t('amoscore', 'Device info'),
            'ip' => BaseAmosModule::t('amoscore', 'IP info'),
            'location' => BaseAmosModule::t('amoscore', 'Location'),
            'logout_at' => BaseAmosModule::t('amoscore', 'Logout At'),
            'logout_by' => BaseAmosModule::t('amoscore', 'Logout By'),
            'created_at' => BaseAmosModule::t('amoscore', 'Created At'),
            'created_by' => BaseAmosModule::t('amoscore', 'Created By'),
            'updated_at' => BaseAmosModule::t('amoscore', 'Updated At'),
            'updated_by' => BaseAmosModule::t('amoscore', 'Updated By'),
            'deleted_at' => BaseAmosModule::t('amoscore', 'Deleted At'),
            'deleted_by' => BaseAmosModule::t('amoscore', 'Deleted By'),
        ];
    }
}
