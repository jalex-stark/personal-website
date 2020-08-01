<?php
//you are free to edit the values below (on your own risk)
define('WSKO_POST_TYPE_ERROR', 'wsko_log_report'); //post type name of the log posts. needs to start with "wsko_" for security reasons!
define('WSKO_LINKING_PREVIEW_LENGTH', 50); //linking preview widget additional text length (front and rear)
define('WSKO_SITEMAP_LIMIT', 50000); //sitemap pagination limit


//Onpage Tresholds
define('WSKO_ONPAGE_ISSUE_MAX_POSTS', 5000); //max url length
define('WSKO_ONPAGE_URL_MAX', 65); //max url length

//metas
define('WSKO_ONPAGE_TITLE_MIN', 45);
define('WSKO_ONPAGE_TITLE_MAX', 70);
define('WSKO_ONPAGE_DESC_MIN', 100);
define('WSKO_ONPAGE_DESC_MAX', 160);

//facebook
define('WSKO_ONPAGE_FB_TITLE_MIN', 10);
define('WSKO_ONPAGE_FB_TITLE_MAX', 90);
define('WSKO_ONPAGE_FB_DESC_MIN', 10);
define('WSKO_ONPAGE_FB_DESC_MAX', 200);

//twitter
define('WSKO_ONPAGE_TW_TITLE_MIN', 10);
define('WSKO_ONPAGE_TW_TITLE_MAX', 70);
define('WSKO_ONPAGE_TW_DESC_MIN', 10);
define('WSKO_ONPAGE_TW_DESC_MAX', 200);

//Misc
define('WSKO_HISTORY_LIMIT', 365); //days till a history item is deleted
?>