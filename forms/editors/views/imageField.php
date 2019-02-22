<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\editors\assets\views
 * @category   CategoryName
 */

use lispa\amos\core\icons\AmosIcons;
?>
<div class="fileinput fileinput-new" data-provides="fileinput">
 <div class="fileinput-new thumbnail">
  <?=$thumbnail;?>
 </div>
 <div class="fileinput-preview fileinput-exists thumbnail"></div>
 <div class="container-btn">
  <span class="btn btn-file btn-block nom no-border-radius nop">

   <!-- new image -->
   <span class="fileinput-new btn btn-navigation-primary-inverse btn-upload">
    <?= AmosIcons::show('upload', ['id' => 'bk-btnImport-new']) ?>
   </span>

   <!-- uploading image -->
   <span class="fileinput-exists btn btn-navigation-primary-inverse btn-upload">
    <?= AmosIcons::show('upload', ['id' => 'bk-btnImport-upl']) ?>
   </span>
   <?=$field;?>
   <a href="#" class="btn btn-action-secondary btn-block fileinput-exists" data-dismiss="fileinput" title="delete image">
    <?= AmosIcons::show('delete') ?>
   </a>

  </span>
 </div>
</div>