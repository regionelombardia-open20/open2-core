# amos-core
Amos core

### using the loader (SpinnerWaitAsset)
in the view insert che code below
```php
 \open20\amos\layout\assets\SpinnerWaitAsset::register($this);
 <div class="loading" id="loader" hidden></div>
```
use the two javascript functions below where you need it
```php
    $('.loading').show();
    $('.loading').hide();
```

## Platform params
#####  hideSettings
Hide the setting gear in the navbar, 
If you want to hide the link for all
```php
      'hideSettings' => true,
```
if you want to hide the link for some users
```php
      'hideSettings' => [
          'roles' => ['ROLE1', 'ROLE2']
      ],
```

if you want to export only the element visualized in the grid
```php
      'disableExportAll' => true
```


 
##  Widget share social
Add in backend/config/components-others
```php
        'socialShare' => [
            'class' => \open20\amos\core\components\ConfiguratorSocialShare::class,
        ],
```
##### Parameters #####
* **mode** - string, default = SocialShareWidget::NORMAL, the otther mode is SocialShareWidget::DROPDOWN

example of usage:
```php

         <?= \open20\amos\core\forms\editors\socialShareWidget\SocialShareWidget::widget([
                'configuratorId' => 'socialShare',
                'model' => $model,
                'url'           => \yii\helpers\Url::to(\Yii::$app->params['platform']['backendUrl'].'/news/news/view?id='.$model->id, true),
                'title'         => $model->title,
                'description'   => $model->descrizione_breve,
//                'imageUrl'      => \yii\helpers\Url::to('absolute/route/to/image.png', true),

            ]); ?>
```

###  Custom the Frontend View Url 
- Implement the interface CustomUrlModelInterface
- Add in common/config/params
```php
        'urlFrontend' => [
                'NewsModel' => '/news/news/{Id}/{Slug}',
            ],
```

### Platform Parameters ###
* **enablePageCache** - boolean; 
If the params is true enable the page cache
