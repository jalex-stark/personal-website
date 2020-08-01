<?php
if (!defined('ABSPATH')) exit;

class WSKO_Class_Localization
{
    public static function translate($section, $id, $params = array(), $default = false)
    {
        $str = '-';
        switch ($section)
        {
            case 'general':
                switch ($id)
                {
                    case 'plugins_widget_head': $str = __('Get more data & more tools with BAVOKO SEO Tools PRO.', 'wsko'); break;
                    case 'plugins_widget_content': $str = __('More SEO Keywords, Search Volume & CPC, Competitors, External Crawler, 1-Click Solution for Internal Links, Backlink Analysis, Performance Analysis & More...', 'wsko'); break;
                    case 'pro_advantages_list': $str = array(
                        __('<li><i class="fa fa-check fa-fw"></i>More data: Google Analytics API, BAVOKO API & external crawler</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>More SEO keywords & priority system in the Content Optimizer</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Search volume & CPC data in addition to ranking keywords</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Ranking analysis by countries & devices</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Keyword research tools with search volume & CPC data</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Competitor monitoring for SEO rankings & Adwords</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>More onpage data through the external crawler</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>One-click solution for internal links</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Backlink monitoring with relevant domain data</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Disavow tool for low-quality backlinks</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Extensive Pagespeed analysis for all pages</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Detailed request analysis for CSS, Javascript & image files</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Automatic e-mail reporting</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Export function for all SEO data in the backend</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Premium support: High priority in our support system</li>', 'wsko'),
                        __('<li><i class="fa fa-check fa-fw"></i>Coming soon: Rich snippets, CTR A/B testing, Requests Manager</li>', 'wsko')
                    ); break;
                }
                break;
            case 'tour':
                switch ($id)
                {
                    case 'intro_title_demo': $str = __('BAVOKO SEO Tools Demo', 'wsko'); break;
                    case 'intro_content_demo': $str = __('Welcome to the demo of BAVOKO SEO Tools! Start the tour for a little introduction to our WordPress SEO plugin. If you have any questions or just want to leave us a feedback, message us with a click on the live chat button in the bottom right :-)', 'wsko'); break;
                    case 'intro_title': $str = __('Welcome!', 'wsko'); break;
                    case 'intro_content': $str = __('After you\'ve completely set up BAVOKO SEO Tools, it can take up to an hour for all the data to be fully available for SEO analysis in the backend. Start the plugin tour now for a little introduction to BAVOKO SEO Tools!', 'wsko'); break;
                    
                    case 'dashboard_title': $str = __('SEO Dashboard', 'wsko'); break;
                    case 'dashboard_content': $str = __('After the API connections and the Onpage Crawler have completely saved the data, you will find the most important SEO KPIs and history data of all sections in the Dashboard of BAVOKO SEO Tools.', 'wsko'); break;
                    case 'search_title': $str = __('Ranking Analysis & Keyword Research', 'wsko'); break;
                    case 'search_content': $str = __('Inside the "Search" section you can analyze your rankings in detail by pages, keywords, countries & devices (PRO), work with different keyword research tools (PRO) and track your most important search terms within the SEO monitoring tool. ', 'wsko'); break;
                    case 'onpage_title': $str = __('Onpage SEO Analysis', 'wsko'); break;
                    case 'onpage_content': $str = __('Within the onpage analysis you can check your pages for the most important SEO aspects such as titles, descriptions, content length, or even indexability (PRO) and optimize them in a unique workflow without having to leave the analysis.', 'wsko'); break;
                    case 'backlinks_title': $str = __('Backlink Analysis', 'wsko'); break;
                    case 'backlinks_content': $str = __('Analyze referring domains and pages within the backlink views and use the disavow tool to mark spam links and to submit them to Google with a few clicks.', 'wsko'); break;
                    case 'performance_title': $str = __('Pagespeed & Requests (PRO)', 'wsko'); break;
                    case 'performance_content': $str = __('Within the performance section you can not only analyze your pagespeed, but also check the individual CSS, javascript and image requests of your website in detail.', 'wsko'); break;
                    case 'tools_title': $str = __('SEO Tools', 'wsko'); break;
                    case 'tools_content': $str = __('Here you will find various SEO tools for metas, redirects, permalinks, sitemaps and much more. We recommend you to carefully review all settings in this section after this tour and take a closer look at the corresponding articles in our knowledge base.', 'wsko'); break;
                    case 'workflow_title': $str = __('SEO Workflow', 'wsko'); break;
                    case 'workflow_content': $str = __('Within the analysis views, you can edit your single pages without leaving the view by clicking on the CO symbol.', 'wsko'); break;
                    case 'co_title': $str = __('Content Optimizer', 'wsko'); break;
                    case 'co_content': $str = __('With the Content Optimizer you can analyze and optimize your single pages by using various tools for content optimization, keywords, meta tags, social snippets, page rankings and more. You can reach it within your posts and pages, as well as in the analysis area via this popup.', 'wsko'); break;
                    case 'intro_title_demo': $str = __('End of Tour', 'wsko'); break;
                    case 'intro_content_demo': $str = __('The tour is over! Thank you for your kind attention! Click the following link for more information about BAVOKO SEO Tools: <a href="%kb_link%">Getting Started with BAVOKO SEO Tools</a>. If you have any questions, just message us with a click on the live chat button in the bottom right.', 'wsko'); break;
                    case 'intro_title': $str = __('End of Tour', 'wsko'); break;
                    case 'intro_content': $str = __('The tour is over! Thank you for your kind attention! Until the SEO data is available for analysis, we recommend you to set up your metas, permalinks and sitemap under "Tools" as a next step. Click the following link for more information about BAVOKO SEO Tools: <a href="%kb_link%" target="_blank">Getting Started in BAVOKO SEO Tools</a>. Good luck with your rankings!', 'wsko'); break;
                }
                break;
            case 'notif':
                switch ($id)
                {
                    case 'no_admin_api': $str = __('Only Admins can edit API settings.', 'wsko'); break;
                    case 'no_permission': $str = __('You have no permission to use BAVOKO SEO Tools!', 'wsko'); break;
                    case 'setup_non_admin': $str = __('Only an Admin can setup the plugin!', 'wsko'); break;
                    case 'admin_no_controller': $str = __('Controller could not be loaded!', 'wsko'); break;
                    case 'iframe_incomplete': $str = __('Incomplete call', 'wsko'); break;
                    case 'support_mail_fail': $str = __('Your mail could not be sent. Maybe there is something wrong with your SMTP configuration. You can still try to contact us <a href="'.WSKO_CONTACT_URL.'">here</a>.', 'wsko'); break;
                    case 'api_unavailable' : $str = __('API is currently unavailable. Please try again in a few minutes.', 'wsko'); break;
                    case 'google_api_error': $str = __("Google API 2.1.0 couldn't be loaded, because you are already loading another version (%%old_ver%%) of it. This has caused a failure with the client.", 'wsko'); break;
                    case 'google_api_warning': $str = __("Google API 2.1.0 couldn't be loaded, because you are already loading another version (%%old_ver%%) of it. This may cause errors with deprecated methods, if your version is outdated.", 'wsko'); break;
                    case 'google_api_error_sub': $str = __('This error is usually caused by a plugin or theme loading an old version of the Google Client API. Updating your plugins and themes may solve this error.', 'wsko'); break;
                    case 'google_api_error_box': $str = __('API could not be loaded. Another plugin may have loaded an earlier version of the Google Client API.', 'wsko'); break;
                    case 'system_cronjobs': $str = __('One or more system CronJobs are not running. ', 'wsko'); break;
                    case 'help_article_fail': $str = __('The Article could not be loaded. Please try again in a few minutes.', 'wsko'); break;
                    case 'kb_empty': $str = __('Knowledge Base Articles could not be loaded. Please try again in a few minutes.', 'wsko'); break;
                    case 'timerange_unsupported': $str = __('Not available with a custom time-range. ', 'wsko'); break;
                    case 'api_setup_controls': $str = __('You are logged in! More controls will be available after setup.', 'wsko'); break;
                    case 'import_empty': $str = __('Nothing found.', 'wsko'); break;
                    case 'comp_w3tc_db': $str = __("W3 Total Cache's Database caching is active. Please copy the following lines and insert them into 'Ignored query stems' in the %%w3tc_link%%.", 'wsko'); break;
                    case 'comp_w3tc_db_sub': $str = __("Ignoring this message can lead to errors within our plugin. You may also need to reset the plugin after adding all the tables.", 'wsko'); break;
                    case 'comp_w3tc_obj': $str = __("W3 Total Cache's Object caching for WP-Admin is active. Please deactivate 'caching for wp-admin requests' in the %%w3tc_link%%.", 'wsko'); break;
                    case 'comp_w3tc_obj_sub': $str = __("Ignoring this message can lead to errors within our plugin.", 'wsko'); break;

                    case 'e_set_time_limit': $str = __('The critical function "set_time_limit" has been disabled on your server. This can lead to not finished scripts, corrupt data and thus endless loading.', 'wsko'); break;
                    case 'e_set_time_limit_sub': $str = __("This usually happens on shared hosting providers due to their systems structure. Please contact your website administrator.", 'wsko'); break;
                    case 'e_license_timeout': $str = __('Your license has timed out. <a href="%%account_link%%">View Account</a>', 'wsko'); break;
                    case 'e_license_user': $str = __('You are using a license that is registered for another user. This can lead to authentication problems.', 'wsko'); break;
                    case 'e_license_user_verify': $str = __('Your Email has not been verified yet. <a href="%%account_link%%">View Account</a>', 'wsko'); break;
                    case 'e_license_prem': $str = __('You are using the Free version with a Premium license. Please download the Premium version to access the full functionality of BAVOKO SEO Tools. <a href="%%account_link%%">View Account</a>', 'wsko'); break;
                    case 'e_license_prem_sub': $str = __('A mail with further information was sent to %%mail%%', 'wsko'); break;
                    case 'e_license_plan': $str = __('Your license is missing a valid premium plan. Try resyncing the license in your <a href="%%account_link%%">Account</a> panel.', 'wsko'); break;

                    case 'ce_php': $str = __("You are running PHP %%php%%, which is not compatible with BAVOKO SEO Tools. We recommend you to update your PHP version to 7.x for a faster and more secure WordPress site!", 'wsko'); break;
                    case 'ce_php_sub': $str = __('There are still WordPress plugins not working with PHP 7.x. If you can’t renounce them, you should at least update to PHP 5.6.', 'wsko'); break;
                    case 'ce_wp': $str = __("You are running WP %%wp%% which is not compatible with BAVOKO SEO Tools. We strongly recommend you to update your WordPress!", 'wsko'); break;
                    case 'ce_dir': $str = __("Your Content Directory (%%dir%%) is not writable! Please create a folder named 'bst' inside your Content Directory or give writing permissions to WordPress. ", 'wsko'); break;
                    case 'ce_dir_sub': $str = __("This folder is used for backups and cache files and is part of our core functionality.", 'wsko'); break;
                    case 'ce_dir_access': $str = __("BST can't access the folder 'bst' in your content directory (%%dir%%)! You will need to give write and read permission to WordPress on this folder.", 'wsko'); break;
                    case 'ce_dir_access_sub': $str = __('This folder is used for backups and cache files and is part of our core functionality.', 'wsko'); break;
                    case 'ce_main': $str = __("Handle the error(s) above to gain access to the plugin", 'wsko'); break;
                    
                }
                break;

            case 'search':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_search':
                switch ($id)
                {
                    case 'first_report': $str = __('Your first report is being fetched, please be patient.', 'wsko'); break;
                    case 'first_report_sub': $str = __('Working on package %%curr%% out of %%all%% packages.', 'wsko'); break;
                    case 'updating': $str = __('Your Search Data is being updated. Please try again in a few minutes.', 'wsko'); break;
                    case 'incomplete_cache': $str = __('There are %%missing%% missing data sets in your last %%days%% days cache. You can wait for the next cronjob (%%cron%%) or initiate it manually (this will take some time and may block your session for the time)', 'wsko'); break;
                    case 'incomplete_cache_sub': $str = __('Please update your data by clicking on \'Update Manually\'. A dataset older than %%days%% days can\'t be updated anymore.', 'wsko'); break;
                    case 'no_owner_access': $str = __('Your account has access to the domain, but it is not set as the owner. Please use your owner account, or change the required permissions.', 'wsko'); break;
                    case 'no_site_access': $str = __('We can\'t find the property %%domain%%. Please choose another Google account!', 'wsko'); break;
                    case 'invalid_token': $str = __('Your Access Token is invalid or has expired (you can only submit it once).', 'wsko'); break;
                    case 'credentials_help': $str = __('Your credentials are invalid. Check the following steps and contact us if the error persists.', 'wsko'); break;
                    case 'credentials_help_list': $str = array(
                            __('Are you checking with the right account? ', 'wsko').WSKO_Class_Template::render_infoTooltip(__("Depending on how many accounts you have yourself, or how often another person is using this browser, you may have the wrong account set right now. Allways check, that the current logged in Google Account is also the property owner.", 'wsko'), 'info', true),
                            __('Is the Google account the verified owner of your website? See Googles <a href="%%howto_link%%" target="_blank">How To Guide</a> and after that check your <a href="%%console_link%%">Search Console</a> if the property "%%home_url%%" is listed and verified.', 'wsko'),
                            __('Has the verfication run out? ', 'wsko').WSKO_Class_Template::render_infoTooltip(__("Just having the page under Properties in your Search Console is not enough. If the page is marked with 'unverified', then you need to redo the verification process of this property. The message displayed should tell you if something is wrong with your property status.", 'wsko'), 'info', true),
                            __('Did you set the property for both <b>http</b>://%%host%% <b>and https</b>://%%host%%? ', 'wsko').WSKO_Class_Template::render_infoTooltip(__("There is a difference between http and https. Please check if you have both sites verified according to the steps above.", 'wsko'), 'info', true),
                            __('Are you able to access the required data? Try to view the <a href="%%report_link%%">Search Analysis</a> ', 'wsko').WSKO_Class_Template::render_infoTooltip(__("Google features a permission system for external accounts managing your sites. Thus the account you are trying to login with may lack the required permissions to access the required data. Click the link below and click on the 'Open Search Analytics Report' button in the new page. You should be able to open this view for your site.", 'wsko'), 'info', true),
                        ); break;
                    case 'api_error': $str = __('Search API not connected. See Settings for more information. ', 'wsko'); break;
                    case 'pending': $str = __('Your Search Data is pending.', 'wsko'); break;
                    case 'cronjob_inactive': $str = __('The Cronjob for your Search Data is not running. ', 'wsko'); break;
                    case 'cronjob_timeout': $str = __('The Cache update was unable to complete due to too many access check fails. Please check your API configuration. ', 'wsko'); break;
                    case 'monitoring_empty': $str = __('No Keywords selected', 'wsko'); break;
                    case 'kw_report_no_search': $str = __("Search API is not connected. Setup the API in your Settings to get your site's performance for this keyword.", 'wsko'); break;
                    case 'kw_report_no_search_data': $str = __("Your Site is not ranking for this keyword.", 'wsko'); break;
                    case 'kw_report_demo': $str = __('Please note: In the demo mode, the keyword research tool is limited to the following 4 keywords:', 'wsko'); break;
                    case 'invalid_cred': $str = __('Your credentials are invalid or you have insufficient permissions.', 'wsko'); break;
                    case 'auth_url_fail': $str = __('Authentication URL could not be generated.', 'wsko'); break;
                    
                }
                break;

            case 'onpage':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_onpage':
                switch ($id)
                {
                    case 'updating': $str = __('Your Onpage Analysis is being updated. Please try again in a few minutes.', 'wsko'); break;
                    case 'prem_updating': $str = __('Your Onpage Analysis (Premium) is being updated. Please try again in a few minutes.', 'wsko'); break;
                    case 'updating_sub': $str = __('Working on package %%curr%% out of %%count%% packages', 'wsko'); break;
                    case 'prem_requested': $str = __('Your Onpage Analysis (Premium) is being generated. This can take a few hours.', 'wsko'); break;
                    case 'pending': $str = __('Your Report is pending.', 'wsko'); break;
                    case 'cronjob_inactive': $str = __('The Cronjob for your Page Analysis is not running. ', 'wsko'); break;
                    case 'cronjob_prem_inactive': $str = __('The Cronjob for your Premium Analysis is not running. ', 'wsko'); break;
                    case 'preview_titles': $str = __('Meta Titles in this report are provided by %%source%%', 'wsko'); break;
                    case 'preview_desc': $str = __('Meta Descriptions in this report are provided by %%source%%', 'wsko'); break;
                    case 'overview_prem_running': $str = __('Your Premium Onpage Crawl is not completed yet. Some report sections may be inaccessible.', 'wsko'); break;
                    case 'onpage_prem_crit_error': $str = __('Your Onpage Analysis (Premium) could not be generated by our crawler because of the following error: "%%error%%"', 'wsko'); break;
                    case 'onpage_prem_crit_error_sub': $str = __('This usually happens due to timeout errors when a huge amount of pages is crawled or when the query limit is to low to finish the crawl within 24 hours. Increase the query limit or whitelist our crawler IP ('.WSKO_CRAWLER_IP.').', 'wsko'); break;
                    case 'onpage_prem_crit_queries': $str = __('Your Onpage Analysis (Premium) has over 100 pages with a HTTP code of 0. This could be the result of blocked queries from your server.', 'wsko'); break;
                    case 'onpage_prem_crit_queries_sub': $str = __('Increase the query limit or whitelist our crawler IP ('.WSKO_CRAWLER_IP.').', 'wsko'); break;
                    case 'seo_plugins_active': $str = __('Please Note: Our plugin has discovered another active SEO plugin. This section has been disabled to prevent errors from using the same functionality multiple times. Active SEO plugin(s):', 'wsko'); break;
                    case 'seo_plugins_active_sub': $str = __('You can continue to use other SEO plugins, only this section is disabled. Deactivate the plugins listed above to get full access.', 'wsko'); break;
                    
                }
                break;

            case 'backlinks':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_backlinks':
                switch ($id)
                {
                    case 'first_run': $str = __('Your initial backlink list is being fetched. This can take a few hours.', 'wsko'); break;
                    case 'updating': $str = __('Your Backlink Analysis is being updated. Please try again in a few minutes.', 'wsko'); break;
                    case 'prem_updating': $str = __('Your Backlink Analysis (Premium) is being updated. Please try again in a few minutes.', 'wsko'); break;
                    case 'pending': $str = __('Your Backlink Analysis is pending.', 'wsko'); break;
                    case 'cronjob_inactive': $str = __('The Cronjob for your Backlink Data is not running. ', 'wsko'); break;
                    case 'api_error': $str = __('Analytics API not connected. See Settings for more information. ', 'wsko'); break;
                    case 'no_sessions': $str = __('You have no sessions (e.g. backlinks) in your selected analytics profile.', 'wsko'); break;
                    case 'invalid_cred': $str = __('Your credentials are invalid or you have insufficient permissions.', 'wsko'); break;
                    case 'auth_url_fail': $str = __('Authentication URL could not be generated.', 'wsko'); break;
                }
                break;

            case 'performance':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_performance':
                switch ($id)
                {
                    case 'analytics_pending': $str = __('Your Analytics Data is being fetched', 'wsko'); break;
                    case 'api_error': $str = __('Analytics API not connected. See Settings for more information. ', 'wsko'); break;
                    case 'no_sessions': $str = __('You have no session with speed information in your selected analytics profile.', 'wsko'); break;
                    case 'analytics_empty': $str = __('Your Analytics profile doesn\'t have any performance data', 'wsko'); break;
                }
                break;
                
            case 'tools':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_tools':
                switch ($id)
                {
                    case 'cronjob_sitemap_inactive': $str = __('The Cronjob for your Sitemap Generation is not running. ', 'wsko'); break;
                    case 'linking_onpage_running': $str = __('Your Premium crawl is being generated. This may take a while.', 'wsko'); break;
                    case 'linking_onpage_pending': $str = __('Your Premium crawl will be started soon.', 'wsko'); break;
                    case 'linking_no_data': $str = __('No link data found.', 'wsko'); break;
                    
                    case 'editors_no_admin': $str = __('Only Admins are able to access and modify the .htaccess and robots.txt files.', 'wsko');
                    case 'editors_main': $str = __('Please note: As far as .htaccess and robots.txt are sensitive files, we don\'t recommend changing them, unless you know what you do. Errors in these files can lead your site to break down.', 'wsko'); break;
                    case 'editors_main_list': $str = array(__('Every change will create a backup in <i>%%backup_dir%%</i>', 'wsko')); break;
                    case 'backup_unwritable': $str = __('Backup folder (%%backup_dir%%) is not writable. Changes won\'t create a backup file.', 'wsko'); break;
                    case 'htaccess_unreadable': $str = __('.htaccess is not readable and thus not editable.', 'wsko'); break;
                    case 'htaccess_unwritable': $str = __('.htaccess is not writable. Saving disabled.', 'wsko'); break;
                    case 'htaccess_uncreatable': $str = __('.htaccess cannot be created.', 'wsko'); break;
                    case 'robots_unreadable': $str = __('robots.txt is not readable and thus not editable.', 'wsko'); break;
                    case 'robots_unwritable': $str = __('robots.txt is not writable. Saving disabled.', 'wsko'); break;
                    case 'robots_uncreatable': $str = __('robots.txt cannot be created.', 'wsko'); break;

                    case 'sitemap_no_access': $str = __('You are missing the required permissions to create/edit the files "sitemap.xml" and "sitemap_bst.xml" in your home path.', 'wsko'); break;
                    case 'sitemap_no_admin': $str = __('Only Admins are able to edit the sitemap.', 'wsko'); break;

                    case 'widgets_demo': $str = __('Please note: Widgets are disabled in the Demo.', 'wsko'); break;
                    case 'widgets_breadcrumb_shortcode': $str = __('Use the shortcode <code>[bst_breadcrumbs]</code> or automatic insertion ("Activate Breadcrumbs") to display breadcrumbs. The Shortcode will use your custom settings unless you specifically set one of the following attributes.', 'wsko'); break;
                    case 'widgets_table_shortcode': $str = __('Use the shortcode <code>[bst_content_table]</code> or automatic insertion ("Activate \'Table of Contents\'") to display a table of contents in your posts. The Shortcode will use your custom settings unless you specifically set one of the following attributes.', 'wsko'); break;
                    case 'robots_uncreatable': $str = __('robots.txt cannot be created.', 'wsko'); break;
                }
                break;

            case 'settings':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_settings':
                switch ($id)
                {
                    case 'no_admin' : $str = __('Only Admins are able to edit the settings.', 'wsko'); break;
                    case 'co_h1': $str = __('If your theme uses post titles as h1-headings in specific post types, please configure them properly in the \'Onpage Settings\' below.', 'wsko'); break;
                    case 'htaccess_editor': $str = __('Attention: Please unlock only, if you are experienced with .htaccess files and know what you are doing. Even a single error in this file can make your entire website go down and can only be fixed with a SSH or FTP connection.', 'wsko'); break;
                    case 'robots_editor' : $str = __('Attention: Please unlock only, if you are experienced with robots.txt files and know what you are doing. Errors in this file can impact your sites rankings and can lead to exclusion of your entire site from the google index.', 'wsko'); break;
                    case 'permissions' : $str = __('Administrators have full access, additional roles can view statistics and make use of some tools. Settings are restricted to admins only.', 'wsko'); break;
                    case 'lightweight_cache' : $str = __('Attention: Using the lightweight cache will solve problems with the PHP Session, but will also reduce the plugins performance.', 'wsko'); break;
                    case 'backup_load_warning' : $str = __('BST is updating your data. Loading a backup now can result in unexpected behaviour. Please wait for the process to finish.', 'wsko'); break;
                    case 'backup_load_warning_sub' : $str = __('Notifications in Search, Onpage, Backlinks or Performance will tell you which section is currently loading', 'wsko'); break;
                    case 'cronjob_dm_inactive' : $str = __('The Cronjob for your daily maintenance is not running. ', 'wsko'); break;
                    case 'reporting_demo' : $str = __('Please note: Reporting is disabled in the Demo.', 'wsko'); break;
                    case 'reporting_deactivated' : $str = __('Reporting is deactivated', 'wsko'); break;
                    case 'reporting_deactivated' : $str = __('Reporting is deactivated', 'wsko'); break;
                    
                }
                break;

            case 'co':
                switch ($id)
                {
                    case '': $str = __('', 'wsko'); break;
                }
                break;
            case 'notif_co':
                switch ($id)
                {
                    case 'demo': $str = __('Please note: In the demo mode, the Content Optimizer only displays the home page.', 'wsko'); break;
                    case 'onpage_report_fail': $str = __('Onpage Report could not be generated!', 'wsko'); break;
                    case 'search_first_run': $str = __('Your search data is being updated. Keywords are available after the first report is finished.', 'wsko'); break;
                    case 'linking_no_data': $str = __('No linking data found.', 'wsko'); break;
                    case 'linking_no_kws': $str = __('Linking suggestions are based on your SEO Keywords but you haven\'t set any.', 'wsko'); break;
                    case 'linking_no_items': $str = __('No linking possibilities found.', 'wsko'); break;
                    case 'linking_update': $str = __('Your Onpage crawl (Premium) is not finished. ', 'wsko'); break;
                    case 'analytics_no_data': $str = __('No analytics data found.', 'wsko'); break;
                    case 'analytics_api_error': $str = __('Analytics API not connected. ', 'wsko'); break;
                    case 'craw_no_data': $str = __('No crawl data found.', 'wsko'); break;
                    case 'post_redirects': $str = __('This post has redirects from old links:', 'wsko'); break;
                    case 'post_redirects_empty': $str = __('No redirects found.', 'wsko'); break;
                    case 'post_redirects_data': $str = __('<b>%%count%%</b> redirect rule(s) are matching the URL <i>%%url%%</i>:', 'wsko'); break;
                    case 'post_redirects_data_sub': $str = __('Rules are sorted descending by priority', 'wsko'); break;
                    case 'post_redirects_data_empty': $str = __('No redirect rules found matching "%%url%%".', 'wsko'); break;
                    case 'status_code_error': $str = __('Status codes could not be fetched.', 'wsko'); break;
                    case 'post_lock': $str = __('Anonther user is currently editing this post. Post editing is disabled. ', 'wsko'); break;
                    
                }
                break;

            case 'prem':
                switch ($id)
                {
                    case 'kw_limit': $str = __('<p class="text-off">Only 2 Keywords are available in the Free Version. Upgrade Plan to add more Keywords & Synonyms. <div style="display:block;"><a href="https://www.bavoko.tools/pricing/" style="margin-top:10px;" target="_blank" class="button button-default">Learn More</a></div>', 'wsko'); break;
                }
                break;

            case 'setup':
                switch ($id)
                {
                    case 'onpage_report_running': $str = __('Your onpage analysis data will be available soon - we are working on your next website report.', 'wsko'); break;
                }
                break;

            case 'update':
                switch ($id)
                {
                    case 'un_onpage_meta_desc': $str = __('Google recently shortened the meta description length to approximately 160 characters. We recommend you to update your meta descriptions to get the best results.', 'wsko'); break;
                    case 'un_onpage_settings': $str = __('Please note: We have changed some options in the general settings. Please review your configuration. ', 'wsko'); break;
                    case 'un_core_update': $str = __('BST 2.1 has come with many core changes. To ensure a bug free experience we recommend reseting your environment. ', 'wsko'); break;
                    case 'un_core_update_sub': $str = __('You will need to reconfigure the plugin afterwards.', 'wsko'); break;
                    case 'un_search_api': $str = __('Important: The Google Search Console API has been changed. Please reconnect your API access. ', 'wsko'); break;
                    
                }
                break;
                
        }
        $str = $str ? $str : ($default ? $default : $str);
        if ($str)
        {
            foreach ($params as $k => $v)
            {
                $str = str_replace('%%'.$k.'%%', ($v||$v==='0'||$v===0)?$v.'':'', $str);
            }
        }
        return $str;
    }
}

function wsko_loc($section, $id, $params = array(), $default = false)
{
    return WSKO_Class_Localization::translate($section, $id, $params, $default);
}
?>