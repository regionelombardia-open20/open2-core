<?php

/*
 * To change this proscription header, choose Proscription Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lispa\amos\core\giiamos\model;

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
                'ContentModel' => 'lispa\amos\core\record\ContentModel',
                'Record' => 'lispa\amos\core\record\Record',
                'NotifyRecord' => 'lispa\amos\notificationmanager\record\NotifyRecord'
            ],
            'baseInterfaceNames' => [
                'CommentInterface' => 'lispa\amos\comments\models\CommentInterface',
                'ContentModelInterface' => 'lispa\amos\core\interfaces\ContentModelInterface',
                'ModelImageInterface' => 'lispa\amos\core\interfaces\ModelImageInterface',
                'ViewModelInterface' => 'lispa\amos\core\interfaces\ViewModelInterface',
                'SeoModelInterface' => 'lispa\amos\seo\interfaces\SeoModelInterface',
                'NotifyRecordInterface' => 'lispa\amos\notificationmanager\record\NotifyRecordInterface',
                'StatsToolbarInterface' => 'lispa\amos\core\interfaces\StatsToolbarInterface',
                'ContentModelSearchInterface' => 'lispa\amos\core\interfaces\ContentModelSearchInterface',
                'SearchModelInterface' => 'lispa\amos\core\interfaces\SearchModelInterface',
            ],
            'ContentModel' => [
                'ContentModelInterface' => 'lispa\amos\core\interfaces\ContentModelInterface',
                'ViewModelInterface' => 'lispa\amos\core\interfaces\ViewModelInterface',
                'ContentModelSearchInterface' => 'lispa\amos\core\interfaces\ContentModelSearchInterface',
                'ModelImageInterface' => 'lispa\amos\core\interfaces\ModelImageInterface',
                'SearchModelInterface' => 'lispa\amos\core\interfaces\SearchModelInterface',
            ],
            'Record' => [
                'StatsToolbarInterface' => 'lispa\amos\core\interfaces\StatsToolbarInterface',
            ],
            'NotifyRecord' => [
                'NotifyRecordInterface' => 'lispa\amos\notificationmanager\record\NotifyRecordInterface',
            ],
        ];
    }

}
