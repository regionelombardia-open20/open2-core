<?php
use lispa\amos\admin\AmosAdmin;
use lispa\amos\admin\widgets\ConnectToUserWidget;
use lispa\amos\admin\widgets\SendMessageToUserWidget;
use lispa\amos\admin\models\UserContact;
use lispa\amos\core\helpers\Html;

use yii\helpers\ArrayHelper;

$adminModule = AmosAdmin::instance();
$moduleChat = \Yii::$app->getModule('chat');
$uid = \Yii::$app->user->id;

\yii\widgets\Pjax::begin([
  'id' => 'pjax-container-content-like',
  'timeout' => 2000,
  'enablePushState' => false,
  'enableReplaceState' => false,
  'clientOptions' => ['data-pjax-container' => 'grid-content-like']]);

echo \lispa\amos\core\views\AmosGridView::widget([
  'id' => 'grid-content-like',
  'dataProvider' => $dataProvider,
  'columns' => [
    'Photo' => [
      'headerOptions' => [
        'id' => \Yii::t('amoscore', 'Photo'),
      ],
      'contentOptions' => [
        'headers' => \Yii::t('amoscore', 'Photo'),
      ],
      'label' => \Yii::t('amoscore', 'Photo'),
      'format' => 'raw',
      'value' => function ($model) {
        /** @var \lispa\amos\admin\models\UserProfile $userProfile */
        $userProfile = $model->user->getProfile();
        return \lispa\amos\admin\widgets\UserCardWidget::widget(['model' => $userProfile]);
      }
    ],
    'nomeCognome',
    [
      'class' => lispa\amos\core\views\grid\ActionColumn::class,
      'template' => '{connect}',
      'buttons' => [
        'connect' => function($url, $model) use ($adminModule, $userNetwork, $uid, $moduleChat) {
          if ($model->user_id != $uid) {
            if (ArrayHelper::isIn($model->user_id, $userNetwork) === false) {
              
              $status = UserContact::find()
                ->where(['contact_id' => $uid])
                ->andWhere(['user_id' => $model->user_id])
                ->andWhere(['deleted_at' => null])
                ->one();
              
              if (($status) && ($status->status == UserContact::STATUS_INVITED)) {
                return 
                Html::a(\Yii::t('amoscore', 'Rifiuta invito'), 
                ['/admin/user-contact/connect', 'contactId' => $uid, 'userId' => $model->user_id, 'accept' => 0], 
                ['class' => 'btn btn-navigation-primary']
                )
                . Html::a(\Yii::t('amoscore', 'Accetta invito'), 
                ['/admin/user-contact/connect', 'contactId' => $uid, 'userId' => $model->user_id, 'accept' => 1], 
                ['class' => 'btn btn-navigation-primary']
                );
              }
              
              $status = UserContact::find()
                ->where(['user_id' => $uid])
                ->andWhere(['contact_id' => $model->user_id])
                ->one();
                  
              if (($status) && ($status->status == UserContact::STATUS_INVITED)) {
                return Html::tag('em', \Yii::t('amoscore', 'In attesa di accettazione della richiesta'));                        }
              
              return Html::a(\Yii::t('amoscore', 'collegati'), 
                ['/admin/user-contact/connect', 'contactId' => $model->user_id], 
                ['class' => 'btn btn-navigation-primary', 'data-confirm' => Yii::t('amoscore', 'Vuoi collegarti?')]
                );
            } else {
              if(!empty($moduleChat)) {
                return Html::a(
                  \Yii::t('amoscore', 'Invia messaggio'),
                  ['/messages', 'contactId' => $model->user_id],
                  ['class' => 'btn btn-navigation-primary', 'data-confirm' => Yii::t('amoscore', 'Vuoi inviare un messaggio?')]);
              }
            }
          }
        }
      ]
    ],
  ]
]);
\yii\widgets\Pjax::end();