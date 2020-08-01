<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$post_id = ( isset( $template_args['post_id'] ) ? $template_args['post_id'] : false );
$priority_keywords = ( isset( $template_args['priority_keywords'] ) ? $template_args['priority_keywords'] : false );
$keyword_data = ( isset( $template_args['keyword_data'] ) ? $template_args['keyword_data'] : false );
$op_report = ( isset( $template_args['op_report'] ) ? $template_args['op_report'] : false );
$ext_keywords = array();

if ( !WSKO_Class_Onpage::seo_plugins_disabled() ) {
    $source = WSKO_Class_Compatibility::get_seo_plugin_preview( 'metas' );
    if ( $source ) {
        $ext_keywords = WSKO_Class_Compatibility::get_keywords_ext( $post_id, $source );
    }
}

$has_prio1 = false;
$count_pr1 = 0;
$has_prio2 = false;
$count_pr2 = 0;
if ( $priority_keywords ) {
    foreach ( $priority_keywords as $pk => $data ) {
        if ( isset( $data['prio'] ) ) {
            
            if ( $data['prio'] == 1 ) {
                $count_pr1++;
                $has_prio1 = true;
            } else {
                
                if ( $data['prio'] == 2 ) {
                    $count_pr2++;
                    $has_prio2 = true;
                }
            
            }
        
        }
    }
}
?>
<div class="wsko-widget-sidebar-keywords">
	<div class="wsko-co-priority-keywords-container" data-post="<?php 
echo  $post_id ;
?>" data-nonce="<?php 
echo  wp_create_nonce( 'wsko_co_add_priority_keyword' ) ;
?>" data-nonce-similar="<?php 
echo  wp_create_nonce( 'wsko_co_add_similar_priority_keyword' ) ;
?>" data-nonce-sort="<?php 
echo  wp_create_nonce( 'wsko_co_sort_priority_keyword' ) ;
?>">
		<div class="wsko-priority-keyword prio-1">
			<p class="wsko-priority-keyword-label main"><?php 
echo  __( 'SEO Keywords', 'wsko' ) ;

if ( !WSKO_Class_Core::is_premium() ) {
    ?> <span>(<span class="wsko-prio-kw-count-1"><?php 
    echo  $count_pr1 ;
    ?></span>/2)</span><?php 
}

WSKO_Class_Template::render_infoTooltip( __( 'Add keywords that you want to optimize/rank for. Drag & Drop keywords to change priority or sorting order.', 'wsko' ), 'info' );
/*?><span class="wsko-text-off wsko-small"><i class="fa fa-exclamation-circle"></i> </span><?php*/
?></p>
			<div class="wsko-co-keyword-suggest-wrapper">
				<input class="wsko-co-keyword-input wsko-form-control wsko-mb10" placeholder="<?php 
echo  __( 'Add Keyword (press Enter)', 'wsko' ) ;
?>" data-nonce="<?php 
echo  wp_create_nonce( 'wsko_co_get_keyword_suggests' ) ;
?>">
				<a href="#" style="line-height: 14px;" class="wsko-co-add-priority-keyword btn btn-flat"><i class="fa fa-plus fa-fw dark wsko-text-off"></i></a>
				<div class="keyword-suggest-inner-wrapper">
					<ul class="wsko-co-keyword-suggests" style="display:none">
					</ul>
					<div class="wsko-co-keyword-suggests-loader" style="display:none">
						<?php 
echo  __( 'Loading suggestions...', 'wsko' ) ;
?>
					</div>
				</div>
				<!--select style="<?php 
echo  ( !WSKO_Class_Core::is_premium() ? 'display:none;' : '' ) ;
?>" class="wsko-co-keyword-prio">
					<option value="1" selected>Prio 1</option>
					<option value="2">Prio 2</option>
				</select-->
			</div>
			<div>
				<?php 
?>
				<ul class="wsko-co-priority-keyword-group" data-prio="1" style="list-style-type:none;min-height:50px;">
					<?php 
if ( $priority_keywords ) {
    foreach ( $priority_keywords as $pk => $data ) {
        
        if ( isset( $data['prio'] ) && $data['prio'] == 1 ) {
            if ( $ext_keywords ) {
                unset( $ext_keywords[$pk] );
            }
            WSKO_Class_Template::render_priority_keyword_item(
                $post_id,
                $pk,
                $data,
                $keyword_data,
                $op_report
            );
        }
    
    }
}
?><span class="wsko-text-off wsko-small wsko-co-keyword-group-no-items" style="<?php 
echo  ( $has_prio1 ? 'display:none;' : '' ) ;
?>opacity:1"><span style="color:#d9534f;"><?php 
WSKO_Class_Template::render_infoTooltip( __( 'You have no Prio 1 keywords! A successful optimization and many helpful features depend on these keywords.', 'wsko' ), 'warning' );
?></span> <span style="opacity:.7;"><?php 
echo  __( 'No Keywords added!', 'wsko' ) ;
?></span></span>
				</ul>
				<?php 

if ( $ext_keywords ) {
    ?><div>
						<h2><?php 
    echo  sprintf( __( 'Importable Keywords from \'%s\'', 'wsko' ), $source ) ;
    ?></h2>
						<ul style="list-style-type:none;"><?php 
    foreach ( $ext_keywords as $kw ) {
        ?><li><a href="#" class="wsko-co-add-priority-keyword-inline dark" data-toggle="tooltip" data-title="Add to SEO Keywords" data-post="<?php 
        echo  $post_id ;
        ?>" data-keyword="<?php 
        echo  $kw ;
        ?>" data-prio="" data-nonce="<?php 
        echo  wp_create_nonce( 'wsko_co_add_priority_keyword' ) ;
        ?>"><i class="fa fa-plus fa-fw"></i> <?php 
        echo  $kw ;
        ?></a></li><?php 
    }
    ?></ul>
					</div><?php 
}

?>
			</div>	
		</div>	
		
		<?php 
/*
<div class="wsko-priority-keyword prio-2">
	<p class="badge badge-success">Prio 2</p>
	<ul class="wsko-co-priority-keyword-group" data-prio="2" style="list-style-type:none;min-height:50px;">
		<?php
		foreach($priority_keywords as $pk => $data)
		{
			if ($data['prio'] == 2)
				WSKO_Class_Template::render_priority_keyword_item($post_id, $pk, $data);
		}
		?>
	</ul>
</div>
<div class="wsko-priority-keyword prio-3">						
	<p class="badge badge-success">Prio 3</p>
	<ul class="wsko-co-priority-keyword-group" data-prio="3" style="list-style-type:none;min-height:50px;">
		<?php
		foreach($priority_keywords as $pk => $prio)
		{
			if ($data['prio'] == 3)
				WSKO_Class_Template::render_priority_keyword_item($post_id, $pk, $data);
		}
		?>
	</ul>
</div>	
*/
?>
	</div>
	<div style="margin-top:10px;" id="wsko_keyword_limit_pro_notice"><?php 
if ( !WSKO_Class_Core::is_premium() ) {
    WSKO_Class_Template::render_notification( 'success', array(
        'msg' => wsko_loc( 'prem', 'kw_limit', array(
        'pricing_link' => WSKO_Controller_Pricing::get_link(),
    ) ),
    ) );
}
?></div>
</div>