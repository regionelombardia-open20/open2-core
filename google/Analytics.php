<?php

namespace open20\amos\core\google;

use yii\helpers\Html;

/**
 * USAGE
 * Required properties
 * - analyticsID Google analyticsID.You can get the this value from this link https://ga-dev-tools.appspot.com/account-explorer/
 * Optional properties
 * - startDate Start date for fetching Analytics data. Requests can specify a start date formatted as YYYY-MM-DD, or as
 * a relative date (e.g., today, yesterday, or NdaysAgo where N is a positive integer). Defaut to - 30daysAgo
 * - endDate End date for fetching Analytics data. Request can specify an end date formatted as YYYY-MM-DD, or as a
 * relative date (e.g., today, yesterday, or NdaysAgo where N is a positive integer). Defaut to today
 * - metrics A list of comma-separated metrics, such as ga:sessions,ga:bounces.Default to ga:sessions.
 * - dimensions A list of comma-separated dimensions for your Analytics data, such as ga:browser,ga:city. Default to ga:browser
 * - container_id The div id to be generated default to analayticsData.Add if uses more than one widget.
 * - extraFields The extra fields as array which are specified in this link
 * https://developers.google.com/analytics/devguides/reporting/core/v3/reference#q_summary
 * - chartType (string)The type of chart to be dispalay. The values may be LINE,BAR,TABLE etc..
 *
 * GOOGLE AUTHENTICATION
 * Google Authentication is Required to recive the data.
 * Go to the Google developers console
 * Create a new project (or use an existing project)
 * Open the project
 * Enable Analytics API
 * In Credentials page Create service account key or use Existing key. Download the file in json Format.
 * Put the file in @app/assets/certificate/service-account-credentials.json(rename the file).
 * Add the mailId of Service Account (we just created) into user Give ServiceAccount Access to the Analytics.
 * (Add new user with Service-account-ID with Read & Analyze permissions)
 *
 */
class Analytics extends \yii\base\Widget
{
    public $connect      = null;
    public $startDate;
    public $endDate;
    public $metrics      = 'ga:sessions';
    public $dimensions   = 'ga:browser';
    public $filters = '';
    public $container_id = 'analayticsData';
//    public $sort=false,$filters=false;
    public $extraFields  = [];
    public $chartType    = 'LINE';
    public $analyticsID;
    public $accesstoken;

    const SESSIONS               = 'sessions';
    const VISITORS               = 'visitors';
    const COUNTRIES              = 'countries';
    const TOTAl_SESSIONS         = 'total_sessions';
    const TOTAL_USERS            = 'total_users';
    const TOTAL_PAGE_VIEWS       = 'total_page_views';
    const AVERAGE_SESSION_LENGTH = 'average_session_length';

    /**
     * Initializes the widget
     */
    public function init()
    {
        parent::init();

        $this->connect = new Connect;
        // Set default values
        // @todo Find a better way to do this
        if (!isset($this->startDate)) {
            $this->startDate = '30daysAgo';
        }
        if (!isset($this->endDate)) {
            $this->endDate = 'today';
        }
    }

    public function run()
    {

        $view             = \Yii::$app->getView();
        $view->registerJs("
            (function(w,d,s,g,js,fs){
              g=w.gapi||(w.gapi={});g.analytics={q:[],ready:function(f){this.q.push(f);}};
              js=d.createElement(s);fs=d.getElementsByTagName(s)[0];
              js.src='https://apis.google.com/js/platform.js';
              fs.parentNode.insertBefore(js,fs);js.onload=function(){g.load('analytics');};
            }(window,document,'script'));
            ");
        $show             = Html::tag('div', '', ['id' => $this->container_id]);
        $chartvar         = 'chart'.mt_rand(111111, 999999999);
        $additionalParams = [];
        if (!empty($this->extraFields)) {
            foreach ($this->extraFields as $key => $option) {
                $additionalParams[] = "'".$key."':'".$option."'";
            }
        }

        $view->registerJs("gapi.analytics.ready(function() {
gapi.analytics.auth.authorize({
    'serverAuth': {
      'access_token': '".$this->connect->accessToken."'
    }
  });
  var ".$chartvar." = new gapi.analytics.googleCharts.DataChart({
    query: {
      'ids': '".$this->analyticsID."', // <-- Replace with the ids value for your view.
      'start-date': '".$this->startDate."',
      'end-date': '".$this->endDate."',
      'metrics': '".$this->metrics."',
      'dimensions': '".$this->dimensions."',"
      . (!empty($this->filters) ? ("'filters': '" . $this->filters ."'") : '')
      .implode(',', $additionalParams)."
    },
    chart: {
      'container': '".$this->container_id."',
      'type': '".$this->chartType."',
      'options': {
        'width': '100%'
      }
    }
  });
  ".$chartvar.".execute();
  });");
        return $show;
    }
}
