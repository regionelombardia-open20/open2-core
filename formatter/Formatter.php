<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\formatter
 * @category   CategoryName
 */

namespace lispa\amos\core\formatter;

use lispa\amos\core\module\BaseAmosModule;
use yii\i18n\Formatter as YiiFormatter;

/**
 * Class Formatter
 * @package lispa\amos\core\formatter
 */
class Formatter extends YiiFormatter
{
    public $tagsSeparator;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->tagsSeparator == null) {
            $this->tagsSeparator = ',';
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function asTags($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $tagsValues = explode($this->tagsSeparator, $value);

        $tagFormatter = '';
        foreach ($tagsValues as $tag) {
            $tagFormatter .= '<span class="formatter-tag">' . $tag . '</span>';
        }

        return $tagFormatter;
    }

    /**
     * @param $value
     * @return string
     */
    public function asCarteCredito($value)
    {
        $dimvalue = strlen($value);
        $newvalue = "";
        for ($i = 0; $i < $dimvalue; $i = $i + 4) {
            $newvalue .= " " . $value[$i] . $value[$i + 1] . $value[$i + 2] . $value[$i + 3];
        }
        return $newvalue;
    }

    /**
     * @param $value
     * @return string
     */
    public function asMaiuscolo($value)
    {
        $newvalue = strtoupper($value);
        return $newvalue;
    }

    /**
     * @param $value
     * @return string
     */
    public function asStatoattivo($value)
    {
        if ($value == 0) {
            $visStato = "Non Attivo";
            return $visStato;
        } else if ($value == 1) {
            $visStato = "Attivo";
            return $visStato;
        } else {
            return $this->nullDisplay;
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function asPrice($value)
    {
        $value = round($value, 2);
        $newvalue = "€ " . $value;
        return $newvalue;
    }

    /**
     * @param $value
     * @return string
     */
    public function asMinutiDaIntero($value)
    {
        $newvalue = $value . " minuti";
        if ($value == 1) {
            $newvalue = $value . " minuto";
        }
        return $newvalue;
    }

    /**
     * @param $value
     * @return string
     */
    public function asPercentuale($value)
    {
        $value = number_format($value, 2, '.', '');
        $newvalue = $value . " %";
        return $newvalue;
    }

    /**
     * @param $value
     * @return string
     */
    public function asStatoSiNo($value)
    {
        if ($value == 0) {
            $visStato = "No";
            return $visStato;
        } else if ($value == 1) {
            $visStato = "Si";
            return $visStato;
        } else {
            return $this->nullDisplay;
        }
    }

    /**
     * @param $value
     * @return string
     */
    public function asStripTags($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        } else
            return strip_tags($value);
    }

    /**
     * @param $start
     * @param null $end
     * @return string
     */
    private function formatDateDiff($start, $end = null)
    {
        if (!($start instanceof \DateTime)) {
            $start = new \DateTime($start);
        }

        if ($end === null) {
            $end = new \DateTime();
        }

        if (!($end instanceof \DateTime)) {
            $end = new \DateTime($start);
        }

        $interval = $end->diff($start);

        $doPlural = function ($nb, $str) {
            switch ($str) {
                case 'year':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'years') : BaseAmosModule::t('amoscore', 'year');
                    break;
                case 'month':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'months') : BaseAmosModule::t('amoscore', 'month');
                    break;
                case 'day':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'days') : BaseAmosModule::t('amoscore', 'day');
                    break;
                case 'hour':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'hours') : BaseAmosModule::t('amoscore', 'hour');
                    break;
                case 'minute':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'minutes') : BaseAmosModule::t('amoscore', 'minute');
                    break;
                case 'second':
                    $str = $nb > 1 ? BaseAmosModule::t('amoscore', 'seconds') : BaseAmosModule::t('amoscore', 'second');
                    break;
            }
            return $str . ' ' . BaseAmosModule::t('amoscore', 'ago');
        };

        /*
          $format = array();
          if ($interval->y !== 0) {
          $format[] = '%y ' . $doPlural($interval->y, 'year');
          }
          if ($interval->m !== 0) {
          $format[] = '%m ' . $doPlural($interval->m, 'month');
          }
         */

        if (($interval->m !== 0) || ($interval->y !== 0)) {
            return $start->format('d/m/Y') . ' ' . BaseAmosModule::t('amoscore', '#formatdatediff_at') . ' ' . $start->format('H:i');
        }

        /*
         * interval->d refer to days. Differences between days means the two dates have different days.
         * If interval->d = 1 means the end date is one day after start date.
         */
        if ($interval->d !== 0) {

            $datetime1 = new \DateTime($start->format('Y-m-d'));
            $datetime2 = new \DateTime($end->format('Y-m-d'));

            $interval2 = $datetime1->diff($datetime2);

            if ($interval2->d == 1) {
                if ($interval2->invert == 0) {
                    return BaseAmosModule::t('amoscore', 'yesterday at') . ' ' . $start->format('H:i');
                } else {
                    return BaseAmosModule::t('amoscore', 'tomorrow at') . ' ' . $start->format('H:i');
                }
            } else {
                return $start->format('d/m/Y') . ' ' . BaseAmosModule::t('amoscore', '#formatdatediff_at') . ' ' . $start->format('H:i');
            }
        }

        if ($interval->h !== 0) {

            $datetime1 = new \DateTime($start->format('Y-m-d'));
            $datetime2 = new \DateTime($end->format('Y-m-d'));

            $interval2 = $datetime1->diff($datetime2);

            if ($interval2->d == 1) {
                if ($interval2->format('%R%a') == '+1') {
                    return BaseAmosModule::t('amoscore', 'yesterday at') . ' ' . $start->format('H:i');
                }
                if ($interval2->format('%R%a') == '-1') {
                    return BaseAmosModule::t('amoscore', 'tomorrow at') . ' ' . $start->format('H:i');
                }
            }

            if ($interval->h < 6) {
                $format[] = '%h ' . $doPlural($interval->h, 'hour');
            } else {
                return BaseAmosModule::t('amoscore', 'today at') . ' ' . $start->format('H:i');
            }
        } elseif ($interval->i !== 0) {
            $format[] = '%i ' . $doPlural($interval->i, 'minute');
        } elseif ($interval->s >= 0) {
            return BaseAmosModule::t('amoscore', 'now');
            /*
             * else {
              $format[] = '%s ' . $doPlural($interval->s, 'second');
              }
             */
        }

        if (count($format) > 1) {
            $format = array_shift($format); //. ' and ' . array_shift($format);
        } else {
            $format = array_pop($format);
        }

        return $interval->format($format);
    }

    /**
     * @param \DateTime|int|string $value
     * @param string $format
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function asDatetime($value, $format = 'human')
    {
        if ($value) {
            if ($format == 'human') {
                if ($this->isValidTimeStamp($value)) {
                    $value = date('Y-m-d H:i:s', $value);
                }
                $dStart = new \DateTime($value);
                $dEnd = new \DateTime();
                return $this->formatDateDiff($dStart, $dEnd);
            } else {
                return parent::asDatetime($value, $format);
            }
        } else {
            return BaseAmosModule::t('amoscore', 'Not available');
        }
    }

    /**
     * @param $timestamp
     * @return bool
     */
    protected function isValidTimeStamp($timestamp)
    {
        return ((string)(int)$timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~PHP_INT_MAX);
    }
}
