<?php

/*
 * To change this proscription header, choose Proscription Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace open20\amos\core\giiamos\model;

/**
 * Description of GeneratoConfig
 *
 */
class GeneratoConfig {

    /**
     * 
     * @return array
     * Configuration array: 
     * - baseClassNames: possible classes the base model can extend
     * - baseInterfaceNames: possible interfaces that can implement the extended model
     * - for each case class (baseclassNames), the array with the respective interfaces implemented
     *
     * how to fill :
     *  [
     *      'baseClassNames' => [
     *              '<class name>' => '<fully qualified class name>',
     *              '...'
     *      ],
     *      'baseInterfaceNames' => [
     *             '<interface name>' => '<fully qualified interface name>',
     *              '...'
     *      ]
     *      '<baseclass name>' => [
     *              '<interface name>' => '<fully qualified interface name>',
     *              '...'
     *      ]
     *  ]
     */
    public static function getDefinition() {
        return [
            'baseClassNames' => [
                'ContentModel' => 'open20\amos\core\record\ContentModel',
                'Record' => 'open20\amos\core\record\Record',
                'NotifyRecord' => 'open20\amos\notificationmanager\record\NotifyRecord'
            ],
            'baseInterfaceNames' => [
                'CommentInterface' => 'open20\amos\comments\models\CommentInterface',
                'ContentModelInterface' => 'open20\amos\core\interfaces\ContentModelInterface',
                'ModelImageInterface' => 'open20\amos\core\interfaces\ModelImageInterface',
                'ViewModelInterface' => 'open20\amos\core\interfaces\ViewModelInterface',
                'SeoModelInterface' => 'open20\amos\seo\interfaces\SeoModelInterface',
                'NotifyRecordInterface' => 'open20\amos\notificationmanager\record\NotifyRecordInterface',
                'StatsToolbarInterface' => 'open20\amos\core\interfaces\StatsToolbarInterface',
                'ContentModelSearchInterface' => 'open20\amos\core\interfaces\ContentModelSearchInterface',
                'SearchModelInterface' => 'open20\amos\core\interfaces\SearchModelInterface',
            ],
            'ContentModel' => [
                'ContentModelInterface' => 'open20\amos\core\interfaces\ContentModelInterface',
                'ViewModelInterface' => 'open20\amos\core\interfaces\ViewModelInterface',
                'ContentModelSearchInterface' => 'open20\amos\core\interfaces\ContentModelSearchInterface',
                'ModelImageInterface' => 'open20\amos\core\interfaces\ModelImageInterface',
                'SearchModelInterface' => 'open20\amos\core\interfaces\SearchModelInterface',
            ],
            'Record' => [
                'StatsToolbarInterface' => 'open20\amos\core\interfaces\StatsToolbarInterface',
            ],
            'NotifyRecord' => [
                'NotifyRecordInterface' => 'open20\amos\notificationmanager\record\NotifyRecordInterface',
            ],
        ];
    }

}
