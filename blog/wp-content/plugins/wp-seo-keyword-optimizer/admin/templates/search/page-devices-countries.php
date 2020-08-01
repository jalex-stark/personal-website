<?php
if (!defined('ABSPATH')) exit;
?>
<ul class="nav nav-tabs bsu-tabs border-dark">
    <li class="waves-effect active"><a data-toggle="tab" href="#tab_countries"><?=__( 'Countries', 'wsko' ) ?></a></li>
    <li class="waves-effect"><a data-toggle="tab" href="#tab_devices"><?=__( 'Devices', 'wsko' ) ?></a></li>
</ul>

<div class="tab-content">
    <div id="tab_countries" class="tab-pane fade in active">
        <div class="row wsko-search-query-wrapper" style="position:relative;">
            <div class="col-sm-12 col-xs-12">
                <div class="panel panel-default panel-body panel-transparent">
                    <?php WSKO_Class_Template::render_lazy_field('chart_countries', 'small'); ?>
                </div>
            </div>  
            <div class="wsko-search-query-default">
                <?php
                //WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'World Map', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'chart_countries'));
                WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Countries', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_countries'));
                ?>
            </div> 
            <div class="col-sm-12 col-xs-12">            
                <div class="bsu-panel panel panel-default wsko-search-query-overlay" style="display:none;">
                    <a class="wsko-search-query-overlay-back btn btn-flat btn-sm panel-link pull-right" href="#"><i class="fa fa-angle-left fa-fw"></i> <?=__('Back to Overview', 'wsko')?></a>
                    <div class="wsko-search-query-overlay-content">
                    </div>
                </div>
            </div>    
        </div>
    </div>
    <div id="tab_devices" class="tab-pane fade wsko-tab-devices">
        <div class="row wsko-search-query-wrapper" style="position:relative;">

            <?php   
                WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Devices Click Distribution', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'class' => '', 'lazyVar' => 'chart_history_devices'));
            ?>    


            <div class="wsko-search-query-default">
            <?php            
            WSKO_Class_Template::render_panel(array('type' => 'lazy', 'title' => __( 'Devices', 'wsko' ), 'col' => 'col-sm-12 col-xs-12', 'lazyVar' => 'table_devices'));
            ?>
            </div>
            <div class="col-sm-12 col-xs-12">
                <div class="bsu-panel panel panel-default wsko-search-query-overlay" style="display:none;">
                    <a class="wsko-search-query-overlay-back btn btn-flat btn-sm panel-link pull-right" href="#"><i class="fa fa-angle-left fa-fw"></i> <?=__('Back to Overview', 'wsko')?></a>
                    <div class="wsko-search-query-overlay-content">
                    </div>
                </div>
            </div> 
        </div>
    </div>
</div>