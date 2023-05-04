<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\rules
 * @category   CategoryName
 */

namespace open20\amos\core\rules;

use open20\amos\core\helpers\StringHelper;
use open20\amos\core\record\Record;
use open20\amos\core\user\User;
use yii\rbac\Rule;

/**
 * Class ReadContextRule
 * @package open20\amos\core\rules
 */
class ReadContextRule extends Rule
{
    public $name = 'readContext';
    public $contextClass = 'models_classname_id';
    public $contextId = 'content_id';

    /**
     * @inheritdoc
     * This method checks if the current user has READ permission on the Model context (e.g. in case of comments, etc.)
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            /** @var Record $model */
            $model = $params['model'];
            $contextClass = $this->contextClass;
            $contextId = $this->contextId;
            $context = null;
            $baseName = StringHelper::baseName($model->className());
            // If no contextClass and contextId are set on the model, retrieve them from the request POST
            // ... else, use the ones already set to generate the context
            if (!$model->$contextClass || !$model->$contextId) {
                $post = \Yii::$app->getRequest()->post();
                if (isset($post[$baseName]))
                    $post = $post[$baseName];
                if (isset($post[$contextClass]) && isset($post[$contextId]))
                    $context = ($post[$contextClass])::findOne($post[$contextId]);
            } else {
                $context = ($model->$contextClass)::findOne($model->$contextId);
            }
            if (empty($context)) return false;
            // If there is a context, check read permissions on it
            return \Yii::$app->user->can(strtoupper(StringHelper::baseName($context->className())).'_READ', ['model' => $context]);
        } else {
            return false;
        }
    }
}
