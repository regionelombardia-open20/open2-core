<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\likeWidget
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors\likeWidget;

use Yii;
use yii\base\Widget;

/**
 * Class LikeWidget
 * @package open20\amos\core\forms\editors\likeWidget
 */
class LikeWidget extends Widget
{
    const MODE_NORMAL = 'normal';

    public
        $model,
        $wrapperTag = 'div',
        $wrapperOptions = ['class' => 'container-like'],
        $linkWrapperTag = 'div',
        $linkWrapperOptions = ['class' => 'like-wrap-button'],
        $enableModalLike = true,
        $mode = self::MODE_NORMAL;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (isset(\Yii::$app->params['disableLike']) && (\Yii::$app->params['disableLike'] === true)) {
            return '';
        }

        $uid = Yii::$app->user->id;
        $cid = $this->model->id;
        $mid = \open20\amos\core\models\ModelsClassname::find()
            ->where(['classname' => $this->model->className()])
            ->one()->id;

        $tot = \open20\amos\core\models\ContentLikes::getLikesToCounter($uid, $cid, $mid);
        $lme = \open20\amos\core\models\ContentLikes::getLikeMe($uid, $cid, $mid);

        return $this->render(
            '_like',
            [
                'uid' => $uid,
                'cid' => $cid,
                'mid' => $mid,
                'tot' => $tot,
                'lme' => ($lme == 1) ? 'likeme' : 'notlikeme'
            ]);
    }
}
