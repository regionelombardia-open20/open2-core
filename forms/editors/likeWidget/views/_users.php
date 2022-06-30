<?php
use open20\amos\admin\AmosAdmin;
use open20\amos\admin\widgets\ConnectToUserWidget;
use open20\amos\admin\widgets\SendMessageToUserWidget;
use open20\amos\admin\models\UserContact;
use open20\amos\core\helpers\Html;
use open20\amos\core\module\BaseAmosModule;

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

echo \open20\amos\core\views\AmosGridView::widget([
  'id' => 'grid-content-like',
  'dataProvider' => $dataProvider,
  'columns' => [
    'Photo' => [
      'headerOptions' => [
        'id' => BaseAmosModule::t('amoscore', 'Photo'),
      ],
      'contentOptions' => [
        'headers' => BaseAmosModule::t('amoscore', 'Photo'),
      ],
      'label' => BaseAmosModule::t('amoscore', 'Photo'),
      'format' => 'raw',
      'value' => function ($model) {
        /** @var \open20\amos\admin\models\UserProfile $userProfile */
        $userProfile = $model->user->getProfile();
        return \open20\amos\admin\widgets\UserCardWidget::widget(['model' => $userProfile]);
      }
    ],
    'nomeCognome',
    [
      'class' => open20\amos\core\views\grid\ActionColumn::class,
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
                Html::a(BaseAmosModule::t('amoscore', 'Rifiuta invito'), 
                ['/admin/user-contact/connect', 'contactId' => $uid, 'userId' => $model->user_id, 'accept' => 0], 
                ['class' => 'btn btn-navigation-primary']
                )
                . Html::a(BaseAmosModule::t('amoscore', 'Accetta invito'), 
                ['/admin/user-contact/connect', 'contactId' => $uid, 'userId' => $model->user_id, 'accept' => 1], 
                ['class' => 'btn btn-navigation-primary']
                );
              }
              
              $status = UserContact::find()
                ->where(['user_id' => $uid])
                ->andWhere(['contact_id' => $model->user_id])
                ->one();
                  
              if (($status) && ($status->status == UserContact::STATUS_INVITED)) {
                return Html::tag('em', BaseAmosModule::t('amoscore', 'In attesa di accettazione della richiesta'));                        }
              
              return Html::a(BaseAmosModule::t('amoscore', 'collegati'), 
                ['/admin/user-contact/connect', 'contactId' => $model->user_id], 
                ['class' => 'btn btn-navigation-primary', 'data-confirm' => BaseAmosModule::t('amoscore', 'Vuoi collegarti?')]
                );
            } else {
              if(!empty($moduleChat)) {
                return Html::a(
                  BaseAmosModule::t('amoscore', 'Invia messaggio'),
                  ['/messages', 'contactId' => $model->user_id],
                  ['class' => 'btn btn-navigation-primary', 'data-confirm' => BaseAmosModule::t('amoscore', 'Vuoi inviare un messaggio?')]);
              }
            }
          }
        }
      ]
    ],
  ]
]);
\yii\widgets\Pjax::end();