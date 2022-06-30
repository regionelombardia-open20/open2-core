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
use yii\base\BaseObject;

/**
 * Class Email
 * @package open20\amos\core\utilities
 */
class Email extends BaseObject
{

    /**
     * @param string $from
     * @param array|string $to
     * @param $subject
     * @param $text
     * @param array $files
     * @param array $bcc
     * @param array $params
     * @param int $priority
     * @param bool $use_queue
     * @return bool
     */
    public static function sendMail(
    $from, $to, $subject, $text, array $files = [], array $bcc = [], $params = [], $priority = 0, $use_queue = false, $cc = [], $replyTo = [])
    {
        /** @var \open20\amos\emailmanager\AmosEmail $mailModule */
        $mailModule = Yii::$app->getModule("email");
        $errCnt     = 0;
        if (isset($mailModule)) {
            if (is_string($to)) {
                $to = [$to];
            } elseif (!is_string($to) && !is_array($to)) {
                return false;
            }
            foreach ($to as $recipient) {
                /** @var string $recipient */
                if ($use_queue) {
                    if (!$mailModule->queue($from, $recipient, $subject, $text, $files, $bcc, $params, $priority)) {
                        $errCnt++;
                    }
                } else {
                    if (!$mailModule->send($from, $recipient, $subject, $text, $files, $bcc, $params, true, $cc, $replyTo)) {
                        $errCnt++;
                    }
                }
            }

            return (($errCnt) ? false : true);
        }

        return false;
    }

    /**
     * Renders a view without applying layout.
     * This method differs from [[render()]] in that it does not apply any layout.
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @param integer $user_id for get user configurations.
     * @return string the rendering result.
     */
    public static function renderMailPartial($view, $params = [], $user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = \Yii::$app->getUser()->id;
        }
        if (empty($user_id)) {
            self::setGuestLanguage();
        } else {
            self::setUserLanguage($user_id);
        }
        if (Yii::$app->controller instanceof \yii\base\Controller) {
            $value = Yii::$app->controller->renderPartial($view, $params);
            if (!is_null($user_id)) {
                if (\Yii::$app instanceof \yii\web\Application) {
                    self::setUserLanguage(\Yii::$app->getUser()->id);
                }
            } else {
                self::setGuestLanguage();
            }
        }
        return $value;
    }

    /**
     * @param int $user_id
     */
    public static function setUserLanguage($user_id)
    {
        $module = \Yii::$app->getModule('translation');
        if ($module && !empty($module->enableUserLanguage) && $module->enableUserLanguage == true) {
            $lang = $module->getUserLanguage($user_id);
            $module->setAppLanguage($lang);
        }
    }

    /**
     * Set the guest lang
     */
    public static function setGuestLanguage()
    {
        $module = \Yii::$app->getModule('translation');
        if ($module && !empty($module->enableUserLanguage) && $module->enableUserLanguage == true) {
            if (method_exists($module, 'getGuestLanguage')) {
                $lang = $module->getGuestLanguage();
                $module->setAppLanguage($lang);
            }
        }
    }
}