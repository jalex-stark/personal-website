<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$global_analysis_data = WSKO_Class_Onpage::get_onpage_analysis();

if ( isset( $global_analysis_data['current_report'] ) ) {
    $global_analysis = $global_analysis_data['current_report'];
    /*$crawl_interval = intval(WSKO_Class_Core::get_setting('onpage_analysis_interval'));
      if ($crawl_interval <= 0)
          $crawl_interval = 1;
      else if ($crawl_interval > 30)
          $crawl_interval = 30;*/
    $crawl_interval = WSKO_ONPAGE_FETCH_INTERVAL;
    $next_cronjob = wp_next_scheduled( 'wsko_onpage_analysis' );
    $crawl_timeout = $global_analysis['started'] + 60 * 60 * 24 * $crawl_interval;
    ?><div class="crawling-info-inner">
        <?php 
    ?>
                <!-- <h4>Onpage Crawl</h4> -->
                <p class="font-unimportant"><b><?php 
    echo  __( 'Last Crawl:', 'wsko' ) ;
    ?></b> <?php 
    echo  ( $global_analysis ? date( 'd.m.Y H:i', $global_analysis['started'] ) : '-' ) ;
    ?></p>
                <p class="font-unimportant"><b><?php 
    echo  __( 'Next Crawl:', 'wsko' ) ;
    ?></b> <?php 
    echo  date( 'd.m.Y H:i', ( $crawl_timeout && $crawl_timeout < time() ? $next_cronjob : $crawl_timeout ) ) ;
    ?></p>		
                <p class="font-unimportant"><b><?php 
    echo  __( 'Crawled Pages:', 'wsko' ) ;
    ?></b> <?php 
    echo  ( $global_analysis ? $global_analysis['total_pages'] : '-' ) ;
    ?></p>
                
                <p style="line-height: 15px; margin-top: 5px;"><small class="text-off"><?php 
    echo  sprintf( __( 'Please note: Changes in your content will not be visible in the current report until the next crawl on %s', 'wsko' ), date( 'd.m.Y', ( $crawl_timeout < time() ? $next_cronjob : $crawl_timeout ) ) ) ;
    ?></small></p>
                <!--a class="btn btn-flat btn-sm" href="#">Regenerate Report</a-->  
        <?php 
    ?>
    </div><?php 
} else {
    _e( 'No crawl info to show', 'wsko' );
}
