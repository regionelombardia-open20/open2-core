<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\views\comment\email
 * @category   CategoryName
 */

use open20\amos\core\helpers\Html;
use open20\amos\core\interfaces\BaseContentModelInterface;
use open20\amos\core\interfaces\ViewModelInterface;
use open20\amos\cwh\base\ModelContentInterface;
use open20\amos\core\module\BaseAmosModule;

/**
 * @var \open20\amos\core\record\Record $contextModel
 * @var \open20\amos\core\record\Record $model
 * @var \open20\amos\core\record\Record $model_reply
 */

if (!empty($user)) {
    $this->params['profile'] = $user->userProfile;
}

$link = '#comments_anchor';
$linkText = $contextModel->__toString();
$description = '-';

if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getTitle')) {
    $linkText = $contextModel->getTitle();
}

if (method_exists($contextModel, 'getFullViewUrl')) {
    $link = \Yii::$app->urlManager->createAbsoluteUrl($contextModel->getFullViewUrl()) . "#comments_anchor";
}

if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getDescription')) {
    $description = $contextModel->getDescription(true);
}

?>

<div style="border:1px solid #cccccc;padding:10px;margin-bottom: 10px;background-color: #ffffff;margin-top:20px">
    
    <div style="box-sizing:border-box;font-size:13px;font-weight:normal;">
        
        Ciao <?= $user->userProfile->getNomeCognome() ?>,
        <br>

        <?php 
            if($model->isNewRecord){
                if(empty($model->created_by))
                {
                        $model->created_by = Yii::$app->user->id;
                }
                echo $model->getCreatedUserProfile()->one()->getNomeCognome(); 
            }
            else
            {
                $model->updated_by = Yii::$app->user->id;
                echo $model->getUpdatedUserProfile()->one()->getNomeCognome();
            }
        ?>

        <?= BaseAmosModule::t('amoscore', 'ti ha taggato in un contenuto.') ?>
           
        <br>
    </div>

    <div style="margin-top:20px">
          <?php
            $moduleCwh  = Yii::$app->getModule('cwh');
            $moduleCommunity = Yii::$app->getModule('community');
            $scope = null;
            $communityId = null;
            if (!empty($moduleCwh)) {
                /** @var \open20\amos\cwh\AmosCwh $moduleCwh */
                $scope = $moduleCwh->getCwhScope();
            }
            if (!empty($scope)) {
                if (isset($scope['community'])) {
                    $communityId = $scope['community'];
                }
            }
            $baseUrl = (!empty(\Yii::$app->params['platform']['backendUrl']) ? \Yii::$app->params['platform']['backendUrl'] : '');
            if(!is_null($communityId) && $moduleCommunity->enableOpenJoin)
            {
                $link = $baseUrl. "/community/join/open-join?id=" . $communityId . "&subscribe=1&urlRedirect=".$link . "&contentId=". $contextModel->id . "&contextClassName=" . str_replace('\\', '\\\\', $model->className());
            }else{
                $link = $baseUrl. "/myactivities/my-activities/read?id=" . $contextModel->id . "&contextClassName=" . str_replace('\\', '\\\\', $model->className()) . "&url=".$link;
            }
          
           ?>
        <?= BaseAmosModule::t('amoscore', 'Clicca qui per vedere il contenuto: ') ?> <?= Html::a($linkText, $link, ['style' => 'color: green;']); ?>
    </div>

</div>
