<?php
//don't ever change these. they are nested within the plugins core and only for adminstrative changes
define('WSKO_LRS_TIMEOUT', (60*10));
$fs = wsko_fs();
if ($fs->is_plan('pro'))
    define('WSKO_SEARCH_ROW_LIMIT', 10000);
else if ($fs->is_plan('advanced'))
    define('WSKO_SEARCH_ROW_LIMIT', 15000);
else if ($fs->is_plan('business'))
    define('WSKO_SEARCH_ROW_LIMIT', 20000);
else if ($fs->is_plan('enterprise'))
    define('WSKO_SEARCH_ROW_LIMIT', 25000);
else
    define('WSKO_SEARCH_ROW_LIMIT', 5000);

define('WSKO_SEARCH_INITIAL_REPORTS', 6);//9);
define('WSKO_SEARCH_INITIAL_REPORT_SIZE', 10);
define('WSKO_SEARCH_REPORT_SIZE', 60);//57);//87);

define('WSKO_ANALYTICS_ROWS_LIMIT', 10000);
define('WSKO_ONPAGE_MAX_LEVEL', 5);

define('WSKO_BACKLINK_FETCH_INTERVAL', 7);
define('WSKO_ONPAGE_FETCH_INTERVAL', 7);

define('WSKO_BACKLINK_LOST_TRESHOLD', 90);

define('WSKO_PERFORMANCE_ANALYTICS_TIMERANGE', 30);

define('WSKO_FEEDBACK_MAIL', 'feedback@bavoko.tools');
define('WSKO_SUPPORT_MAIL', 'support@bavoko.tools');
define('WSKO_CONTACT_URL', 'https://www.bavoko.tools/contact/');

define('WSKO_GOOGLE_MAPS_API_KEY', 'AIzaSyDbF_7pWUsNdc6BntDtPZ_azWHsomoIvPA');

define('WSKO_API_FACEBOOK_ID', '1999765900281727');
define('WSKO_API_FACEBOOK_SECRET', '7665f66023a986061751840af418f6da');

define('WSKO_API_TWITTER_CONSUMER', 'cXSNwYU3mpaLlozKg9FKlhuls');
define('WSKO_API_TWITTER_CONSUMER_SECRET', 'DwJWWEzL3WwHs8O8z4suH688ku18LrldFhGQOrV9NohvLGtnII');
define('WSKO_API_TWITTER_TOKEN', '880465199953850372-ogRsWbiI7zPk7Bp9HdqkWtxM6p621MM');
define('WSKO_API_TWITTER_TOKEN_SECRET', 'AIMbeVPC2lfRB1bCM34SvHnibdhcVZkLh5r9ehssYfXDO');

define('WSKO_FEEDBACK_API', 'https://www.bavoko.tools/ext/v1/report-api.php');
define('WSKO_CRAWLER_IP', '18.191.27.97');
?>