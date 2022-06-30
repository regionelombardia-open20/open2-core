<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use Yii;
use yii\base\Widget;

class LastUpdateModelWidget extends Widget
{
    public $layout = __DIR__ . "/views/widgets/widget_last_update_model.php";
    private $model = null;
    private $cssClass = '';
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;
    }
    
    public function getCssClass()
    {
        return $this->cssClass;
    }
    
    public function getLayout()
    {
        return $this->layout;
    }
    
    public function run()
    {
        $datet = $this->model->updated_at;
        
        $date_update = Yii::$app->formatter->asDate($datet);
        $time_update = Yii::$app->formatter->asTime($datet);
        return $this->renderFile($this->getLayout(), [
            'date_update' => $date_update,
            'time_update' => $time_update,
            'class' => $this->cssClass
        ]);
    }
}
