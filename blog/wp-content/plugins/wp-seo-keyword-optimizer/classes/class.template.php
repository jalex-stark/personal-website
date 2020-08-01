<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Template
{
    public static function render_table_ajax_field(
        $ajax_func,
        $data,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $group = ( isset( $params['group'] ) ? $params['group'] : false );
        
        if ( $ajax_func ) {
            $ajax_func = 'wsko_' . $ajax_func;
            ?><div class="wsko-table-lazy-ajax-field" data-ajax="<?php 
            echo  $ajax_func ;
            ?>" data-ajax-data="<?php 
            echo  ( $data ? $data : '' ) ;
            ?>" <?php 
            echo  ( $group ? 'data-group="' . $group . '"' : '' ) ;
            ?> data-nonce="<?php 
            echo  wp_create_nonce( $ajax_func ) ;
            ?>">
				<?php 
            WSKO_Class_Template::render_preloader( array(
                'size' => 'small',
            ) );
            ?>
			</div><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_array_dropdown( $data, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $title = ( isset( $params['title'] ) ? $params['title'] : __( 'More', 'wsko' ) );
        $key = ( isset( $params['key'] ) ? $params['key'] : false );
        $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_array_dropdown_' );
        ?><div class="panel-group">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" href="#<?php 
        echo  $uniqid ;
        ?>"><?php 
        echo  $title ;
        ?></a>
					</h4>
				</div>
				<div id="<?php 
        echo  $uniqid ;
        ?>" class="panel-collapse collapse">
					<div class="panel-body">
					<ul><?php 
        if ( $data ) {
            foreach ( $data as $d ) {
                $d = (array) $d;
                ?><li><?php 
                
                if ( $key ) {
                    echo  $d[$key] ;
                } else {
                    echo  $d ;
                }
                
                ?></li><?php 
            }
        }
        ?></ul>
					</div>
				</div>
			</div>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_node_grid(
        $type,
        $data,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $height = ( isset( $params['height'] ) ? $params['height'] : '300px' );
        $node_click = ( isset( $params['node_click'] ) ? $params['node_click'] : false );
        $node_color = ( isset( $params['node_color'] ) ? $params['node_color'] : '#8eb9ff' );
        $edge_color = ( isset( $params['edge_color'] ) ? $params['edge_color'] : '#e6e6e6' );
        $node_highlight_color = ( isset( $params['node_highlight_color'] ) ? $params['node_highlight_color'] : '#bbb' );
        $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_node_grid_' );
        if ( isset( $params['out_id'] ) ) {
            $params['out_id'] = $uniqid;
        }
        ?><div style="position:relative;width:100%;height:<?php 
        echo  $height ;
        ?>"><div id="<?php 
        echo  $uniqid ;
        ?>" style="position: absolute;top: 0;bottom: 0;left: 0;right: 0;"></div>
		<div id="<?php 
        echo  $uniqid ;
        ?>_loading" style="display:none;position: absolute;top: 0;bottom: 0;left: 0;right: 0;background-color:white;"><?php 
        WSKO_Class_Template::render_preloader( array(
            'size' => 'big',
        ) );
        ?></div></div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			<?php 
        
        if ( $type === 'sigma' ) {
            ?>
			var data = JSON.parse('<?php 
            echo  json_encode( $data ) ;
            ?>');
			sigma.classes.graph.addMethod('hoverNodeEdges', function(e) {
				var id = e.data.node.id;
				var inNeighbors = this.allNeighborsIndex[id];
				for (var i in inNeighbors) {
					for (var j in inNeighbors[i]) {
						inNeighbors[i][j].size = 1;
						inNeighbors[i][j].color = '<?php 
            echo  $node_highlight_color ;
            ?>';
					}
				}
			});
			sigma.classes.graph.addMethod('dehoverNodeEdges', function(e) {
				var id = e.data.node.id;
				var inNeighbors = this.allNeighborsIndex[id];
				for (var i in inNeighbors) {
					for (var j in inNeighbors[i]) {
						inNeighbors[i][j].size = null;
						inNeighbors[i][j].color = null;
					}
				}
			});
			s = new sigma({
				graph: data,
				renderers: [{
					container: '<?php 
            echo  $uniqid ;
            ?>',
					type: 'canvas' // sigma.renderers.canvas works as well
				}],
				settings: {
					defaultNodeColor: '<?php 
            echo  $node_color ;
            ?>',
					defaultEdgeColor: '<?php 
            echo  $edge_color ;
            ?>',
					defaultEdgeType:'line',
					enableHovering:true,
					edgeColor: "default",
					font:"sans-serif",
					drawEdges: false
				}
			});
			var mouseDown = false;
			$(document).on('mousedown', function() {
				mouseDown = true;
			}).on('mouseup', function() {
				mouseDown = false;
			});
			s.bind('overNode', function (e) {
				if (!mouseDown)
				{
					s.graph.hoverNodeEdges(e, true);
					s.settings('drawEdges', true);
					s.refresh();
				}
			});
			s.bind('outNode', function (e) {
				s.graph.dehoverNodeEdges(e, false);
				s.settings('drawEdges', false);
				s.refresh();
			});
			<?php 
            
            if ( $node_click ) {
                ?>
			s.bind('clickNode', function (e) {
				<?php 
                switch ( $node_click['type'] ) {
                    case 'open_co_url':
                        ?>window.wsko_open_optimizer_modal(e.data.node['<?php 
                        echo  $node_click['key'] ;
                        ?>'], 'post_url', 'overview');<?php 
                        break;
                }
                ?>
			});
			<?php 
            }
            
            ?>
			var $par_tab = $('#<?php 
            echo  $uniqid ;
            ?>').closest('.tab-pane'),
			level = 0;
			while ($par_tab && $par_tab.length)
			{
				level++;
				$('a[data-toggle="tab"][href="#'+$par_tab.attr('id')+'"]').on('shown.bs.tab', function (e) {
					s.refresh();
				});
				var $par_tab = $par_tab.parent().closest('.tab-pane');
				if (level > 10) //deadlock fallback
					break;
			}

			$('.wsko-sigma-filter:not(.wsko-init)').addClass('wsko-init').change(function(event){
				var $this = $(this);
				if ($this.data('sigma') === '<?php 
            echo  $uniqid ;
            ?>')
				{
					var key = $this.data('key'),
					val = $this.data('val'),
					comp = $this.data('comp'),
					checked = $this.attr('checked');
					s.graph.nodes().forEach(function(n) {
						var match = false;
						switch (comp)
						{
							case 'gt':
							if (n[key] > val) 
								match = true;
							break;
							case 'ge':
							if (n[key] >= val) 
								match = true;
							break;
							case 'eq':
							default:
							if (n[key] == val) 
								match = true;
							break;

						}
						if (match) {
							n.hidden = !checked;
						} 
					}); 
					s.refresh();
					//doForceAtlas2();
				}
			});
			
			var atlas2timeout = false;
			//do layout calc for 5 secs
			function doForceAtlas2()
			{
				//$('#<?php 
            echo  $uniqid ;
            ?>_loading').show();
				s.startForceAtlas2({
					gravity: 10,
					outboundAttractionDistribution: true
				});
				if (atlas2timeout)
					clearTimeout(atlas2timeout);
				atlas2timeout = setTimeout(function() {
					s.killForceAtlas2();
					$('#<?php 
            echo  $uniqid ;
            ?>_loading').fadeOut();
					s.settings('drawEdges', true);
					s.refresh();
				}, 5000);
			};
			doForceAtlas2();
		<?php 
        } else {
            ?>
			 var data = JSON.parse('<?php 
            echo  json_encode( $data ) ;
            ?>');
			 
			 <?php 
        }
        
        ?>
		});
		</script><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_status_iconbar( $data, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $class = ( isset( $params['class'] ) ? $params['class'] : false );
        $active = true;
        ?><div class="<?php 
        echo  $class ;
        ?>"><?php 
        foreach ( $data as $d ) {
            ?>
			<?php 
            /* <i class="fa fa-circle" style="margin-right: 3px; color:<?=$d['condition'] ? '#5cb85c' : (isset($d['warning']) && $d['warning'] ? '#f0ad4e' : '#d9534f')?>;" data-toggle="tooltip" data-title="<?=$d['condition'] ? $d['text_t'] : $d['text_f'] ?>"></i><?php */
            if ( !$d['condition'] ) {
                $active = false;
            }
        }
        WSKO_Class_Template::render_badge( $active );
        ?>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_post_dirty_icon( $post, $params = array(), $return = true )
    {
        if ( $return ) {
            ob_start();
        }
        $dirty = WSKO_Class_Onpage::get_op_post_dirty( $post );
        
        if ( $dirty ) {
            ?><span class="wsko-op-post-dirty-icon" data-tooltip="<?php 
            echo  __( 'The post has been modified since the last Onpage Crawl. Content Score and table values may differ.', 'wsko' ) ;
            ?>"><i class="fa fa-exclamation-circle"></i></span><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_ajax_beacon( $action, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $post = ( isset( $params['post'] ) ? $params['post'] : array() );
        $plain_js = ( isset( $params['plain_js'] ) ? $params['plain_js'] : false );
        $size = ( isset( $params['size'] ) ? $params['size'] : 'small' );
        if ( $post ) {
            foreach ( $post as $k => $v ) {
                $post[$k] = $k . ':"' . $v . '"';
            }
        }
        $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_ajax_beacon_' );
        ?><div id="<?php 
        echo  $uniqid ;
        ?>"><?php 
        WSKO_Class_Template::render_preloader( array(
            'size'     => $size,
            'plain_js' => $plain_js,
        ) );
        ?></div>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			<?php 
        
        if ( $plain_js ) {
            ?>
			$.ajax({
				url: '<?php 
            echo  admin_url( 'admin-ajax.php' ) ;
            ?>',
				type: 'post',
				data: {action: '<?php 
            echo  $action ;
            ?>',<?php 
            echo  ( $post ? implode( ',', $post ) . ',' : '' ) ;
            ?> nonce: '<?php 
            echo  wp_create_nonce( $action ) ;
            ?>'},
				async: true,
				success: function(res)
				{
					if (res.success)
					{
						$('#<?php 
            echo  $uniqid ;
            ?>').html(res.view);
						if (window.wsko_init_core)
							window.wsko_init_core();
					}
				}
			});
			<?php 
        } else {
            ?>
			window.wsko_post_element({action: '<?php 
            echo  $action ;
            ?>',<?php 
            echo  ( $post ? implode( ',', $post ) . ',' : '' ) ;
            ?> nonce: '<?php 
            echo  wp_create_nonce( $action ) ;
            ?>'},
				function(res){
					if (res.success)
					{
						$('#<?php 
            echo  $uniqid ;
            ?>').html(res.view);
					}
					return true;
				}, function() {
				}, false, false);
			<?php 
        }
        
        ?>
		});
		</script>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_keyword_field( $keyword, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $monitored_keywords = WSKO_Class_Search::get_monitored_keywords();
        $is_monitored = in_array( $keyword, $monitored_keywords );
        ?>
		<?php 
        /*
        <div class="dropdown" style="display:inline-block;">
        	<a class="dropdown-toggle dark" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        		<i class="fa fa-ellipsis-v fa-fw"></i>
        	</a>
        	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        		<a class="dropdown-item" href="#"><?php WSKO_Class_Template::render_page_link(WSKO_Controller_Search::get_instance(), 'keyword_research', 'Search similar keywords', array('post' => array('keyword' => $keyword)));?> </a>
        	</div>
        </div>
        */
        ?>
		<?php 
        
        if ( WSKO_Class_Core::is_demo() ) {
            echo  substr( $keyword, 0, 3 ) . '...' ;
        } else {
            echo  $keyword ;
        }
        
        ?> <a href="#" class="wsko-set-monitoring-keyword ml3 mr3" data-toggle="tooltip" <?php 
        echo  ( $is_monitored ? 'title' : 'data-original-title' ) ;
        ?>="<?php 
        echo  __( "Remove from 'Monitored Keywords'", 'wsko' ) ;
        ?>" <?php 
        echo  ( !$is_monitored ? 'title' : 'data-original-title' ) ;
        ?>="<?php 
        echo  __( "Add to 'Monitored Keywords'", 'wsko' ) ;
        ?>" data-keyword="<?php 
        echo  $keyword ;
        ?>" data-set="<?php 
        echo  ( $is_monitored ? 'false' : 'true' ) ;
        ?>"><i class="fa fa-star<?php 
        echo  ( $is_monitored ? '' : '-o' ) ;
        ?>"></i></a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_backlink_field( $backlink, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $is_domain = ( isset( $params['is_domain'] ) ? $params['is_domain'] : false );
        $reload_table = ( isset( $params['reload_table'] ) ? $params['reload_table'] : false );
        $bc = parse_url( 'http://' . $backlink );
        $backlink_domain = ( isset( $bc['host'] ) ? $bc['host'] : '' );
        $disavowed_backlinks = WSKO_Class_Search::get_disavowed_backlinks();
        $is_disavowed = isset( $disavowed_backlinks['urls'][$backlink] );
        $is_disavowed_domain = isset( $disavowed_backlinks['domains'][$backlink_domain] );
        
        if ( WSKO_Class_Core::is_demo() ) {
            $backlink = 'example.com/backlink/';
            $backlink_domain = 'example.com';
        }
        
        
        if ( $is_domain ) {
            
            if ( $backlink_domain ) {
                ?>
				<a href="#" class="wsko-set-backlink-disavow ml3" <?php 
                echo  ( $reload_table ? 'data-reload-table="true"' : '' ) ;
                ?> data-toggle="tooltip" <?php 
                echo  ( $is_disavowed_domain ? 'title' : 'data-original-title' ) ;
                ?>="<?php 
                echo  __( "Remove Domain from 'Disavowed Backlinks'", 'wsko' ) ;
                ?>" <?php 
                echo  ( !$is_disavowed_domain ? 'title' : 'data-original-title' ) ;
                ?>="<?php 
                echo  __( "Add Domain to 'Disavowed Backlinks'", 'wsko' ) ;
                ?>" data-backlink="<?php 
                echo  $backlink_domain ;
                ?>" data-set="<?php 
                echo  ( $is_disavowed_domain ? 'false' : 'true' ) ;
                ?>" data-domain="true"><i class="fa fa-<?php 
                echo  ( $is_disavowed_domain ? 'undo' : 'trash' ) ;
                ?>"></i></a><?php 
            }
        
        } else {
            ?>
			<a href="#" class="wsko-set-backlink-disavow ml3" <?php 
            echo  ( $reload_table ? 'data-reload-table="true"' : '' ) ;
            ?> data-toggle="tooltip" <?php 
            echo  ( $is_disavowed ? 'title' : 'data-original-title' ) ;
            ?>="<?php 
            echo  __( "Remove URL from 'Disavowed Backlinks'", 'wsko' ) ;
            ?>" <?php 
            echo  ( !$is_disavowed ? 'title' : 'data-original-title' ) ;
            ?>="<?php 
            echo  __( "Add URL to 'Disavowed Backlinks'", 'wsko' ) ;
            ?>" data-backlink="<?php 
            echo  $backlink ;
            ?>" data-set="<?php 
            echo  ( $is_disavowed ? 'false' : 'true' ) ;
            ?>"><i class="fa fa-<?php 
            echo  ( $is_disavowed ? 'undo' : 'trash' ) ;
            ?>"></i></a><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_asset_field(
        $url,
        $type,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        ?><div class="wsko-asset-img-replace-wrapper"><?php 
        
        if ( $type == 'img' ) {
            ?><img class="wsko-asset-img-preview" src="<?php 
            echo  $url ;
            ?>" style="width:120px"><?php 
        }
        
        ?>
			<p><?php 
        echo  basename( $url ) ;
        ?></p><a href="<?php 
        echo  $url ;
        ?>" target="_blank"><small class="text-off"><?php 
        echo  $url ;
        ?></small></a><br/><?php 
        
        if ( $type == 'img' ) {
            /*$attachment = attachment_url_to_postid($url);
            		if ($attachment)
            		{
            			WSKO_Class_Template::render_popup_link('Test', 'Attachment', '', array('ajax' => array('action' => 'get_attachment_popup', 'data' => array('attachment' => $attachment))));
            		}*/
            $path = WSKO_Class_Helper::get_asset_path( $url );
            
            if ( $path ) {
                ?><label><input style="display:none;" type="file" accepts="image/*" class="wsko-replace-asset" data-path="<?php 
                echo  $path ;
                ?>" data-nonce="<?php 
                echo  wp_create_nonce( 'wsko_replace_asset' ) ;
                ?>"><span data-tooltip="<?php 
                echo  __( 'Replace Image (SEO friendly - image information like url, alt tag or title tag of the old file will be applied)', 'wsko' ) ;
                ?>"><i class="fa fa-refresh"></i></span></label><?php 
            }
        
        }
        
        ?>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_content_optimizer_multi_link_ajax(
        $ajax_source,
        $arg,
        $title,
        $params = array(),
        $return = false
    )
    {
        $msg = ( isset( $params['msg'] ) ? $params['msg'] : false );
        if ( $return ) {
            ob_start();
        }
        
        if ( $ajax_source ) {
            $ajax_source = 'wsko_' . $ajax_source;
            ?><a href="#" class="wsko-content-optimizer-multi-ajax-link" data-action="<?php 
            echo  $ajax_source ;
            ?>" data-ajax-arg="<?php 
            echo  $arg ;
            ?>" data-nonce="<?php 
            echo  wp_create_nonce( $ajax_source ) ;
            ?>"><?php 
            echo  $title ;
            ?></a><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_content_optimizer_multi_link(
        $posts,
        $title,
        $params = array(),
        $return = false
    )
    {
        $msg = ( isset( $params['msg'] ) ? $params['msg'] : false );
        if ( $return ) {
            ob_start();
        }
        ?><a href="#" class="wsko-content-optimizer-multi-link" data-posts="<?php 
        echo  htmlspecialchars( json_encode( $posts ) ) ;
        ?>" data-title="<?php 
        echo  $msg ;
        ?>"><?php 
        echo  $title ;
        ?></a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_priority_keyword_item(
        $post_id,
        $keyword,
        $data,
        $keyword_data,
        $op_report,
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        if ( $keyword_data ) {
            foreach ( $keyword_data as $kw ) {
                
                if ( $kw->keyval == $keyword ) {
                    $data['clicks'] = $kw->clicks;
                    $data['pos'] = $kw->position;
                }
            
            }
        }
        if ( $op_report['issues']['keyword_density'] ) {
            
            if ( isset( $op_report['issues']['keyword_density'][$keyword] ) ) {
                $data['den'] = $op_report['issues']['keyword_density'][$keyword]['density'];
                $data['den_type'] = $op_report['issues']['keyword_density'][$keyword]['type'];
                $data['den_det_type'] = $op_report['issues']['keyword_density'][$keyword]['det_type'];
            }
        
        }
        if ( $keyword_data ) {
            if ( isset( $data['similar'] ) && $data['similar'] ) {
                foreach ( $data['similar'] as $kw_m => $d ) {
                    foreach ( $keyword_data as $kw ) {
                        
                        if ( $kw->keyval == $kw_m ) {
                            
                            if ( !isset( $data['clicks'] ) ) {
                                $data['clicks'] = $kw->clicks;
                            } else {
                                $data['clicks'] += $kw->clicks;
                            }
                            
                            
                            if ( !isset( $data['pos'] ) ) {
                                $data['pos'] = $kw->position;
                            } else {
                                $data['pos'] += $kw->position;
                            }
                        
                        }
                    
                    }
                }
            }
        }
        ?><li class="wsko-co-priority-keyword" data-post="<?php 
        echo  $post_id ;
        ?>" data-prio="<?php 
        echo  $data['prio'] ;
        ?>" data-keyword="<?php 
        echo  $keyword ;
        ?>" data-nonce="<?php 
        echo  wp_create_nonce( 'wsko_co_delete_priority_keyword' ) ;
        ?>">
			<div class="wsko-prio-keyword-actions wsko-inline-block wsko-pull-right">	
				<a data-tooltip="<?php 
        echo  __( 'Add similar Keyword', 'wsko' ) ;
        ?>" class="wsko-co-add-similar-kw wsko-pull-right dark wsko-text-off" href="#" data-nonce="<?php 
        echo  wp_create_nonce( 'wsko_co_add_similar_priority_keyword' ) ;
        ?>" data-keyword="<?php 
        echo  $keyword ;
        ?>" data-post="<?php 
        echo  $post_id ;
        ?>"><i class="fa fa-plus"></i></a>
				<a href="#" data-tooltip="<?php 
        echo  __( 'Delete Keyword', 'wsko' ) ;
        ?>" class="wsko-co-delete-priority-keyword wsko-pull-right dark wsko-text-off"><i class="fa fa-times fa-fw"></i></a> 
				<span class="wsko-prio-keyword-data pull-right wsko-text-off wsko-mr10"><span data-tooltip="<?php 
        echo  __( 'Clicks', 'wsko' ) ;
        ?>" class="wsko-mr5"><i class="fa fa-mouse-pointer fa-fw wsko-small"></i><?php 
        echo  ( isset( $data['clicks'] ) ? $data['clicks'] : '-' ) ;
        ?></span> <span data-tooltip="<?php 
        echo  __( 'Avg. Position', 'wsko' ) ;
        ?>" class="wsko-mr5"><i class="fa fa-bars fa-fw wsko-small"></i><?php 
        echo  ( isset( $data['pos'] ) ? $data['pos'] : '-' ) ;
        ?></span>  <span data-tooltip="<?php 
        echo  __( 'Keyword Density', 'wsko' ) ;
        ?>" style="<?php 
        echo  ( isset( $data['den_det_type'] ) ? ( $data['den_det_type'] == 2 || $data['den_det_type'] == -1 ? 'color:orange' : (( $data['den_type'] == 1 ? 'color:#5cb85c' : 'color:#d9534f' )) ) : '' ) ;
        ?>"><?php 
        echo  ( isset( $data['den'] ) ? WSKO_Class_Helper::format_number( $data['den'], 2 ) : '-' ) ;
        ?>%</span></span>
			</div>
			<span class="wsko-prio-keyword-text-wrapper" data-tooltip="<?php 
        echo  $keyword ;
        ?>"><p class="wsko-prio-keyword-text wsko-inline-block wsko-text-off"><?php 
        echo  $keyword ;
        ?></p></span> 
			<ul class="wsko-co-similar-prio-kw-list" data-keyword="<?php 
        echo  $keyword ;
        ?>"><?php 
        if ( isset( $data['similar'] ) && $data['similar'] ) {
            foreach ( $data['similar'] as $kw => $d ) {
                ?><li class="wsko-co-similar-prio-kw" data-post="<?php 
                echo  $post_id ;
                ?>" data-keyword-key="<?php 
                echo  $keyword ;
                ?>" data-keyword="<?php 
                echo  $kw ;
                ?>"><span class="wsko-text-off wsko-small"><?php 
                echo  $kw ;
                ?></span> <a data-tooltip="<?php 
                echo  __( 'Delete Keyword', 'wsko' ) ;
                ?>" style="display:none;" class="wsko-co-delete-similar-kw wsko-pull-right dark wsko-text-off" href="#" data-nonce="<?php 
                echo  wp_create_nonce( 'wsko_co_delete_similar_priority_keyword' ) ;
                ?>" data-keyword-key="<?php 
                echo  $keyword ;
                ?>" data-post="<?php 
                echo  $post_id ;
                ?>" data-keyword="<?php 
                echo  $kw ;
                ?>"><i class="fa fa-times fa-fw"></i></a></li><?php 
            }
        }
        ?>
				<li></li>
			</ul>
		</li><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_content_optimizer_link( $post_id, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( WSKO_Class_Core::is_demo() ) {
            $post_id = WSKO_Class_Helper::url_to_postid( home_url() );
            // WSKO_Class_Helper::get_home_id();
        }
        
        $open_tab = ( isset( $params['open_tab'] ) ? $params['open_tab'] : false );
        $class = ( isset( $params['class'] ) ? $params['class'] : false );
        $iframe = ( isset( $params['iframe'] ) && $params['iframe'] ? true : false );
        $inline_multi_link = ( isset( $params['multi_link'] ) ? $params['multi_link'] : false );
        
        if ( $iframe ) {
            ?><a href="#" class="bst-co-iframe-link <?php 
            echo  $class ;
            ?>" data-post="<?php 
            echo  $post_id ;
            ?>" <?php 
            echo  ( $iframe ? 'data-iframe="' . htmlspecialchars( WSKO_Controller_Iframe::get_iframe( 'co', $post_id, array(
                'width'  => '100%',
                'height' => '100%',
            ) ) ) . '"' : '' ) ;
            ?>><small class="bst-co-iframe-link-text" data-toggle="tooltip" data-title="<?php 
            echo  __( 'Content Optimizer', 'wsko' ) ;
            ?>"><?php 
            echo  __( 'CO', 'wsko' ) ;
            ?></small></a><?php 
        } else {
            ?><a href="#" class="wsko-content-optimizer-link <?php 
            echo  ( $inline_multi_link ? 'wsko-multi-link' : '' ) ;
            ?> ml3 <?php 
            echo  $class ;
            ?>" data-post="<?php 
            echo  $post_id ;
            ?>" <?php 
            echo  ( $open_tab ? 'data-opentab="' . $open_tab . '"' : '' ) ;
            ?>><small class="wsko-content-optimizer-link-text" data-toggle="tooltip" data-title="<?php 
            echo  __( 'Content Optimizer', 'wsko' ) ;
            ?>"><?php 
            echo  __( 'CO', 'wsko' ) ;
            ?></small></a><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_meta_snippet_container(
        $snippet_type,
        $type,
        $arg,
        $params = array(),
        $return = false,
        &$out_title_r = false,
        &$out_desc_r = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $post_id = ( isset( $params['post_id'] ) ? $params['post_id'] : false );
        $term_id = ( isset( $params['term_id'] ) ? $params['term_id'] : false );
        $preview = ( isset( $params['preview'] ) ? $params['preview'] : false );
        $auto_snippet = WSKO_Class_Core::get_setting( 'auto_social_snippet' );
        $url_c = "";
        $overrides_a = array();
        switch ( $type ) {
            case 'post_id':
                $overrides_a[] = array(
                    'tax'  => false,
                    'term' => false,
                    'post' => $post_id,
                );
                break;
            case 'post_archive':
                $url_c = get_post_type_archive_link( $arg );
                $overrides_a[] = array();
                break;
            case 'post_type':
                $url_c = get_post_type_archive_link( $arg );
                
                if ( strpos( $url_c, '?' ) !== false ) {
                    $url_c .= '&' . $arg . '=' . 'a-post';
                } else {
                    $url_c .= '/a-post/';
                }
                
                $rand_posts = WSKO_Class_Helper::get_random_post( $arg, 5 );
                
                if ( $rand_posts ) {
                    $post_id = $rand_posts[0]->ID;
                    foreach ( $rand_posts as $p ) {
                        $overrides_a[] = array(
                            'tax'  => false,
                            'term' => false,
                            'post' => $p->ID,
                        );
                    }
                    $first_post = WSKO_Class_Helper::get_first_post( $arg );
                    if ( $first_post ) {
                        $post_id = $first_post->ID;
                    }
                }
                
                //$overrides_a[] = array('tax' => false, 'term' => false, 'post' => $post_id);
                break;
            case 'post_tax':
                $tax = $arg;
                $term_f = false;
                $terms = WSKO_Class_Helper::get_random_term( $arg, 5 );
                if ( $terms ) {
                    foreach ( $terms as $term ) {
                        if ( !$term_f ) {
                            $term_f = $term;
                        }
                        $overrides_a[] = array(
                            'tax'  => $tax,
                            'term' => $term->term_id,
                            'post' => false,
                        );
                    }
                }
                $term_id = ( $term_f ? $term_f->term_id : false );
                $term = $term_f;
                //$overrides_a[] = array('tax' => $tax, 'term' => $term_f ? $term_f->term_id : false, 'post' => false);
                break;
            case 'post_term':
                $args = WSKO_Class_Helper::safe_explode( ':', $arg, 2 );
                $term_id = false;
                $tax = false;
                
                if ( count( $args ) == 2 ) {
                    $tax = $args[0];
                    $term_id = $args[1];
                    $overrides_a[] = array(
                        'tax'  => $tax,
                        'term' => $term_id,
                        'post' => false,
                    );
                }
                
                break;
            case 'other':
                $overrides_a[] = array(
                    'tax'  => false,
                    'term' => false,
                    'post' => $post_id,
                );
                break;
        }
        
        if ( $preview ) {
            $meta_obj = WSKO_Class_Compatibility::get_meta_object_ext( $arg, $type, $preview );
        } else {
            $meta_obj = WSKO_Class_Onpage::get_meta_object( $arg, $type );
        }
        
        
        if ( $overrides_a ) {
            $count_ov_a = count( $overrides_a );
            
            if ( $count_ov_a > 1 ) {
                ?><div class="wsko-metas-slide-wrapper" data-max="<?php 
                echo  $count_ov_a ;
                ?>">
					<div style="float:right">
						<a href="#" class="wsko-metas-slide-control wsko-metas-slide-inactive" data-slide="left"><i class="fa fa-chevron-left"></i></a> <a href="#" class="wsko-metas-slide-control" data-slide="right"><i class="fa fa-chevron-right"></i></a>
					</div><?php 
            }
            
            $f = true;
            $title_r = "";
            $desc_r = "";
            foreach ( $overrides_a as $k => $overrides ) {
                ?><div class="wsko-metas-slide <?php 
                echo  ( $f ? 'wsko-metas-slide-active' : '' ) ;
                ?>" data-slide="<?php 
                echo  $k ;
                ?>"><?php 
                if ( isset( $overrides['post'] ) && $overrides['post'] ) {
                    $post_id = $overrides['post'];
                }
                if ( isset( $overrides['term'] ) && $overrides['term'] ) {
                    $term_id = $overrides['term'];
                }
                $title = "";
                $url = $url_c;
                $desc = "";
                $og_title = "";
                $og_desc = "";
                $og_img = "";
                $tw_title = "";
                $tw_desc = "";
                $tw_img = "";
                $title_r = "";
                $desc_r = "";
                $og_title_r = "";
                $og_desc_r = "";
                $og_img_r = "";
                $tw_title_r = "";
                $tw_desc_r = "";
                $tw_img_r = "";
                $meta_obj_r = false;
                
                if ( $post_id ) {
                    //global $post;
                    $post = get_post( $post_id );
                    //setup_postdata($post);
                    $title_o = WSKO_Class_Helper::sanitize_meta( get_the_title( $post->ID ) );
                    $title = $title_d = WSKO_Class_Helper::get_default_page_title( $title_o );
                    $desc = WSKO_Class_Helper::sanitize_meta( $post->post_content, 'the_content' );
                    $url = get_permalink( $post->ID );
                    $meta_obj_r = WSKO_Class_Onpage::get_meta_object( $post->post_type, 'post_type' );
                }
                
                
                if ( $term_id ) {
                    if ( $type === 'post_term' ) {
                        $meta_obj_r = WSKO_Class_Onpage::get_meta_object( $tax, 'post_tax' );
                    }
                    $term = get_term_by( 'id', $term_id, $tax );
                    $title = $title_d = WSKO_Class_Helper::get_default_page_title( WSKO_Class_Helper::sanitize_meta( $term->name ) );
                }
                
                
                if ( $meta_obj_r ) {
                    
                    if ( isset( $meta_obj_r['title'] ) && $meta_obj_r['title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['title'], $overrides );
                        if ( $meta ) {
                            $title_r = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['desc'] ) && $meta_obj_r['desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['desc'], $overrides );
                        if ( $meta ) {
                            $desc_r = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_title'] ) && $meta_obj_r['og_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_title'], $overrides );
                        if ( $meta ) {
                            $og_title_r = $meta;
                        }
                    } else {
                        $og_title_r = $title_r;
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_desc'] ) && $meta_obj_r['og_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_desc'], $overrides );
                        if ( $meta ) {
                            $og_desc_r = $meta;
                        }
                    } else {
                        $og_desc_r = $desc_r;
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_img'] ) && $meta_obj_r['og_img'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_img'], $overrides );
                        if ( $meta ) {
                            $og_img_r = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_title'] ) && $meta_obj_r['tw_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_title'], $overrides );
                        if ( $meta ) {
                            $tw_title_r = $meta;
                        }
                    } else {
                        $tw_title_r = $title_r;
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_desc'] ) && $meta_obj_r['tw_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_desc'], $overrides );
                        if ( $meta ) {
                            $tw_desc_r = $meta;
                        }
                    } else {
                        $tw_desc_r = $desc_r;
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_img'] ) && $meta_obj_r['tw_img'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_img'], $overrides );
                        if ( $meta ) {
                            $tw_img_r = $meta;
                        }
                    }
                    
                    if ( isset( $meta_obj_r['slug'] ) && $meta_obj_r['slug'] ) {
                        $url = home_url( $meta_obj_r['slug'] );
                    }
                }
                
                
                if ( $meta_obj ) {
                    
                    if ( isset( $meta_obj['title'] ) && $meta_obj['title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['title'], $overrides );
                        if ( $meta ) {
                            $title = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['desc'] ) && $meta_obj['desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['desc'], $overrides );
                        if ( $meta ) {
                            $desc = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['og_title'] ) && $meta_obj['og_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['og_title'], $overrides );
                        if ( $meta ) {
                            $og_title = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['og_desc'] ) && $meta_obj['og_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['og_desc'], $overrides );
                        if ( $meta ) {
                            $og_desc = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['og_img'] ) && $meta_obj['og_img'] ) {
                        $meta = $meta_obj['og_img'];
                        if ( $meta ) {
                            $og_img = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['tw_title'] ) && $meta_obj['tw_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['tw_title'], $overrides );
                        if ( $meta ) {
                            $tw_title = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['tw_desc'] ) && $meta_obj['tw_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj['tw_desc'], $overrides );
                        if ( $meta ) {
                            $tw_desc = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj['tw_img'] ) && $meta_obj['tw_img'] ) {
                        $meta = $meta_obj['tw_img'];
                        if ( $meta ) {
                            $tw_img = $meta;
                        }
                    }
                    
                    if ( $type === "post_type" ) {
                        if ( isset( $meta_obj['slug'] ) && $meta_obj['slug'] ) {
                            $url = home_url( $meta_obj['slug'] );
                        }
                    }
                } else {
                    
                    if ( $type !== 'post_id' && !$post_id ) {
                        if ( !$title ) {
                            $title = __( "Some Title", 'wsko' );
                        }
                        if ( !$desc ) {
                            $desc = __( "Description (no post selected)", 'wsko' );
                        }
                    }
                
                }
                
                //if ($post_id)
                //wp_reset_postdata();
                
                if ( $post_id && has_post_thumbnail( $post_id ) && (!$tw_img || !$og_img) ) {
                    $thumbnail_url = get_the_post_thumbnail_url( $post_id );
                    if ( !$tw_img ) {
                        $tw_img = $thumbnail_url;
                    }
                    if ( !$og_img ) {
                        $og_img = $thumbnail_url;
                    }
                }
                
                if ( !$url ) {
                    $url = home_url( '/some-link/' );
                }
                $max_len = 350;
                
                if ( mb_strlen( $desc ) > $max_len ) {
                    $desc = substr( $desc, 0, $max_len - 3 );
                    $desc .= '...';
                }
                
                
                if ( mb_strlen( $og_desc ) > $max_len ) {
                    $og_desc = substr( $og_desc, 0, $max_len - 3 );
                    $og_desc .= '...';
                }
                
                
                if ( mb_strlen( $tw_desc ) > $max_len ) {
                    $tw_desc = substr( $tw_desc, 0, $max_len - 3 );
                    $tw_desc .= '...';
                }
                
                
                if ( $post_id && $type == 'post_type' ) {
                    ?><p><small class="font-unimportant"><?php 
                    echo  sprintf( __( 'Example snippet was generated from "%s"', 'wsko' ), '<a href="' . get_edit_post_link( $post_id ) . '" target="_blank">' . substr( $title_o, 0, 25 ) . '</a>' ) ;
                    ?></small></p><?php 
                } else {
                    
                    if ( $term_id && $type == 'post_tax' ) {
                        ?><p><small class="font-unimportant"><?php 
                        echo  sprintf( __( 'Example snippet was generated from term "%s"', 'wsko' ), '<a href="' . admin_url( 'term.php?taxonomy=' . $tax . '&tag_ID=' . $term->term_taxonomy_id ) . '" target="_blank">' . substr( $term->name, 0, 25 ) . '</a>' ) ;
                        ?></small></p><?php 
                    }
                
                }
                
                switch ( $snippet_type ) {
                    case 'google':
                        WSKO_Class_Template::render_meta_snippet(
                            'google',
                            ( $title && (!$title_r || $title != $title_d) ? $title : $title_r ),
                            $url,
                            ( $desc ? $desc : $desc_r ),
                            array(
                            'no_title' => !isset( $meta_obj['title'] ) || !$meta_obj['title'],
                            'no_desc'  => !isset( $meta_obj['desc'] ) || !$meta_obj['desc'],
                        )
                        );
                        break;
                    case 'facebook':
                        WSKO_Class_Template::render_meta_snippet(
                            'facebook',
                            ( $og_title ? $og_title : (( $og_title_r ? $og_title_r : (( $auto_snippet ? $title : '' )) )) ),
                            $url,
                            ( $og_desc ? $og_desc : (( $og_desc_r ? $og_desc_r : (( $auto_snippet ? $desc : '' )) )) ),
                            array(
                            'img' => ( $og_img ? $og_img : $og_img_r ),
                        )
                        );
                        break;
                    case 'twitter':
                        WSKO_Class_Template::render_meta_snippet(
                            'twitter',
                            ( $tw_title ? $tw_title : (( $tw_title_r ? $tw_title_r : (( $auto_snippet ? $title : '' )) )) ),
                            $url,
                            ( $tw_desc ? $tw_desc : (( $tw_desc_r ? $tw_desc_r : (( $auto_snippet ? $desc : '' )) )) ),
                            array(
                            'img' => ( $tw_img ? $tw_img : $tw_img_r ),
                        )
                        );
                        break;
                }
                ?></div><?php 
                $f = false;
            }
            if ( $count_ov_a > 1 ) {
                ?></div><?php 
            }
        } else {
            ?><div><small class="text-off"><?php 
            echo  __( 'Preview could not be generated (No post found).', 'wsko' ) ;
            ?></small></div><?php 
        }
        
        $out_title_r = $title_r;
        $out_desc_r = $desc_r;
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_meta_snippet(
        $type,
        $title,
        $url,
        $desc,
        $params = array(),
        $return = false
    )
    {
        $img = ( isset( $params['img'] ) ? $params['img'] : false );
        $no_title = ( isset( $params['no_title'] ) && $params['no_title'] ? true : false );
        $no_desc = ( isset( $params['no_desc'] ) && $params['no_desc'] ? true : false );
        if ( $desc && strlen( $desc ) > WSKO_ONPAGE_DESC_MAX ) {
            $desc = substr( $desc, 0, WSKO_ONPAGE_DESC_MAX ) . '...';
        }
        if ( $return ) {
            ob_start();
        }
        switch ( $type ) {
            case 'tab':
                ?>
				<div style="border-bottom:40px solid lightgray;border-left:20px solid transparent;border-right:20px solid transparent;height:0;">
					<div style="height:40px;width:100%;padding:10px;">
						<i class="fa fa-times" style="float:right;"></i>
						<img src="<?php 
                echo  WSKO_Class_Search::get_external_link( 'favicon', WSKO_Class_Helper::get_host_base() ) ;
                ?>">
						<?php 
                echo  $title ;
                ?>
					</div>
				</div>
				<?php 
                break;
            case 'google':
                ?>
				<div class="wsko-google-snippet wsko-p20 wsko-mb15 wsko-z-depth-1">
					<div id="wsko_google_meta_desktop">
						<div>
							<a href="#" class="wsko-google-snippet-top wsko-hightlight-input" data-container=".wsko-set-metas-wrapper" data-input="input[name='title']"><?php 
                echo  $title ;
                ?></a><?php 
                echo  ( $no_title ? ' <span data-tooltip="' . __( 'Default title set', 'wsko' ) . '" data-placement="bottom"><i class="fa fa-info fa-fw" style="display:inline-block;color:gray"></i></span>' : '' ) ;
                ?><br/>
							<span class="wsko-google-snippet-url wsko-hightlight-input" data-container=".wsko-set-metas-wrapper" data-input="input[name='url']"><?php 
                echo  esc_attr( $url ) ;
                ?></span><br/>
							<span class="wsko-google-snippet-desc wsko-hightlight-input" data-container=".wsko-set-metas-wrapper" data-input="textarea[name='desc']"><?php 
                echo  esc_attr( $desc ) ;
                ?></span><?php 
                echo  ( $no_desc ? ' <span data-tooltip="' . __( 'No description set. Snippet may differ from real view!', 'wsko' ) . '" data-placement="bottom"><i class="fa fa-info fa-fw" style="display:inline-block;color:gray"></i></span>' : '' ) ;
                ?>
						</div>	
					</div>		
				</div>
				<?php 
                break;
            case 'facebook':
                ?>
				<div class="wsko-facebook-snippet wsko-z-depth-1">
					<div class="wsko-facebook-snippet-image">
						<?php 
                
                if ( $img ) {
                    ?>
							<img src="<?php 
                    echo  $img ;
                    ?>">
						<?php 
                } else {
                    ?>
							<div class="wsko-social-no-image text-off"><?php 
                    echo  __( 'No Image found', 'wsko' ) ;
                    ?></div>
						<?php 
                }
                
                ?>	
					</div>
					<div class="wsko-facebook-snippet-content">
						<span class="wsko-facebook-title"><?php 
                echo  esc_attr( $title ) ;
                ?></span><br/>
						<span class="wsko-facebook-desc"><?php 
                echo  esc_attr( $desc ) ;
                ?></span><br/>
						<span class="wsko-facebook-url font-unimportant"><?php 
                echo  __( 'URL', 'wsko' ) ;
                ?></span>
					</div>
				</div>
				<?php 
                break;
            case 'twitter':
                ?>
				<div class="wsko-twitter-snippet wsko-z-depth-1" style="border-radius:2px;">
					<div class="wsko-twitter-snippet-image">
						<?php 
                
                if ( $img ) {
                    ?>
							<img src="<?php 
                    echo  $img ;
                    ?>">
						<?php 
                } else {
                    ?>
							<div class="wsko-social-no-image text-off"><?php 
                    echo  __( 'No Image found', 'wsko' ) ;
                    ?></div>
						<?php 
                }
                
                ?>
					</div>
					<div class="wsko-twitter-snippet-content">
						<span class="wsko-twitter-title"><label class="m0"><?php 
                echo  esc_attr( $title ) ;
                ?></label></span><br/>
						<span class="wsko-twitter-desc"><?php 
                echo  esc_attr( $desc ) ;
                ?></span><br/>
						<span class="font-unimportant"><?php 
                echo  __( 'URL', 'wsko' ) ;
                ?></span>
					</div>
				</div>
				<?php 
                break;
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_main_navigation(
        $controllers,
        $current,
        $subpage,
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        foreach ( $controllers as $contr ) {
            $contr = $contr::get_instance();
            
            if ( $contr->subpages && is_array( $contr->subpages ) && !empty($contr->subpages) ) {
                ?><div class="wsko-admin-main-navbar-item waves-effect panel <?php 
                echo  ( $current == $contr::get_link( false, true ) ? 'wsko-active' : '' ) ;
                ?>" data-link="<?php 
                echo  $contr::get_link( false, true ) ;
                ?>" data-name="<?php 
                echo  $contr::get_link( false, true ) ;
                ?>" style="height:auto;">
					<div class="panel-heading">
						<a data-toggle="collapse" data-parent="#wsko_admin_view_sidebar_overlay" href="<?php 
                echo  $contr::get_link() ;
                ?>" data-target="#wsko_admin_<?php 
                echo  $contr::get_link( false, true ) ;
                ?>_subpages">
							<h4 class="panel-title">
								<i class="fa fa-<?php 
                echo  $contr->icon ;
                ?> fa-fw wsko-text-icon"></i> <?php 
                echo  $contr->get_title() ;
                ?> <i class="fa fa-angle-down" style="float:right;"></i>
							</h4>
						</a>
					</div>
					<div id="wsko_admin_<?php 
                echo  $contr::get_link( false, true ) ;
                ?>_subpages" class="wsko-admin-main-navbar-sub-panel panel-collapse collapse <?php 
                echo  ( $current == $contr::get_link( false, true ) ? 'in' : '' ) ;
                ?>">
						<div class="panel-body">
							<ul class="wsko-admin-main-sub-navbar">
								<?php 
                $pages = $contr::get_subpages( true );
                if ( $pages ) {
                    foreach ( $pages as $k => $page ) {
                        ?><li class="wsko-admin-main-sub-navbar-item <?php 
                        echo  ( $subpage == $k ? 'wsko-active' : '' ) ;
                        ?>" data-link="<?php 
                        echo  $contr::get_link( false, true ) . '_' . $contr::get_link( $k, true, false ) ;
                        ?>">
											<?php 
                        WSKO_Class_Template::render_page_link(
                            $contr,
                            $k,
                            $page['title'],
                            array(
                            'button' => false,
                        )
                        );
                        ?>
										</li><?php 
                    }
                }
                $ext_pages = $contr::get_subpages( false );
                
                if ( $ext_pages ) {
                    
                    if ( $pages ) {
                        ?><div></div><?php 
                        //separator
                    }
                    
                    foreach ( $ext_pages as $k => $page ) {
                        ?><li class="wsko-admin-main-sub-navbar-item">
											<a href="<?php 
                        echo  $page['link'] ;
                        ?>"><?php 
                        echo  $page['title'] ;
                        ?></a>
										</li><?php 
                    }
                }
                
                ?>
							</ul>
						</div>
					</div>
				</div><?php 
            } else {
                ?><div class="wsko-admin-main-navbar-item waves-effect <?php 
                echo  ( $current == $contr::get_link( false, true ) ? 'wsko-active' : '' ) ;
                ?>" data-link="<?php 
                echo  $contr::get_link( false, true ) ;
                ?>" data-name="<?php 
                echo  $contr::get_link( false, true ) ;
                ?>">
					<?php 
                WSKO_Class_Template::render_page_link(
                    $contr,
                    false,
                    '<i class="fa fa-' . $contr->icon . ' fa-fw wsko-text-icon"></i> ' . $contr->get_title(),
                    array(
                    'button' => false,
                )
                );
                ?>
				</div><?php 
            }
        
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_notification( $type, $params, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_notice_' );
        $prio = ( isset( $params['prio'] ) && $params['prio'] ? $params['prio'] : false );
        $notifClass = ( isset( $params['notif-class'] ) && $params['notif-class'] ? $params['notif-class'] : false );
        $group = ( isset( $params['group'] ) && $params['group'] ? $params['group'] : false );
        $class = false;
        $add_support = ( isset( $params['show_support'] ) && $params['show_support'] ? true : false );
        $discardable = ( isset( $params['discardable'] ) && $params['discardable'] ? $params['discardable'] : false );
        switch ( $type ) {
            case 'error':
                $class = "error";
                if ( !isset( $params['show_support'] ) ) {
                    $add_support = true;
                }
                break;
            case 'warning':
                $class = "warning";
                /*if (!isset($params['show_support'])) $add_support = true;*/
                break;
            case 'info':
                $class = "info";
                break;
            case 'success':
                $class = "success";
                break;
            default:
                echo  __( 'Not supported notification type!', 'wsko' ) ;
                return;
        }
        
        if ( $class ) {
            
            if ( $discardable ) {
                $wsko_data = WSKO_Class_Core::get_data();
                if ( isset( $wsko_data['discard_notice'] ) && is_array( $wsko_data['discard_notice'] ) && in_array( $discardable, $wsko_data['discard_notice'] ) ) {
                    
                    if ( $return ) {
                        return "";
                    } else {
                        return;
                    }
                
                }
            }
            
            ?><div id="<?php 
            echo  $uniqid ;
            ?>" class="bs-callout wsko-notice wsko-notice-<?php 
            echo  $class ;
            ?> <?php 
            echo  $notifClass ;
            ?>">
				<?php 
            
            if ( $discardable ) {
                ?><span class="pull-right"><?php 
                WSKO_Class_Template::render_ajax_button(
                    '<i class="fa fa-times"></i>',
                    'discard_notice',
                    array(
                    'notice' => $discardable,
                ),
                    array(
                    'no_reload' => true,
                    'no_button' => true,
                    'remove'    => '#' . $uniqid,
                )
                );
                ?></span><?php 
            }
            
            
            if ( $add_support ) {
                ?><a href="#" class="btn btn-flat btn-sm wsko-give-feedback pull-right"><?php 
                echo  __( 'Support', 'wsko' ) ;
                ?></a><?php 
            }
            
            echo  $params['msg'] ;
            
            if ( $prio ) {
                ?><span class="pull-right badge wsko-badge badge-primary" data-toggle="tooltip" title="Priority"><?php 
                echo  $prio ;
                ?></span><?php 
            }
            
            
            if ( $group ) {
                ?><span class="pull-right badge wsko-badge badge-default" data-toggle="tooltip" title="Issue Group"><?php 
                echo  $group ;
                ?></span><?php 
            }
            
            
            if ( isset( $params['list'] ) && $params['list'] && is_array( $params['list'] ) ) {
                ?><ul><?php 
                foreach ( $params['list'] as $list_item ) {
                    ?><li style="margin-left:20px;list-style-type: disc;"><?php 
                    echo  $list_item ;
                    ?></li><?php 
                }
                ?></ul><?php 
            }
            
            
            if ( isset( $params['subnote'] ) && $params['subnote'] ) {
                ?><p class="wsko_callout_note"><strong><?php 
                echo  __( 'Note:', 'wsko' ) ;
                ?></strong> <?php 
                echo  $params['subnote'] ;
                ?></p><?php 
            }
            
            ?>
			</div><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_timespan_widget( $return = false )
    {
        $r = WSKO_Class_Template::render_template( 'controller/template-timespan-select.php', array(), $return );
        if ( $return ) {
            return $r;
        }
    }
    
    public static function render_api_statusbar( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        ?><a href="<?php 
        echo  WSKO_Controller_Settings::get_link( 'apis' ) ;
        ?>" class="wsko-admin-api-statusbar wsko-load-lazy-page" data-controller="<?php 
        echo  WSKO_Controller_Settings::get_link( false, true ) ;
        ?>" data-subpage="<?php 
        echo  WSKO_Controller_Settings::get_link( 'apis', true, false ) ;
        ?>"><?php 
        $api_message = __( "Critical Error!", 'wsko' );
        $api_color = "red";
        
        if ( WSKO_Class_Search::get_ga_client_se() ) {
            $api_message = __( "Credentials missing.", 'wsko' );
            $api_color = "yellow";
            
            if ( WSKO_Class_Search::get_se_token() ) {
                $api_message = __( "Credentials provided.", 'wsko' );
                $api_color = "green";
            }
        
        }
        
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'Google Search API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-search"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p><?php 
        $api_message = __( "Critical Error!", 'wsko' );
        $api_color = "red";
        
        if ( WSKO_Class_Search::get_ga_client_an() ) {
            $api_message = __( "Credentials missing.", 'wsko' );
            $api_color = "yellow";
            
            if ( WSKO_Class_Search::get_an_token() ) {
                $api_message = __( "Credentials provided.", 'wsko' );
                $api_color = "green";
            }
        
        }
        
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'Google Analytics API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-bar-chart"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p><?php 
        $api_color = "red";
        $api_message = __( "Critical Error!", 'wsko' );
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'Facebook API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-facebook-official"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p><?php 
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'Twitter API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-twitter"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p><?php 
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'LinkendIn API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-linkedin-square"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p><?php 
        ?><p class="wsko-admin-api-status-icon" data-toggle="tooltip" data-title="<?php 
        echo  __( 'Google Plus API:', 'wsko' ) ;
        ?> <?php 
        echo  $api_message ;
        ?>"><i class="fa fa-google-plus-official"></i> <i class="fa fa-circle" style="color:<?php 
        echo  $api_color ;
        ?>"></i></p>
		</a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_radial_progress(
        $type,
        $header,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $dataVal = ( isset( $args['val'] ) && $args['val'] ? round( $args['val'] ) : 0 );
        $class = ( isset( $args['class'] ) && $args['class'] ? $args['class'] : '' );
        $info = ( isset( $args['info'] ) && $args['info'] ? $args['info'] : '' );
        $hide_percent = ( isset( $args['hide_percent'] ) && $args['hide_percent'] ? $args['hide_percent'] : false );
        switch ( $type ) {
            case 'default':
                $fg_color = '#898989';
                break;
            case 'error':
                $fg_color = '#d9534f';
                break;
            case 'warning':
                $fg_color = '#f0ad4e';
                break;
            case 'success':
            default:
                $fg_color = '#5cb85c';
                //'#30B455';
                break;
        }
        ?>
			<div class="wsko-circular-progress <?php 
        echo  $class ;
        ?>">
				<p class="wsko-circular-progress-label"><?php 
        echo  $header ;
        ?> <?php 
        
        if ( $info ) {
            ?> <span data-tooltip="<?php 
            echo  $info ;
            ?>"><i class="fa fa-info fa-fw"></i></span> <?php 
        }
        
        ?></p>
				<div class="wsko-circle-progress" data-dimension="40" data-text="<?php 
        echo  $dataVal ;
        echo  ( $hide_percent ? '' : '%' ) ;
        ?>" data-fontsize="10" data-percent="<?php 
        echo  $dataVal ;
        ?>" data-fgcolor="<?php 
        echo  $fg_color ;
        ?>" data-bgcolor="#eee" data-width="10" data-bordersize="3" data-animationstep="2"></div>
			</div>	
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_chart(
        $type,
        $header,
        $data,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( $data ) {
            $pixel_height = ( isset( $args['pixel_height'] ) && $args['pixel_height'] ? $args['pixel_height'] : 280 );
            $pixel_width = ( isset( $args['pixel_width'] ) && $args['pixel_width'] ? $args['pixel_width'] : 495 );
            $chart_height = ( isset( $args['chart_height'] ) && $args['chart_height'] ? intval( $args['chart_height'] ) : 60 );
            $chart_width = ( isset( $args['chart_width'] ) && $args['chart_width'] ? intval( $args['chart_width'] ) : 85 );
            $chart_left = ( isset( $args['chart_left'] ) && $args['chart_left'] ? intval( $args['chart_left'] ) : 0 );
            $hide_legend = ( isset( $args['hide_legend'] ) && $args['hide_legend'] ? $args['hide_legend'] : false );
            $legend_pos = ( isset( $args['legend_pos'] ) && $args['legend_pos'] ? $args['legend_pos'] : 'top' );
            $legend_align = ( isset( $args['legend_align'] ) && $args['legend_align'] ? $args['legend_align'] : 'start' );
            $isStacked = ( isset( $args['isStacked'] ) && $args['isStacked'] ? true : false );
            $table_filter = ( isset( $args['table_filter'] ) ? $args['table_filter'] : false );
            $multi_y = ( isset( $args['multi_y'] ) ? $args['multi_y'] : false );
            $format = ( isset( $args['format'] ) ? $args['format'] : false );
            $colors = ( isset( $args['colors'] ) ? $args['colors'] : false );
            $chart_id = ( isset( $args['chart_id'] ) ? $args['chart_id'] : '' );
            $big_bar = ( isset( $args['big_bar'] ) ? $args['big_bar'] : '' );
            $axisTitle = ( isset( $args['axisTitle'] ) ? $args['axisTitle'] : '' );
            $axisTitleY = ( isset( $args['axisTitleY'] ) ? $args['axisTitleY'] : '' );
            $row_colors = ( isset( $args['row_colors'] ) ? $args['row_colors'] : false );
            $raw_add = ( isset( $args['raw_add'] ) ? $args['raw_add'] : false );
            $ready_event = ( isset( $args['ready_event'] ) ? $args['ready_event'] : false );
            $toggle_columns = ( isset( $args['toggle_columns'] ) ? $args['toggle_columns'] : false );
            $class = false;
            switch ( $type ) {
                case 'area':
                    $class = "AreaChart";
                    break;
                case 'line':
                    $class = "LineChart";
                    break;
                case 'column':
                    $class = "ColumnChart";
                    break;
                case 'column_line':
                    $class = "ComboChart";
                    break;
                case 'bar':
                    $class = "BarChart";
                    break;
                case 'bar_y':
                    $class = "BarChart";
                    break;
                case 'pie':
                    $class = "PieChart";
                    break;
                case 'world':
                    $class = "GeoChart";
                    break;
                default:
                    return;
            }
            $ticks = array();
            
            if ( $type == 'bar' ) {
                $max_ticks = 0;
                foreach ( $data as $d ) {
                    $d = array_values( (array) $d );
                    if ( is_array( $d[1] ) ) {
                        $d[1] = $d[1]['v'];
                    }
                    $d[1] = intval( $d[1] );
                    if ( $d[1] > $max_ticks ) {
                        $max_ticks = $d[1];
                    }
                }
                if ( $max_ticks && $max_ticks < 30 ) {
                    for ( $i = 0 ;  $i <= $max_ticks ;  $i++ ) {
                        $ticks[] = $i;
                    }
                }
            }
            
            $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_graph_' );
            ?>
				<div class="wsko-brand wsko-chart-outer-wrapper">
					<?php 
            
            if ( $toggle_columns && $header ) {
                $f = true;
                foreach ( $header as $k => $v ) {
                    
                    if ( $f ) {
                        $f = false;
                        continue;
                    }
                    
                    //if ($row_colors)
                    ?><label class="wsko-toggle-columns-label"><input type="checkbox" class="wsko-chart-column-toggle form-control" data-column="<?php 
                    echo  $k ;
                    ?>" checked> <?php 
                    echo  $v ;
                    ?></label><?php 
                }
            }
            
            ?>
					
					<img src="<?php 
            echo  WSKO_PLUGIN_URL ;
            ?>/admin/img/logo-bl.png" <?php 
            if ( $type == 'pie' || $type == 'world' ) {
                echo  'style="display:none;"' ;
            }
            ?> />  <!-- don´t show in pie & world chart -->
					<div class="wsko-chart-wrapper" id="<?php 
            echo  $uniqid ;
            ?>"></div>
				</div>
			<style>
				<?php 
            /*div.wsko-chart-wrapper svg g:last-child,*/
            ?>
				.google-visualization-tooltip { 
					/*pointer-events: none; */
					padding:0px;
					border-color:#ddd;
					-webkit-box-shadow: 0 1px 5px rgba(0,0,0,0.06);
					box-shadow: 0 1px 5px rgba(0,0,0,0.06);
				}
			</style>
			<script type="text/javascript">
			jQuery(document).ready(function($){
				if ((typeof google !== 'undefined') && (typeof google.charts !== 'undefined'))
				{
					google.charts.setOnLoadCallback(function () {
						var options = {
							//width: <?php 
            echo  $pixel_width ;
            ?>,
							width: '100%',
							height: <?php 
            echo  $pixel_height ;
            ?>,
							chartArea: { width: "<?php 
            echo  $chart_width ;
            ?>%", height: "<?php 
            echo  $chart_height ;
            ?>%" <?php 
            echo  ( $chart_left ? ',left: "' . $chart_left . '%", right: "5%"' : '' ) ;
            ?> },
							tooltip: { isHtml: true },
							<?php 
            echo  ( $type === 'bar_y' ? "bars: 'vertical'," : '' ) ;
            ?>
						//Custom Options
						<?php 
            
            if ( $type == 'area' || $type == 'line' ) {
                ?>	
							isStacked: <?php 
                echo  ( $isStacked ? 'true' : 'false' ) ;
                ?>,
							<?php 
                
                if ( $multi_y ) {
                    ?>
							series: {
								0: {targetAxisIndex: 0, color: '#337AB7'},
								1: {targetAxisIndex: 1, color: '#86c5f0'}
							},
							vAxes: [
								{title: "<?php 
                    echo  $multi_y[0] ;
                    ?>"},
								{title: "<?php 
                    echo  $multi_y[1] ;
                    ?>"}
							],
							<?php 
                }
                
                ?>
							hAxis: {title: '<?php 
                echo  ( $axisTitle ? $axisTitle : '' ) ;
                ?>',  titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'transparent'}, showTextEvery: 6},
							vAxis: {title: '<?php 
                echo  ( $axisTitleY ? $axisTitleY : '' ) ;
                ?>', minValue: 0, titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'#eee'}, baselineColor: '#ccc'},
							backgroundColor: { fill:'transparent' },
							pointSize: 3,
							<?php 
                if ( $type == 'area' ) {
                    ?>
							series: {
								0:{color: '#337AB7', areaOpacity: 0.3},
								1:{color: '#86c5f0', areaOpacity: 0.05}
							},
							<?php 
                }
                ?>
						<?php 
            } else {
                
                if ( $type == 'bar' ) {
                    ?>
							<?php 
                    echo  ( $big_bar ? 'chartArea: { left: "40%", right:30},' : '' ) ;
                    ?>
							hAxis: {<?php 
                    echo  ( $ticks ? 'ticks: [' . implode( ',', $ticks ) . '],' : '' ) ;
                    ?> title: '<?php 
                    echo  ( $axisTitle ? $axisTitle : '' ) ;
                    ?>',  titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'#eee'}, baselineColor: '#ccc'},
							vAxis: {title: '<?php 
                    echo  ( $axisTitleY ? $axisTitleY : '' ) ;
                    ?>', minValue: 0, titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'transparent'}},			
							backgroundColor: { fill:'transparent' },				  
						<?php 
                } else {
                    
                    if ( $type == 'column' ) {
                        
                        if ( $multi_y ) {
                            ?>
							series: {
								0: {targetAxisIndex: 0, color: '#337AB7'},
								1: {targetAxisIndex: 1, color: '#86c5f0'}
							},
							vAxes: [
								{title: "<?php 
                            echo  $multi_y[0] ;
                            ?>"},
								{title: "<?php 
                            echo  $multi_y[1] ;
                            ?>"}
							],
							<?php 
                        }
                        
                        switch ( $chart_id ) {
                            case 'lost_new_keywords':
                                $showTextEvery = 4;
                                break;
                            default:
                                $showTextEvery = 1;
                        }
                        ?>
							
							isStacked: <?php 
                        echo  ( $isStacked ? 'true' : 'false' ) ;
                        ?>,
							hAxis: {title: '<?php 
                        echo  $axisTitle ;
                        ?>',  titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'transparent'}, showTextEvery: <?php 
                        echo  $showTextEvery ;
                        ?>},				 
							vAxis: {title: '<?php 
                        echo  ( $axisTitleY ? $axisTitleY : '' ) ;
                        ?>', minValue: 0, titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'#eee'}, baselineColor: '#ccc'},	
							backgroundColor: { fill:'transparent' },
						<?php 
                    } else {
                        
                        if ( $type == 'column_line' ) {
                            ?>
							seriesType: 'bars',
							series: {1: {type: 'line'}},
							bar: {groupWidth: "95%"},
							hAxis: {title: '<?php 
                            echo  ( $axisTitle ? $axisTitle : '' ) ;
                            ?>', direction: -1, slantedText: true, slantedTextAngle: 45,  titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'transparent'}},
							vAxis: {title: '<?php 
                            echo  ( $axisTitleY ? $axisTitleY : '' ) ;
                            ?>',minValue: 0, titleTextStyle: {color: '#888'}, textStyle: {color: '#888'}, gridlines: {color:'#eee'}, baselineColor: '#ccc'},	
							backgroundColor: { fill:'transparent' },			  
						<?php 
                        } else {
                            
                            if ( $type == 'pie' ) {
                                ?>
							backgroundColor: { fill:'transparent' },	
						<?php 
                            } else {
                                if ( $type == 'world' ) {
                                    ?>
							backgroundColor: 'transparent',
						<?php 
                                }
                            }
                        
                        }
                    
                    }
                
                }
            
            }
            
            ?>


						/* Colors */
						<?php 
            
            if ( $colors ) {
                ?>colors: ['<?php 
                echo  implode( "','", $colors ) ;
                ?>'],<?php 
            } else {
                //defaults
                ?>colors: [/*'#325f86',*/ '#337AB7', '#3498db', '#72a9ce', '#95ccc1', '#1ABC9C', '#5eab92', '#74bd84', '#65b36f', '#679671', '#7f9283'],<?php 
            }
            
            ?> 
						
						/* Onpage Charts Color / Custom Settings */
						<?php 
            switch ( $chart_id ) {
                case 'prio_keywords':
                    ?> bar: {groupWidth: 130}, <?php 
                    break;
                    /* 'title_dupl': ?> series: {0:{color: '#5cb85c'}, 1:{color: '#f0ad4e'}, 2:{color: '#d9534f'}, 3:{color: '#d9534f'}, 4:{color: '#d9534f'}, 5:{color: '#d9534f'}, 6:{color: '#d9534f'}, 7:{color: '#d9534f'}, 8:{color: ''}}, < ?php ; break; */
                    /* case 'desc_dupl': ?> colors: ['#5cb85c', '#f0ad4e', '#d9534f', '#d9534f', '#d9534f', '#d9534f', '#d9534f', '#d9534f', '#d9534f'], < ?php; break; */
                    /* case 'content_length': ?> colors: ['#d9534f', 'red', '#1ABC9C', '#2ECC71', '#16a085'], < ?php; break; */
            }
            ?>
							
						legend: { position: '<?php 
            echo  ( $hide_legend ? 'none' : $legend_pos ) ;
            ?>', alignment: '<?php 
            echo  $legend_align ;
            ?>', scrollArrows: {activeColor: '#337ab7',inactiveColor: '#ccc'}, pagingTextStyle: {color:'#337ab7'} },
						animation: {"startup": true, duration: 1200, easing: 'out'}
						<?php 
            echo  ( $raw_add ? ',' . $raw_add : '' ) ;
            ?>
						};
						var data = google.visualization.arrayToDataTable([
							['<?php 
            echo  implode( "'" . (( $row_colors ? ',{role:"style", type: "string"}' : '' )) . ",'", $header ) ;
            ?>'<?php 
            echo  ( $row_colors ? ',{role:"style", type: "string"}' : '' ) ;
            ?>],
							<?php 
            foreach ( $data as $r_k => $row ) {
                $first = true;
                ?>[<?php 
                foreach ( $row as $k => $val ) {
                    if ( !$first ) {
                        echo  ',' ;
                    }
                    $first = false;
                    
                    if ( is_integer( $val ) || is_numeric( $val ) ) {
                        echo  '{v: ' . $val . (( isset( $format[$k] ) ? ', f: "' . str_replace( '{0}', $val, $format[$k] ) . '"' : '' )) . '}' ;
                    } else {
                        
                        if ( is_array( $val ) ) {
                            
                            if ( is_integer( $val['v'] ) || is_numeric( $val['v'] ) ) {
                                echo  '{v: ' . $val['v'] . ', f: "' . preg_replace( "/\r|\n/", "", ( isset( $format[$k] ) ? str_replace( '{0}', $val['f'], $format[$k] ) : $val['f'] ) ) . '"}' ;
                            } else {
                                echo  '{v: "' . preg_replace( "/\r|\n/", "", $val['v'] ) . '", f: "' . preg_replace( "/\r|\n/", "", ( isset( $format[$k] ) ? str_replace( '{0}', $val['f'], $format[$k] ) : $val['f'] ) ) . '"}' ;
                            }
                        
                        } else {
                            echo  '{v: "' . preg_replace( "/\r|\n/", "", $val ) . '"' . (( isset( $format[$k] ) ? ', f: "' . preg_replace( "/\r|\n/", "", str_replace( '{0}', $val, $format[$k] ) ) . '"' : '' )) . '}' ;
                        }
                    
                    }
                    
                    if ( $row_colors ) {
                        
                        if ( isset( $row_colors[$r_k] ) ) {
                            
                            if ( is_array( $row_colors[$r_k] ) ) {
                                
                                if ( isset( $row_colors[$r_k][$k] ) ) {
                                    ?>,'<?php 
                                    echo  $row_colors[$r_k][$k] ;
                                    ?>'<?php 
                                } else {
                                    ?>,''<?php 
                                }
                            
                            } else {
                                ?>,'<?php 
                                echo  $row_colors[$r_k] ;
                                ?>'<?php 
                            }
                        
                        } else {
                            ?>,''<?php 
                        }
                    
                    }
                }
                /*if ($row_colors)
                		{
                			if (isset($row_colors[$r_k]))
                			{
                				?>,'<?=$row_colors[$r_k]?>'<?php
                			}
                			else
                			{
                				?>,''<?php
                			}
                		}*/
                ?>],<?php 
            }
            ?>
						]);
						var data_view = new google.visualization.DataView(data),
							chart_obj = $('#<?php 
            echo  $uniqid ;
            ?>'),
							chart = new google.visualization.<?php 
            echo  $class ;
            ?>(chart_obj.get(0)),
							needsRedraw = true;
						google.visualization.events.addListener(chart, 'ready', function (){
							<?php 
            if ( $ready_event ) {
                echo  $ready_event ;
            }
            ?>
						});
						function draw()
						{
							if (needsRedraw)
							{
								if ($('#<?php 
            echo  $uniqid ;
            ?>').is(":visible"))
								{
									chart.draw(data_view, options);
									needsRedraw = false;
								}
							}
						}
						var resizeTO;
						function queue_redraw(instant)
						{
							if(resizeTO) clearTimeout(resizeTO);
							
							if (instant)
								draw()
							else
								resizeTO = setTimeout(function() {
									draw();
								}, 1000);
						}
						<?php 
            
            if ( $table_filter ) {
                ?>
						var columnReg = JSON.parse('<?php 
                echo  json_encode( $table_filter['value_matrix'] ) ;
                ?>');
						google.visualization.events.addListener(chart, 'select', function (){
							var selection = chart.getSelection();
							for (var i = 0; i < selection.length; i++) {
								var item = selection[i];
								if (item.row != null)// && item.column != null)
								{
									if (item.column == null)
										item.column = 1;
									if (columnReg && columnReg[item.column-1] && columnReg[item.column-1][item.row])
									{
										<?php 
                
                if ( isset( $table_filter['type'] ) ) {
                    if ( $table_filter['type'] === 'click' ) {
                        ?>
												$(columnReg[item.column-1][item.row][0]).click();
											<?php 
                    }
                } else {
                    ?>
											$('<?php 
                    echo  $table_filter['table'] ;
                    ?>').trigger('wsko_add_external_filter', [columnReg[item.column-1][item.row]]);
										<?php 
                }
                
                ?>
									}
								}
							}
						});
						<?php 
            }
            
            ?>
						$(window).resize(function() {
							needsRedraw = true;
							queue_redraw();
						});
						//$(document).on('wsko_init_page', function() {
							//queue_redraw();
							$('#<?php 
            echo  $uniqid ;
            ?>').parents('.tab-pane,.wsko-tab').each(function(index){
								var $pane = $(this);
								id = $pane.attr('id');
								if (id)
								{
									$('a[href="#'+id+'"][data-toggle="tab"]').on('shown.bs.tab', function(event){
										queue_redraw(true);
									});
									$('a.wsko-nav-link[href="#'+id+'"]').click(function(event){
										queue_redraw(true);
									});
								}
							});
							queue_redraw(true);
						//});
						<?php 
            
            if ( $toggle_columns ) {
                ?>
							$('#<?php 
                echo  $uniqid ;
                ?>').closest('.wsko-chart-outer-wrapper').find('.wsko-chart-column-toggle').change(function(event){
								var toggled = [];
								$('#<?php 
                echo  $uniqid ;
                ?>').closest('.wsko-chart-outer-wrapper').find('.wsko-chart-column-toggle').each(function(index){
									if (!$(this).is(':checked'))
										toggled.push($(this).data('column'));
								});
								data_view = new google.visualization.DataView(data);
								data_view.hideColumns(toggled);
								needsRedraw = true;
								queue_redraw();
							});
						<?php 
            }
            
            ?>
						<?php 
            ?>
					});
				}
			});
			</script>
			<div class="clearfix"></div><?php 
        } else {
            WSKO_Class_Template::render_template( 'misc/template-no-data.php', array() );
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_static_chart(
        $type,
        $header,
        $data,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $width = ( isset( $params['width'] ) ? $params['width'] : 10 );
        //200;
        $height = ( isset( $params['height'] ) ? $params['height'] : 5 );
        //100;
        $count = 0;
        $max = array();
        foreach ( $data as $d ) {
            $count++;
            foreach ( $d as $i => $v ) {
                $v = floatval( $v );
                
                if ( !isset( $max[$i] ) ) {
                    $max[$i] = $v;
                } else {
                    $max[$i] += $v;
                }
            
            }
        }
        ?><div class="wsko-chart-container" style="width:<?php 
        echo  $width ;
        ?>cm;height:<?php 
        echo  $height ;
        ?>cm">
			<?php 
        switch ( $type ) {
            case 'bar':
                $i = 0;
                $width_m = 0.2;
                $width_s = round( $width / $count, 2 );
                //($width_b + ($width_bm*2)) / $width * 100;
                foreach ( $data as $d ) {
                    $height_v = round( $d[1] / $max[1] * $height, 2 );
                    $width_v = round( $width_s * $i, 2 ) - round( $width_m / 2, 2 );
                    ?><div class="wsko-chart-bar-item" style="left:<?php 
                    echo  $width_v + $width_m ;
                    ?>cm;top:<?php 
                    echo  $height - $height_v ;
                    ?>cm;height:<?php 
                    echo  $height_v ;
                    ?>cm;width:<?php 
                    echo  $width_s - $width_m ;
                    ?>cm">
						<?php 
                    echo  $d[1] ;
                    ?>
						<div class="wsko-chart-bar-label" style="top: <?php 
                    echo  $height_v ;
                    ?>cm;"><?php 
                    echo  $d[0] ;
                    ?></div>
					</div><?php 
                    $i++;
                }
                break;
            case 'area':
                break;
            case 'line':
                $i = 0;
                $width_m = 0.2;
                $width_s = round( $width / $count, 2 );
                //($width_b + ($width_bm*2)) / $width * 100;
                foreach ( $data as $d ) {
                    $height_v = round( $d[1] / $max[1] * $height, 2 );
                    $width_v = round( $width_s * $i, 2 ) - round( $width_m / 2, 2 );
                    ?><div class="wsko-chart-line-item" style="left:<?php 
                    echo  $width_v + $width_m ;
                    ?>cm;top:<?php 
                    echo  $height - $height_v ;
                    ?>cm;height:<?php 
                    echo  $height_v ;
                    ?>cm;width:<?php 
                    echo  $width_s - $width_m ;
                    ?>cm">
						<?php 
                    echo  $d[1] ;
                    ?>
						<div class="wsko-chart-bar-label" style="top: <?php 
                    echo  $height_v ;
                    ?>cm;"><?php 
                    echo  $d[0] ;
                    ?></div>
					</div><?php 
                    $i++;
                }
                break;
            case 'pie':
                ?><circle r="25" cx="50" cy="50" class="pie"/><?php 
                break;
        }
        ?>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_table(
        $header,
        $data,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $id = ( isset( $params['id'] ) && $params['id'] ? $params['id'] : false );
        $class = ( isset( $params['class'] ) && $params['class'] ? $params['class'] : false );
        $row_class = ( isset( $params['row_class'] ) && $params['row_class'] ? $params['row_class'] : false );
        $scrollY = ( isset( $params['scrollY'] ) && $params['scrollY'] ? $params['scrollY'] : false );
        $scrollX = ( isset( $params['scrollX'] ) && $params['scrollX'] ? $params['scrollX'] : false );
        $ajax = ( isset( $params['ajax'] ) && $params['ajax'] ? $params['ajax'] : false );
        $filter = ( isset( $params['filter'] ) && $params['filter'] ? $params['filter'] : false );
        $export = ( isset( $params['export'] ) ? $params['export'] : true );
        $order = ( isset( $params['order'] ) && $params['order'] ? $params['order'] : false );
        $no_pages = ( isset( $params['no_pages'] ) && $params['no_pages'] ? true : false );
        $row_click = ( isset( $params['row_click'] ) ? $params['row_click'] : false );
        ?>
		<div class="wsko-table-wrapper <?php 
        echo  ( $no_pages ? 'wsko-table-small wsko_table_simple' : '' ) ;
        ?>">
			<div class="wsko-table-controls col-sm-12 col-xs-12">
				<div style="position:relative;z-index:3;">
					<?php 
        if ( $export && !$no_pages ) {
            WSKO_Class_Template::render_export_link();
        }
        
        if ( $ajax ) {
            ?><div style="float: right;"><a class="wsko-reload-ajax-table btn btn-flat btn-sm" style="line-height: 25px;" href="#"><i class="fa fa-refresh"></i> <?php 
            echo  __( 'Reload', 'wsko' ) ;
            ?></a></div><?php 
        }
        
        
        if ( $filter ) {
            ?>

							<div class="button-dropdown">
								<button class="btn btn-flat btn-sm button toggle"><i class="fa fa-filter fa-fw wsko-mr5"></i><?php 
            echo  __( 'Filter', 'wsko' ) ;
            ?>  <i class="fa fa-angle-down fa-fw"></i></button>
								<ul class="dropdown-menu dropdown">
									<?php 
            foreach ( $filter as $k => $f ) {
                $add = "";
                if ( $f['type'] == 'select' && !$f['values'] ) {
                    continue;
                }
                switch ( $f['type'] ) {
                    case 'number_range':
                        $add = (( isset( $f['min'] ) ? ' data-min="' . $f['min'] . '"' : '' )) . (( isset( $f['max'] ) ? ' data-max="' . $f['max'] . '"' : '' ));
                        break;
                    case 'select':
                        $add = ' data-values="' . htmlspecialchars( json_encode( $f['values'] ) ) . '"';
                        break;
                }
                ?><li><a href="#" class="wsko-table-add-filter" data-name="<?php 
                echo  $k ;
                ?>" data-type="<?php 
                echo  $f['type'] ;
                ?>" data-title="<?php 
                echo  $f['title'] ;
                ?>"<?php 
                echo  $add ;
                ?>><?php 
                echo  $f['title'] ;
                ?></a></li><?php 
            }
            ?>
								</ul>
							</div>			
							<!-- if filter active -->
							<?php 
            /* <button class="btn btn-flat" type="button">Clear Filter</button> */
            ?>
							
							<div class="wsko-table-filter-box">
							</div>
						<?php 
        }
        
        ?>
				</div>
			</div>
			<table id="<?php 
        echo  ( $id ? $id : WSKO_Class_Helper::get_unique_id( 'wsko_table_' ) ) ;
        ?>" class="table table-striped table-bordered wsko-tables table-condensed <?php 
        echo  ( $class ? $class : '' ) ;
        ?> <?php 
        echo  ( $ajax ? 'wsko-ajax-tables' : '' ) ;
        ?>" <?php 
        echo  ( $order ? 'data-def-order="' . $order['col'] . '" data-def-orderdir="' . $order['dir'] . '"' : '' ) ;
        ?> <?php 
        echo  ( $ajax ? 'data-action="' . $ajax['action'] . '" data-nonce="' . wp_create_nonce( $ajax['action'] ) . '"' . (( isset( $ajax['arg'] ) ? ' data-arg="' . $ajax['arg'] . '"' : '' )) . (( isset( $ajax['arg2'] ) ? ' data-arg2="' . $ajax['arg2'] . '"' : '' )) : '' ) ;
        ?> data-scrollY="<?php 
        echo  $scrollY ;
        ?>" data-scrollX="<?php 
        echo  $scrollX ;
        ?>" data-row-class="<?php 
        echo  $row_class ;
        ?>" cellspacing="0" width="100%">
				<thead>
					<tr>
					<?php 
        
        if ( $ajax ) {
            foreach ( $header as $k => $h ) {
                $width = false;
                $cl = false;
                
                if ( is_array( $h ) ) {
                    $width = ( isset( $h['width'] ) ? $h['width'] : false );
                    $cl = ( isset( $h['class'] ) ? $h['class'] : false );
                    $h = $h['name'];
                }
                
                ?><th data-name="<?php 
                echo  $k ;
                ?>" <?php 
                echo  ( $width ? 'data-width="' . $width . '"' : '' ) ;
                ?> <?php 
                echo  ( $cl ? 'data-class="' . $cl . '"' : '' ) ;
                ?>><?php 
                echo  $h ;
                ?></th><?php 
            }
        } else {
            foreach ( $header as $k => $h ) {
                $width = false;
                
                if ( is_array( $h ) ) {
                    $width = $h['width'];
                    $h = $h['name'];
                }
                
                ?><th <?php 
                echo  ( $width ? 'data-width="' . $width . '"' : '' ) ;
                ?>><?php 
                echo  $h ;
                ?></th><?php 
            }
        }
        
        ?>
					</tr>
				</thead>
				<tfoot>
					<tr>
					<?php 
        foreach ( $header as $k => $h ) {
            $width = false;
            if ( is_array( $h ) ) {
                $h = $h['name'];
            }
            ?><th><?php 
            echo  $h ;
            ?></th><?php 
        }
        ?>
					</tr>
				</tfoot>
				<tbody>
					<?php 
        if ( $data ) {
            foreach ( $data as $k => $row ) {
                ?><tr <?php 
                echo  ( $row_click ? 'class="wsko-table-row-click" data-container="' . $row_click['container'][$k] . '" data-back="' . $row_click['back_link'] . '"' : '' ) ;
                ?>><?php 
                foreach ( $row as $value ) {
                    ?><td <?php 
                    echo  ( isset( $value['class'] ) ? 'class="' . $value['class'] . '"' : '' ) ;
                    ?> <?php 
                    echo  ( isset( $value['order'] ) ? 'data-order="' . $value['order'] . '"' : '' ) ;
                    ?>><?php 
                    echo  $value['value'] ;
                    ?></td><?php 
                }
                ?></tr><?php 
            }
        }
        ?>
				</tbody>
			</table>
		</div>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_progress(
        $title,
        $value,
        $max,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $data = ( isset( $args['data'] ) ? $args['data'] : false );
        $class = ( isset( $args['class'] ) ? $args['class'] : false );
        $progress_class = ( isset( $args['progress_class'] ) ? $args['progress_class'] : false );
        $text = ( isset( $args['text'] ) ? $args['text'] : '' );
        $tooltip = ( isset( $args['tooltip'] ) ? $args['tooltip'] : '' );
        $no_tooltip = ( isset( $args['no_tooltip'] ) && $args['no_tooltip'] ? true : false );
        ?>
		<div class="row wsko-progress-wrapper">
			<div class="col-sm-3 col-xs-12">
				<span><?php 
        echo  $title ;
        ?></span>
			</div>
			<div class="col-sm-9 col-xs-12">
				<div class="wsko-progress-bar progress <?php 
        echo  $class ;
        ?>" <?php 
        echo  ( !$no_tooltip ? 'data-toggle="tooltip" title="' . (( $tooltip ? $tooltip : $value )) . '"' : '' ) ;
        ?> <?php 
        if ( $data ) {
            foreach ( $data as $k => $d ) {
                echo  ' data-' . $k . '="' . $d . '"' ;
            }
        }
        ?>>
				  <div class="progress-bar progress-bar-<?php 
        echo  $progress_class ;
        ?>" role="progressbar" style="width: <?php 
        echo  ( $max && $value ? floor( $value / $max * 100 ) : 0 ) ;
        ?>%" aria-valuenow="<?php 
        echo  $value ;
        ?>" aria-valuemin="0" aria-valuemax="<?php 
        echo  $max ;
        ?>"></div>
				  <span style="position:absolute;top:0px;left:0px;right:0px;bottom:0px;text-align:center;"><?php 
        echo  $text ;
        ?></span>
				</div>
			</div>
		</div>			
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_progress_stacked(
        $title,
        $values,
        $max,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $class = ( isset( $args['class'] ) ? $args['class'] : false );
        $fill = ( isset( $args['fill'] ) ? $args['fill'] : true );
        $progressFullWidth = ( isset( $args['progress_full_width'] ) ? $args['progress_full_width'] : false );
        ?>
		<div class="row wsko-progress-wrapper">
			<?php 
        
        if ( !$progressFullWidth ) {
            ?>
				<div class="col-sm-3 col-xs-12">
					<span><?php 
            echo  $title ;
            ?></span>
				</div>
			<?php 
        }
        
        ?>	
			<div class="<?php 
        echo  ( $progressFullWidth ? 'col-sm-12' : 'col-sm-9' ) ;
        ?> col-xs-12">
				<div class="wsko-progress-bar progress <?php 
        echo  $class ;
        ?>" >
					<?php 
        $sum = 0;
        foreach ( $values as $k => $v ) {
            $sum += $values[$k]['width_val'] = ( $max ? floor( $v['value'] / $max * 100 * 100 ) / 100 : 0 );
        }
        $add = 0;
        if ( $fill ) {
            
            if ( $sum > 100 ) {
                $add = -($sum - 100);
            } else {
                $add = 100 - $sum;
            }
        
        }
        foreach ( $values as $v ) {
            $tooltip = ( isset( $v['tooltip'] ) ? $v['tooltip'] : false );
            $data = ( isset( $v['data'] ) && is_array( $v['data'] ) ? $v['data'] : array() );
            $width_val = $v['width_val'];
            
            if ( $add && $width_val && $width_val + $add > 0 ) {
                $width_val += $add;
                $add = 0;
            }
            
            ?><a href="#" class="progress-bar progress-bar-<?php 
            echo  $v['class'] ;
            ?>" <?php 
            echo  ( $tooltip ? 'data-toggle="tooltip" data-title="' . $tooltip . '"' : '' ) ;
            ?> role="progressbar" <?php 
            foreach ( $data as $k => $d ) {
                echo  ' data-' . $k . '="' . $d . '"' ;
            }
            ?> style="width: <?php 
            echo  $width_val ;
            ?>%"><?php 
            echo  $v['title'] ;
            ?></a><?php 
        }
        ?>
				</div>
			</div>
		</div>			
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_progress_icon(
        $val,
        $val_perc,
        $params = array(),
        $return = false
    )
    {
        $tooltip_add = ( isset( $params['tooltip'] ) ? $params['tooltip'] : false );
        $flip = ( isset( $params['flip'] ) && $params['flip'] ? true : false );
        $absolute = ( isset( $params['absolute'] ) && $params['absolute'] ? true : false );
        $decimals = ( isset( $params['decimals'] ) ? $params['decimals'] : 0 );
        $formated = ( isset( $params['formated'] ) && $params['formated'] ? true : false );
        if ( $return ) {
            ob_start();
        }
        ?><span class="wsko-progress-icon wsko-ml5 <?php 
        echo  ( $val_perc ? ( $val_perc < 0 ? 'wsko-red' : 'wsko-green' ) : 'wsko-gray' ) ;
        ?>" data-tooltip="<?php 
        echo  (( $absolute ? '' : (( $formated ? $val : WSKO_Class_Helper::format_number( $val ) )) )) . (( $tooltip_add !== false ? $tooltip_add : '' )) ;
        ?>"><?php 
        
        if ( $val_perc < 0 ) {
            
            if ( $flip ) {
                ?><i class="fa fa-angle-up"></i><?php 
            } else {
                ?><i class="fa fa-angle-down"></i><?php 
            }
        
        } else {
            
            if ( $val_perc > 0 ) {
                
                if ( $flip ) {
                    ?><i class="fa fa-angle-down"></i><?php 
                } else {
                    ?><i class="fa fa-angle-up"></i><?php 
                }
            
            } else {
                ?>-<?php 
            }
        
        }
        
        
        if ( $formated ) {
            
            if ( $absolute ) {
                ?> <?php 
                echo  $val ;
                ?></span><?php 
            } else {
                ?> <?php 
                echo  $val_perc ;
                ?>%</span><?php 
            }
        
        } else {
            
            if ( $absolute ) {
                ?> <?php 
                echo  WSKO_Class_Helper::format_number( $val, $decimals ) ;
                ?></span><?php 
            } else {
                ?> <?php 
                echo  WSKO_Class_Helper::format_number( $val_perc, $decimals ) ;
                ?>%</span><?php 
            }
        
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function template_exists( $template )
    {
        return file_exists( WSKO_PLUGIN_PATH . 'admin/templates/' . $template );
    }
    
    public static function render_template( $template, $template_args = array(), $wsko_save_prefix_return = false )
    {
        if ( $wsko_save_prefix_return ) {
            ob_start();
        }
        if ( WSKO_Class_Template::template_exists( $template ) ) {
            include WSKO_PLUGIN_PATH . 'admin/templates/' . $template;
        }
        if ( $wsko_save_prefix_return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_preloader( $args = array(), $return = false )
    {
        $size = ( isset( $args['size'] ) && $args['size'] ? $args['size'] : '' );
        $align = ( isset( $args['align'] ) && $args['align'] ? $args['align'] : '' );
        $plain_js = ( isset( $args['plain_js'] ) ? $args['plain_js'] : false );
        if ( $return ) {
            ob_start();
        }
        
        if ( $plain_js ) {
            ?><div class="wsko-inline-loader"></div><?php 
            global  $wsko_inline_loader_output ;
            
            if ( !$wsko_inline_loader_output ) {
                $wsko_inline_loader_output = true;
                ?>
				<style>.wsko-inline-loader {
					border: 4px solid #f3f3f3;
					border-top: 4px solid #3498db;
					border-radius: 50%;
					width: 20px;
					height: 20px;
					animation: wsko-inline-spin 2s linear infinite;
				}
				@keyframes wsko-inline-spin {
					0% { transform: rotate(0deg); }
					100% { transform: rotate(360deg); }
				}</style><?php 
            }
        
        } else {
            ?><div class="align-<?php 
            echo  $align ;
            ?> display-block">
				<div class="preloader-wrapper <?php 
            echo  $size ;
            ?> active">
					<div class="spinner-layer spinner-blue-only">
					<div class="circle-clipper left">
						<div class="circle"></div>
					</div><div class="gap-patch">
						<div class="circle"></div>
					</div><div class="circle-clipper right">
						<div class="circle"></div>
					</div>
					</div>
				</div>
			</div><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_wsko_preloader( $args = array() )
    {
        $size = ( isset( $args['size'] ) && $args['size'] ? $args['size'] : '' );
        $align = ( isset( $args['align'] ) && $args['align'] ? $args['align'] : '' );
        ob_start();
        ?>
		<div class="wsko-modal-loader">
		  <div class="loader">
			<svg class="circular" viewBox="25 25 50 50">
			  <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle>
			</svg>
		  </div>
		</div> 
		<?php 
        return ob_get_clean();
    }
    
    public static function render_bst_preloader( $args = array() )
    {
        $size = ( isset( $args['size'] ) && $args['size'] ? $args['size'] : '' );
        $align = ( isset( $args['align'] ) && $args['align'] ? $args['align'] : '' );
        ob_start();
        ?>
		  <div class="bst-loader">
			<svg class="bst-circular" viewBox="25 25 50 50">
			  <circle class="bst-path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"></circle>
			</svg>
		  </div>
		<?php 
        return ob_get_clean();
    }
    
    public static function render_infoTooltip( $content, $type, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        switch ( $type ) {
            case 'warning':
                ?>
			<span data-tooltip="<?php 
                echo  $content ;
                ?>">
				<i class="fa fa-exclamation-triangle fa-fw wsko-info"></i>
			</span>
			<?php 
                break;
            case 'info':
            default:
                ?>
			<span data-tooltip="<?php 
                echo  $content ;
                ?>">
				<i class="fa fa-question fa-fw wsko-info"></i>
			</span>
			<?php 
                break;
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_export_link( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        ?>
				<div class="button-dropdown table-export-link" style="display:none">
					<a class="button toggle btn btn-flat btn-sm "><i class="fa fa-download fa-fw wsko-mr5"></i><?php 
        echo  __( 'Export', 'wsko' ) ;
        ?>  <i class="fa fa-angle-down fa-fw"></i></a>
					<ul class="dropdown dropdown-menu">

					</ul>
				</div>	
			<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_issueGroup( $args = array() )
    {
        $title = ( isset( $args['title'] ) && $args['title'] ? $args['title'] : '' );
        $items = ( isset( $args['items'] ) ? $args['items'] : false );
        $col = ( isset( $args['col'] ) && $args['col'] ? $args['col'] : '' );
        $class = ( isset( $args['class'] ) && $args['class'] ? $args['class'] : '' );
        $info = ( isset( $args['info'] ) && $args['info'] ? $args['info'] : '' );
        $parent = ( isset( $args['parent'] ) && $args['parent'] ? $args['parent'] : false );
        $active = ( isset( $args['active'] ) && $args['active'] ? true : false );
        $pdf = ( isset( $args['pdf'] ) && $args['pdf'] ? true : false );
        $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_collapse_panel_' );
        ?>
		<div class="bsu-panel bsu-panel-issue <?php 
        echo  $col ;
        ?> panel panel-default p0">
			<div class="panel-heading">
				<h4 class="panel-title">			
					<?php 
        
        if ( !$pdf ) {
            ?><a data-toggle="collapse" data-parent="#<?php 
            echo  $parent ;
            ?>" href="#<?php 
            echo  $uniqid ;
            ?>"><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span><?php 
        }
        
        ?>
						<?php 
        /* <span class="icon"><i class="fa fa-fw fa-<?=$fa?>"></i></span> */
        ?>
						<span><?php 
        echo  $title ;
        ?> <?php 
        if ( $info ) {
            WSKO_Class_Template::render_infoTooltip( $info, 'info' );
        }
        ?></span>
					<?php 
        if ( !$pdf ) {
            ?></a><?php 
        }
        ?>
				</h4>	
			</div>
			<?php 
        
        if ( !$pdf ) {
            ?><div id="<?php 
            echo  $uniqid ;
            ?>" class="panel-collapse collapse <?php 
            echo  ( $active ? 'in' : '' ) ;
            ?>"><?php 
        }
        
        ?>
				<div class="panel-body">
					<?php 
        
        if ( $items ) {
            usort( $items, function ( $a, $b ) {
                
                if ( $a['class'] == $b['class'] ) {
                    if ( $a['count'] == $b['count'] ) {
                        return 0;
                    }
                    return ( $a['count'] > $b['count'] ? -1 : 1 );
                }
                
                if ( $a['class'] == 'error' ) {
                    return -1;
                }
                if ( $a['class'] == 'warning' ) {
                    return 1;
                }
            } );
            foreach ( $items as $i ) {
                echo  WSKO_Class_Template::render_panel( array(
                    'type'  => 'issue',
                    'title' => $i['title'],
                    'issue' => $i['issue'],
                    'class' => $i['class'],
                ) ) ;
                //echo WSKO_Class_Template::render_notification('success', array('msg' => $i['title']))		;
            }
        } else {
            ?><small class="text-off"><?php 
            echo  __( 'No issues found.', 'wsko' ) ;
            ?></small><?php 
        }
        
        ?>
				</div>
			<?php 
        if ( !$pdf ) {
            ?></div><?php 
        }
        ?>
		</div>
		<?php 
    }
    
    public static function render_panel( $args = array(), $return = false )
    {
        $hasLink = false;
        $type = ( isset( $args['type'] ) && $args['type'] ? $args['type'] : 'default' );
        $title = ( isset( $args['title'] ) ? $args['title'] : '' );
        $col = ( isset( $args['col'] ) && $args['col'] ? $args['col'] : '' );
        $class = ( isset( $args['class'] ) && $args['class'] ? $args['class'] : '' );
        $fa = ( isset( $args['fa'] ) && $args['fa'] ? $args['fa'] : '' );
        $custom = ( isset( $args['custom'] ) ? $args['custom'] : '' );
        $info = ( isset( $args['info'] ) && $args['info'] ? $args['info'] : '' );
        
        if ( $type == 'complex' ) {
            $lazyVar = ( isset( $args['lazyVar'] ) && $args['lazyVar'] ? $args['lazyVar'] : '' );
            $lazyObj = explode( ',', $lazyVar );
        } else {
            $lazyVar = ( isset( $args['lazyVar'] ) && $args['lazyVar'] ? $args['lazyVar'] : '' );
        }
        
        if ( $return ) {
            ob_start();
        }
        switch ( $type ) {
            case 'hero':
                
                if ( isset( $args['tableLink'] ) && is_array( $args['tableLink'] ) && isset( $args['tableLink']['link'] ) && isset( $args['tableLink']['title'] ) ) {
                    $hasLink = true;
                    $tableLink = $args['tableLink']['link'];
                    $tableAnchor = $args['tableLink']['title'];
                }
                
                ?>
				<div class="bsu-panel bsu-panel-hero <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default">
						<span class="pull-right icon"><i class="fa fa-<?php 
                echo  $fa ;
                ?>"></i></span>
						<?php 
                
                if ( $hasLink ) {
                    ?>
							<a class="panel-link pull-right waves-effect btn-flat btn-sm" href="<?php 
                    echo  $tableLink ;
                    ?>"><?php 
                    echo  $tableAnchor ;
                    ?></a>
						<?php 
                }
                
                ?>
						<div class="panel-inner">
							<?php 
                
                if ( $lazyVar ) {
                    ?> 
									<span class="wsko-label"> <?php 
                    WSKO_Class_Template::render_lazy_field( $lazyVar, 'small' );
                    ?> </span>
									<?php 
                } else {
                    ?>
								<span class="wsko-label"><?php 
                    echo  $custom ;
                    ?></span>
							<?php 
                }
                
                ?>	
							
							<p class="text-off"><?php 
                echo  $title ;
                ?> <?php 
                if ( $info ) {
                    WSKO_Class_Template::render_infoTooltip( $info, 'info' );
                }
                ?></p>
							<?php 
                /* <?=$custom?> */
                ?>
						</div>	
					</div>	
				</div>
				<?php 
                break;
            case 'hero-custom':
                ?>
				<div class="bsu-panel bsu-panel-hero <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default">
						<span class="pull-right icon"><i class="fa fa-<?php 
                echo  $fa ;
                ?>"></i></span>
						
						<div class="panel-inner">
							<span class="wsko-label"><?php 
                echo  $custom ;
                ?></span>
							
							<p><?php 
                echo  $title ;
                ?></p>
						</div>	
					</div>	
				</div>
				<?php 
                break;
            case 'chart':
                ?>
				<div class="bsu-panel bsu-panel-chart <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default">
						<p class="panel-heading m0"><?php 
                echo  $title ;
                ?> <?php 
                if ( $info ) {
                    WSKO_Class_Template::render_infoTooltip( $info, 'info' );
                }
                ?></p>
						<?php 
                
                if ( $lazyVar ) {
                    WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                } else {
                    echo  $custom ;
                }
                
                ?>
					</div>
				</div>
				<?php 
                break;
            case 'lazy':
                $panelBody = ( isset( $args['panelBody'] ) && $args['panelBody'] ? true : false );
                
                if ( isset( $args['tableLink'] ) && is_array( $args['tableLink'] ) && isset( $args['tableLink']['link'] ) && isset( $args['tableLink']['title'] ) ) {
                    $hasLink = true;
                    $tableLink = $args['tableLink']['link'];
                    $tableAnchor = $args['tableLink']['title'];
                }
                
                ?>
				<div class="bsu-panel <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default <?php 
                echo  $class ;
                ?>">
						<?php 
                
                if ( $class != 'panel-transparent' ) {
                    ?>
							<?php 
                    
                    if ( $hasLink ) {
                        ?>
								<a class="panel-link pull-right waves-effect btn-flat btn-sm" href="<?php 
                        echo  $tableLink ;
                        ?>"><?php 
                        echo  $tableAnchor ;
                        ?></a>
							<?php 
                    }
                    
                    ?>	
							<p class="panel-heading m0"><?php 
                    echo  $title ;
                    ?> <?php 
                    if ( $info ) {
                        WSKO_Class_Template::render_infoTooltip( $info, 'info' );
                    }
                    ?></p>
							<?php 
                    echo  $custom ;
                    ?>
						<?php 
                }
                
                ?>
						<div class="<?php 
                echo  ( $panelBody ? 'panel-body' : '' ) ;
                ?>">
							<?php 
                WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                ?>
						</div>	
					</div>
				</div>
				<?php 
                break;
            case 'table-simple':
            case 'table':
                
                if ( isset( $args['tableLink'] ) && is_array( $args['tableLink'] ) && isset( $args['tableLink']['link'] ) && isset( $args['tableLink']['title'] ) ) {
                    $hasLink = true;
                    $tableLink = $args['tableLink']['link'];
                    $tableAnchor = $args['tableLink']['title'];
                }
                
                ?><div class="bsu-panel bsu-panel-table <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default">
						<?php 
                
                if ( $hasLink ) {
                    ?>
							<a class="panel-link pull-right waves-effect btn-flat btn-sm" href="<?php 
                    echo  $tableLink ;
                    ?>"><?php 
                    echo  $tableAnchor ;
                    ?></a>
						<?php 
                }
                
                ?>	
						<p class="panel-heading m0"><?php 
                echo  $title ;
                ?></p>
						<?php 
                echo  $custom ;
                ?>	
						<div class="<?php 
                echo  ( $type == 'table-simple' ? 'wsko_table_simple' : '' ) ;
                ?>">
						<?php 
                WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                ?>
						</div>	
					</div>
				</div>
				<?php 
                break;
            case 'complex':
                
                if ( isset( $args['tableLink'] ) && is_array( $args['tableLink'] ) && isset( $args['tableLink']['link'] ) && isset( $args['tableLink']['title'] ) ) {
                    $hasLink = true;
                    $tableLink = $args['tableLink']['link'];
                    $tableAnchor = $args['tableLink']['title'];
                }
                
                ?><div class="bsu-panel bsu-panel-table <?php 
                echo  $col ;
                ?>">
					<div class="panel panel-default">
						<?php 
                
                if ( $hasLink ) {
                    ?>
							<a class="panel-link pull-right waves-effect btn-flat btn-sm" href="<?php 
                    echo  $tableLink ;
                    ?>"><?php 
                    echo  $tableAnchor ;
                    ?></a>
						<?php 
                }
                
                ?>	
						<p class="panel-heading m0"><?php 
                echo  $title ;
                ?></p>
						
						<?php 
                foreach ( $lazyObj as $lazyVar ) {
                    ?>
							<div class="bsu-panel bsu-complex">
								<?php 
                    WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                    ?>
							</div>
						<?php 
                }
                ?>
											
					</div>
				</div>
				<?php 
                break;
            case 'issue':
                $issue = ( isset( $args['issue'] ) ? $args['issue'] : false );
                ?>
					<div class="bs-callout wsko-notice">
							<?php 
                /* <span class="icon"><i class="fa fa-fw fa-<?=$fa?>"></i></span> */
                ?>
							<span><?php 
                echo  $title ;
                ?> <?php 
                if ( $info ) {
                    WSKO_Class_Template::render_infoTooltip( $info, 'info' );
                }
                ?></span>
							<span class="wsko-issue-link badge wsko-badge badge-default" style="float:none;"><?php 
                echo  $issue ;
                ?></span>
							<span class="wsko-issue-link badge wsko-badge badge-<?php 
                echo  $class ;
                ?>" style="float:none; margin-left:5px;"><?php 
                echo  ( $class == 'error' ? 'Prio 1' : 'Prio 2' ) ;
                ?></span>
					</div>												
					<?php 
                break;
            case 'collapse':
                $parent = ( isset( $args['parent'] ) && $args['parent'] ? $args['parent'] : false );
                $active = ( isset( $args['active'] ) && $args['active'] ? true : false );
                $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_collapse_panel_' );
                ?>
				<div class="bsu-panel panel panel-default <?php 
                echo  $class ;
                ?>">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" <?php 
                echo  ( $parent ? 'data-parent="#' . $parent . '"' : '' ) ;
                ?> href="#<?php 
                echo  $uniqid ;
                ?>"><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span> <?php 
                echo  $title ;
                ?></a>
						</h4>
					</div>
					<div id="<?php 
                echo  $uniqid ;
                ?>" class="panel-collapse collapse <?php 
                echo  ( $active ? 'in' : '' ) ;
                ?>">
						<div class="panel-body">
							<?php 
                
                if ( $lazyVar ) {
                    WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                } else {
                    if ( $custom ) {
                        echo  $custom ;
                    }
                }
                
                ?>
						</div>
					</div>
				</div>
				<?php 
                break;
            case 'collapse_single':
                $parent = ( isset( $args['parent'] ) && $args['parent'] ? $args['parent'] : false );
                $active = ( isset( $args['active'] ) && $args['active'] ? true : false );
                $uniqid = WSKO_Class_Helper::get_unique_id( 'wsko_collapse_panel_' );
                ?>
				<div class="bsu-panel <?php 
                echo  $class ;
                ?>">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse" <?php 
                echo  ( $parent ? 'data-parent="#' . $parent . '"' : '' ) ;
                ?> href="#<?php 
                echo  $uniqid ;
                ?>"><span class="pull-right"><i class="fa fa-angle-down font-unimportant"></i></span> <?php 
                echo  $title ;
                ?></a>
							</h4>
						</div>
						<div id="<?php 
                echo  $uniqid ;
                ?>" class="panel-collapse collapse <?php 
                echo  ( $active ? 'in' : '' ) ;
                ?>">
							<div class="panel-body">
								<?php 
                
                if ( $lazyVar ) {
                    WSKO_Class_Template::render_lazy_field( $lazyVar, 'small', 'center' );
                } else {
                    if ( $custom ) {
                        echo  $custom ;
                    }
                }
                
                ?>
							</div>
						</div>
					</div>
				</div>
				<?php 
                break;
            case 'progress':
                $items = ( isset( $args['items'] ) ? $args['items'] : false );
                $progressFullWidth = ( isset( $args['progress_full_width'] ) ? true : false );
                ?>
				<div class="bsu-panel bsu-panel-progress <?php 
                echo  $col ;
                ?> <?php 
                echo  $class ;
                ?>">
					<div class="panel panel-default">
						<?php 
                
                if ( $hasLink ) {
                    ?>
							<a class="panel-link pull-right waves-effect btn-flat btn-sm" href="<?php 
                    echo  $tableLink ;
                    ?>"><?php 
                    echo  $tableAnchor ;
                    ?></a>
						<?php 
                }
                
                ?>	
						<p class="panel-heading m0"><?php 
                echo  $title ;
                ?></p>
						<div class="panel-body">
							<?php 
                echo  $custom ;
                ?>
							<?php 
                if ( $items ) {
                    foreach ( $items as $i ) {
                        
                        if ( isset( $i['stacked'] ) && $i['stacked'] ) {
                            WSKO_Class_Template::render_progress_stacked(
                                $i['title'],
                                $i['value'],
                                $i['max'],
                                array(
                                'data'                => ( isset( $i['data'] ) ? $i['data'] : false ),
                                'tooltip'             => ( isset( $i['tooltip'] ) ? $i['tooltip'] : false ),
                                'class'               => ( isset( $i['class'] ) ? $i['class'] : false ),
                                'progress_full_width' => ( isset( $progressFullWidth ) ? $progressFullWidth : false ),
                                'fill'                => ( isset( $i['fill'] ) ? $i['fill'] : true ),
                            )
                            );
                        } else {
                            WSKO_Class_Template::render_progress(
                                $i['title'],
                                $i['value'],
                                $i['max'],
                                array(
                                'data'           => ( isset( $i['data'] ) ? $i['data'] : false ),
                                'tooltip'        => ( isset( $i['tooltip'] ) ? $i['tooltip'] : false ),
                                'class'          => ( isset( $i['class'] ) ? $i['class'] : false ),
                                'progress_class' => $i['progress_class'],
                            )
                            );
                        }
                    
                    }
                }
                ?>
						</div>
					</div>	
				</div>
				<?php 
                break;
            case 'custom':
                ?>
					<div class="bsu-panel bsu-panel-chart <?php 
                echo  $col ;
                ?>">
						<div class="panel panel-default  <?php 
                echo  $class ;
                ?>">
							<?php 
                
                if ( $class != 'panel-transparent' ) {
                    ?>
								<p class="panel-heading m0"><?php 
                    echo  $title ;
                    ?> <?php 
                    if ( $info ) {
                        WSKO_Class_Template::render_infoTooltip( $info, 'info' );
                    }
                    ?></p>
							<?php 
                }
                
                ?>
							<?php 
                echo  $custom ;
                ?>
								
						</div>
					</div>
					<?php 
                break;
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_form( $args = array(), $return = false )
    {
        $type = ( isset( $args['type'] ) && $args['type'] ? $args['type'] : 'default' );
        //input, textarea, editor, select, checkbox, radio
        $id = ( isset( $args['id'] ) && $args['id'] ? $args['id'] : '' );
        $name = ( isset( $args['name'] ) && $args['name'] ? $args['name'] : '' );
        $title = ( isset( $args['title'] ) && $args['title'] ? $args['title'] : '' );
        $subtitle = ( isset( $args['subtitle'] ) && $args['subtitle'] ? $args['subtitle'] : '' );
        $value = ( isset( $args['value'] ) && $args['value'] ? $args['value'] : '' );
        $placeholder = ( isset( $args['placeholder'] ) && $args['placeholder'] ? $args['placeholder'] : '' );
        $rows = ( isset( $args['rows'] ) && $args['rows'] ? $args['rows'] : '' );
        $class = ( isset( $args['class'] ) && $args['class'] ? $args['class'] : '' );
        $nonce = ( isset( $args['nonce'] ) && $args['nonce'] ? $args['nonce'] : '' );
        $dataPost = ( isset( $args['data-post'] ) && $args['data-post'] ? $args['data-post'] : '' );
        $progressBar = ( isset( $args['progressBar'] ) && $args['progressBar'] ? true : false );
        $disabled = ( isset( $args['disabled'] ) && $args['disabled'] ? 'disabled' : '' );
        $fullWidth = ( isset( $args['fullWidth'] ) && $args['fullWidth'] ? $args['fullWidth'] : false );
        //$default = isset($args['default']) && $args['default'] ? $args['default'] : '';
        //$col = isset($args['col']) && $args['col'] ? $args['col'] : '';
        //$fa = isset($args['fa']) && $args['fa'] ? $args['fa'] : '';
        
        if ( $progressBar ) {
            $progressID = ( isset( $args['progressID'] ) && $args['progressID'] ? $args['progressID'] : '' );
            $progressType = ( isset( $args['progressType'] ) && $args['progressType'] ? $args['progressType'] : '' );
        }
        
        if ( $return ) {
            ob_start();
        }
        ?>
			<div class="wsko-row row form-group">
				<div class="<?php 
        echo  ( $fullWidth ? 'wsko-col-sm-12 col-sm-12' : 'wsko-col-sm-3 col-sm-3' ) ;
        ?> wsko-col-xs-12 col-xs-12">
					<p><?php 
        echo  $title ;
        ?></p>
					<small class="font-unimportant"><?php 
        echo  $subtitle ;
        ?></small>
				</div>
				<div class="<?php 
        echo  ( $fullWidth ? 'wsko-col-sm-12 col-sm-12' : 'wsko-col-sm-9 col-sm-9' ) ;
        ?> wsko-col-xs-12 col-xs-12">
					<?php 
        switch ( $type ) {
            case 'input':
                ?>
						<input class="form-control wsko-form-control <?php 
                echo  $class ;
                ?>" <?php 
                echo  $disabled ;
                ?> id="<?php 
                echo  $id ;
                echo  ( $progressBar && $progressID ? '-' . $progressID : '' ) ;
                ?>" name="<?php 
                echo  $name ;
                ?>" placeholder="<?php 
                echo  $placeholder ;
                ?>" value="<?php 
                echo  $value ;
                ?>">
						<?php 
                break;
            case 'textarea':
                ?>
						<textarea class="form-control wsko-form-control <?php 
                echo  $class ;
                ?>" <?php 
                echo  $disabled ;
                ?> id="<?php 
                echo  $id ;
                echo  ( $progressBar && $progressID ? '-' . $progressID : '' ) ;
                ?>" name="<?php 
                echo  $name ;
                ?>" placeholder="<?php 
                echo  $placeholder ;
                ?>" rows="<?php 
                echo  $rows ;
                ?>"><?php 
                echo  $value ;
                ?></textarea> 
						<?php 
                break;
            case 'previewable_textarea':
                $highlights = ( isset( $args['highlights'] ) && $args['highlights'] ? $args['highlights'] : '' );
                ?>
						<div class="wsko-previewable-textarea" data-highlights="<?php 
                echo  ( $highlights ? htmlspecialchars( json_encode( $highlights ) ) : '' ) ;
                ?>">
							<textarea <?php 
                echo  $disabled ;
                ?> id="<?php 
                echo  $id ;
                ?>" name="<?php 
                echo  $name ;
                ?>" placeholder="<?php 
                echo  $placeholder ;
                ?>" rows="<?php 
                echo  $rows ;
                ?>"><?php 
                echo  $value ;
                ?></textarea> 
							<div class="wsko-textarea-preview">
							</div>
						</div>
						<?php 
                break;
            case 'editor':
                $id = ( $id ? $id : WSKO_Class_Helper::get_unique_id( 'wsko_tmce_editor_' ) );
                ?>
						<textarea class="form-control wsko-form-control <?php 
                echo  $class ;
                ?>" <?php 
                echo  $disabled ;
                ?> id="<?php 
                echo  $id ;
                echo  ( $progressBar && $progressID ? '-' . $progressID : '' ) ;
                ?>" name="<?php 
                echo  $name ;
                ?>" placeholder="<?php 
                echo  $placeholder ;
                ?>" rows="<?php 
                echo  $rows ;
                ?>"><?php 
                echo  $value ;
                ?></textarea> 
						<?php 
                //wp_editor( $value, $id, array('editor_class' => $class));
                break;
            case 'url_media':
                ?>
						<div class="wsko-co-media-picker-container">
							<input class="form-control wsko-form-control wsko-co-media-picker-target <?php 
                echo  $class ;
                ?>" <?php 
                echo  $disabled ;
                ?> id="<?php 
                echo  $id ;
                echo  ( $progressBar && $progressID ? '-' . $progressID : '' ) ;
                ?>" name="<?php 
                echo  $name ;
                ?>" type="url" placeholder="<?php 
                echo  $placeholder ;
                ?>" value="<?php 
                echo  $value ;
                ?>">
							<a class="wsko-co-media-picker" href="#"><i class="fa fa-camera"></i></a>
						</div>
						<?php 
                break;
            case 'select':
                ?>
						
						<?php 
                break;
            case 'checkbox':
                ?>
						
						<?php 
                break;
            case 'radio':
                ?>
						
						<?php 
                break;
            case 'multi':
                ?>
						
						<?php 
                break;
            case 'submit':
                ?>
						<button class="button <?php 
                echo  $class ;
                ?>" id="<?php 
                echo  $id ;
                ?>" data-post="<?php 
                echo  $dataPost ;
                ?>" data-nonce="<?php 
                echo  wp_create_nonce( $nonce ) ;
                ?>"><?php 
                echo  __( 'Save', 'wsko' ) ;
                ?></button>
						<?php 
                break;
        }
        /* Progress Bar */
        
        if ( $progressBar ) {
            $dataMax = '';
            switch ( $progressType ) {
                case 'google_title':
                    $dataMax = WSKO_ONPAGE_TITLE_MAX;
                    $dataMin = WSKO_ONPAGE_TITLE_MIN;
                    break;
                case 'google_desc':
                    $dataMax = WSKO_ONPAGE_DESC_MAX;
                    $dataMin = WSKO_ONPAGE_DESC_MIN;
                    break;
                case 'fb_title':
                    $dataMax = WSKO_ONPAGE_FB_TITLE_MAX;
                    break;
                case 'fb_desc':
                    $dataMax = WSKO_ONPAGE_FB_DESC_MAX;
                    break;
                case 'tw_title':
                    $dataMax = WSKO_ONPAGE_TW_TITLE_MAX;
                    break;
                case 'tw_title':
                    $dataMax = WSKO_ONPAGE_TW_DESC_MAX;
                    break;
            }
            ?>
						<div style="height: 8px;">
							<progress id="wsko_progress_<?php 
            echo  $progressID ;
            ?>" class="wsko-progress" value="" data-min="<?php 
            echo  $dataMin ;
            ?>" max="<?php 
            echo  $dataMax ;
            ?>"></progress>
						</div>
						<script type="text/javascript">
							jQuery(document).ready(function($)
							{	
								$('#<?php 
            echo  $id ;
            ?>-<?php 
            echo  $progressID ;
            ?>').on('keyup', function() {
									var $val = $(this).val().length;
									var $progress = $('#wsko_progress_<?php 
            echo  $progressID ;
            ?>');
									var $max = $progress.attr('max');
									var $min = $progress.attr('data-min');
									
									$progress.val( $val );
									
									if ($val < $min) {
										$progress.removeClass('wsko-success wsko-error').addClass('wsko-warning');
									}
									if ($val >= $min) {
										$progress.removeClass('wsko-warning wsko-error').addClass('wsko-success');
									}
									if ($val > $max) {
										$progress.removeClass('wsko-success wsko-warning').addClass('wsko-error');
									}
								}).trigger('keyup') ;	
							});	
						</script>
					<?php 
        }
        
        ?>
				</div>
			</div>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_ajax_button(
        $title,
        $action,
        $post_params = array(),
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $alert = ( isset( $args['alert'] ) ? $args['alert'] : false );
        $class = ( isset( $args['class'] ) ? $args['class'] : '' );
        $no_button = ( isset( $args['no_button'] ) && $args['no_button'] ? true : false );
        $no_reload = ( isset( $args['no_reload'] ) && $args['no_reload'] ? true : false );
        $table_reload = ( isset( $args['table_reload'] ) && $args['table_reload'] ? true : false );
        $reload_real = ( isset( $args['reload_real'] ) && $args['reload_real'] ? true : false );
        $remove = ( isset( $args['remove'] ) ? $args['remove'] : false );
        $remove_parent = ( isset( $args['remove_parent'] ) ? $args['remove_parent'] : false );
        $ajax_reload = ( isset( $args['ajax_reload'] ) ? $args['ajax_reload'] : '' );
        $sources = ( isset( $args['sources'] ) ? $args['sources'] : '' );
        ?><a class="<?php 
        echo  ( $no_button ? '' : 'button' ) ;
        ?> wsko-ajax-button <?php 
        echo  $class ;
        ?>" href="#" data-action="<?php 
        echo  'wsko_' . $action ;
        ?>" data-nonce="<?php 
        echo  wp_create_nonce( 'wsko_' . $action ) ;
        ?>"<?php 
        foreach ( $post_params as $key => $param ) {
            ?> data-wsko-post-<?php 
            echo  $key ;
            ?>="<?php 
            echo  $param ;
            ?>"<?php 
        }
        ?> <?php 
        echo  ( $alert ? 'data-alert="' . $alert . '"' : '' ) ;
        ?> <?php 
        echo  ( $no_reload ? 'data-no-reload="true"' : '' ) ;
        ?> <?php 
        echo  ( $reload_real ? 'data-reload-real="true"' : '' ) ;
        ?> <?php 
        echo  ( $table_reload ? 'data-table-reload="true"' : '' ) ;
        ?> <?php 
        echo  ( $remove ? 'data-remove="' . $remove . '"' : '' ) ;
        ?> <?php 
        echo  ( $remove_parent ? 'data-remove-parent="' . $remove_parent . '"' : '' ) ;
        ?> <?php 
        echo  ( $ajax_reload ? 'data-ajax-reload="' . $ajax_reload . '"' : '' ) ;
        ?> <?php 
        echo  ( $sources ? 'data-sources="' . $sources . '"' : '' ) ;
        ?>><i class="wsko-load-icon fa fa-spinner fa-pulse" style="display:none"></i> <?php 
        echo  $title ;
        ?></a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_revoke_api_button( $api, $args = array(), $return = false )
    {
        $title = ( isset( $args['title'] ) && $args['title'] ? $args['title'] : __( 'Revoke Access/Logout', 'wsko' ) );
        if ( $return ) {
            ob_start();
        }
        WSKO_Class_Template::render_ajax_button(
            $title,
            'revoke_api_access',
            array(
            'api' => $api,
        ),
            array(
            'ajax_reload' => ( isset( $args['ajax_reload'] ) ? $args['ajax_reload'] : '' ),
            'alert'       => __( "Your credentials will be deleted. Are you sure?", 'wsko' ),
            'class'       => 'wsko-button-danger',
        ),
            false
        );
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_recache_api_button( $api, $args = array(), $return = false )
    {
        $title = ( isset( $args['title'] ) && $args['title'] ? $args['title'] : __( 'Update Cache Manually', 'wsko' ) );
        if ( $return ) {
            ob_start();
        }
        WSKO_Class_Template::render_ajax_button(
            $title,
            'update_api_cache',
            array(
            'api' => $api,
        ),
            array(
            'ajax_reload' => ( isset( $args['ajax_reload'] ) ? $args['ajax_reload'] : '' ),
            __( "Be advised: A CronJob is allready waiting to update your cache, just wait a few minutes. Please only make use of this button, when you are sure that your CronJob has failed and you don't want to wait another hour.", 'wsko' ),
        ),
            false
        );
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_delete_api_cache_button( $api, $args = array(), $return = false )
    {
        $title = ( isset( $args['title'] ) && $args['title'] ? $args['title'] : __( 'Clear Cache', 'wsko' ) );
        if ( $return ) {
            ob_start();
        }
        WSKO_Class_Template::render_ajax_button(
            $title,
            'delete_api_cache',
            array(
            'api' => $api,
        ),
            array(
            'ajax_reload' => ( isset( $args['ajax_reload'] ) ? $args['ajax_reload'] : '' ),
            'alert'       => __( "Your API cache will be completely deleted. Are you sure?", 'wsko' ),
        ),
            false
        );
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_url_resolve_field( $url, $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        ?>
		<span><p class="wsko-ajax-url-field" data-url="<?php 
        echo  $url ;
        ?>"><i class="fa fa-spinner fa-pulse"></i></p><a class="font-unimportant" href="<?php 
        echo  $url ;
        ?>"><?php 
        echo  $url ;
        ?></a></span>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_url_post_field( $post_id, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $with_optimizer = ( isset( $params['with_co'] ) && $params['with_co'] ? true : false );
        $open_tab = ( isset( $params['open_tab'] ) ? $params['open_tab'] : false );
        $multi_link = ( isset( $params['multi_link'] ) ? $params['multi_link'] : false );
        $wrapped = ( isset( $params['wrapped'] ) ? $params['wrapped'] : false );
        $url = get_permalink( $post_id );
        ?><div class="" style="<?php 
        echo  ( $wrapped ? '' : 'width:100%;height:100%;' ) ;
        ?>">
			<?php 
        
        if ( $with_optimizer ) {
            ?><p style="float:right"><?php 
            WSKO_Class_Template::render_content_optimizer_link( $post_id, array(
                'open_tab' => $open_tab,
            ), false );
            ?></p><?php 
        }
        
        WSKO_Class_Template::render_url_index_badges( $url );
        ?>
			<a href="#" class="wsko-content-optimizer-link wsko-co-no-style <?php 
        echo  ( $multi_link ? 'wsko-multi-link' : '' ) ;
        ?> wsko-focus-table-row-element" <?php 
        echo  ( $open_tab ? 'data-opentab="' . $open_tab . '"' : '' ) ;
        ?> data-post="<?php 
        echo  $post_id ;
        ?>"><?php 
        echo  esc_html( get_the_title( $post_id ) ) ;
        ?></a><br/>
			<p class="font-unimportant"><a href="<?php 
        echo  $url ;
        ?>" class="dark wsko-focus-table-row-element" target="_blank"><?php 
        echo  WSKO_Class_Helper::get_relative_url( $url ) ;
        ?> <i class="fa fa-external-link"></i></a></p>
		</div><?php 
        //WSKO_Class_Template::render_url_post_field_s($url, $params, false);
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_url_post_field_s( $url, $params = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( WSKO_Class_Core::is_demo() ) {
            $url = WSKO_Class_Helper::map_url( $url );
            //'https://example.com/url/';
        }
        
        $with_optimizer = ( isset( $params['with_co'] ) && $params['with_co'] ? true : false );
        $open_tab = ( isset( $params['open_tab'] ) ? $params['open_tab'] : false );
        $multi_link = ( isset( $params['multi_link'] ) ? $params['multi_link'] : false );
        $wrapped = ( isset( $params['wrapped'] ) ? $params['wrapped'] : false );
        $title_d = WSKO_Class_Helper::url_get_title( $url );
        
        if ( is_object( $title_d ) && $title_d && $title_d->type == "post" ) {
            ?><div class="" style="<?php 
            echo  ( $wrapped ? '' : 'width:100%;height:100%;' ) ;
            ?>">
				<?php 
            
            if ( $with_optimizer ) {
                ?><p style="float:right"><?php 
                WSKO_Class_Template::render_content_optimizer_link( $title_d->post_id, array(
                    'open_tab' => $open_tab,
                ), false );
                ?></p><?php 
            }
            
            WSKO_Class_Template::render_url_index_badges( $url );
            ?>
				<a href="#" class="wsko-content-optimizer-link wsko-co-no-style <?php 
            echo  ( $multi_link ? 'wsko-multi-link' : '' ) ;
            ?> wsko-focus-table-row-element" <?php 
            echo  ( $open_tab ? 'data-opentab="' . $open_tab . '"' : '' ) ;
            ?> data-post="<?php 
            echo  $title_d->post_id ;
            ?>"><?php 
            echo  ( $title_d->title_empty ? $title_d->title : esc_html( $title_d->title ) ) ;
            ?></a><br/>
				<p class="font-unimportant"><a href="<?php 
            echo  $url ;
            ?>" class="dark wsko-focus-table-row-element" target="_blank"><?php 
            echo  WSKO_Class_Helper::get_relative_url( $url ) ;
            ?> <i class="fa fa-external-link"></i></a></p>
			</div><?php 
        } else {
            ?><span><?php 
            echo  $title_d->title ;
            ?><br/><p class="font-unimportant"><a href="<?php 
            echo  $url ;
            ?>" class="dark" target="_blank"><?php 
            echo  WSKO_Class_Helper::get_relative_url( $url ) ;
            ?> <i class="fa fa-external-link"></i></a></p></span><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_url_index_badges( $url, $array = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_page_link(
        $controller,
        $subpage,
        $text,
        $params = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $button = ( isset( $params['button'] ) && $params['button'] ? true : false );
        $class = ( isset( $params['class'] ) ? $params['class'] : '' );
        $post_params = ( isset( $params['post'] ) ? $params['post'] : array() );
        $tab = false;
        
        if ( strpos( $subpage, '#' ) !== false ) {
            $parts = WSKO_Class_Helper::safe_explode( '#', $subpage );
            $subpage = $parts[0];
            $tab = $parts[1];
        }
        
        ?><a href="<?php 
        echo  $controller::get_link( $subpage ) ;
        ?>" class="<?php 
        echo  ( $button ? 'button' : '' ) ;
        ?> <?php 
        echo  ( !$subpage ? 'wsko-link' : '' ) ;
        ?> <?php 
        echo  $class ;
        ?> wsko-load-lazy-page" data-controller="<?php 
        echo  $controller::get_link( false, true ) ;
        ?>" <?php 
        echo  ( $subpage ? 'data-subpage="' . $controller::get_link( $subpage, true, false ) . '"' : '' ) ;
        ?> <?php 
        echo  ( $tab ? 'data-subtab="' . $tab . '"' : '' ) ;
        foreach ( $post_params as $key => $param ) {
            ?> data-wsko-post-<?php 
            echo  $key ;
            ?>="<?php 
            echo  $param ;
            ?>"<?php 
        }
        ?>><?php 
        echo  $text ;
        ?></a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_lazy_field(
        $var,
        $size,
        $align = false,
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        ?><div class="wsko-lazy-field" data-wsko-lazy-var="<?php 
        echo  $var ;
        ?>">
			<?php 
        WSKO_Class_Template::render_preloader( array(
            'size' => $size,
        ) + (( $align ? array(
            'align' => $align,
        ) : array() )) );
        ?> 
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_feedback( $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        ?>
			<div class="feedback-panel hidden-xs">
				<div class="row">
					<div class="col-sm-7">
						<p style="line-height: 24px;"><?php 
        echo  __( 'How do you like BAVOKO SEO Tools?', 'wsko' ) ;
        ?></p>
					</div>
					<div class="col-sm-5 align-right">
						<a class="wsko-give-feedback btn-flat btn-sm"><i class="fa fa-thumbs-up fa-fw"></i> <?php 
        echo  __( 'Like', 'wsko' ) ;
        ?></a>
						<a class="wsko-give-feedback btn-flat btn-sm"><i class="fa fa-thumbs-down fa-fw"></i> <?php 
        echo  __( 'Dislike', 'wsko' ) ;
        ?></a>
					</div>	
				</div>		
			</div>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_no_data_view( $args, $return = false )
    {
        $elem = ( isset( $args['elem'] ) ? $args['elem'] : null );
        $fa = ( isset( $args['fa'] ) ? $args['fa'] : 'cogs' );
        $notif = ( isset( $args['notif'] ) ? $args['notif'] : __( 'Please follow the steps below', 'wsko' ) );
        $collapsed = ( isset( $args['collapsed'] ) ? $args['collapsed'] : false );
        if ( $return ) {
            ob_start();
        }
        ?>
			<div class="wsko-no-data-wrapper">
				<div class="wsko-no-data-inner">
					<i class="fa fa-<?php 
        echo  $fa ;
        ?> fa-no-data-icon wsko-mb15"></i>
					<p><?php 
        echo  $notif ;
        ?></p>
				</div>
				<?php 
        WSKO_Class_Template::render_template( 'setup/template-intool-setup-widget.php', array(
            'elem'      => $elem,
            'collapsed' => $collapsed,
        ) );
        ?>
			</div>
		<?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_popup_link(
        $link,
        $title,
        $content,
        $args = array(),
        $return = false
    )
    {
        $ajax = ( isset( $args['ajax'] ) ? $args['ajax'] : false );
        $class = ( isset( $args['class'] ) ? $args['class'] : false );
        if ( $return ) {
            ob_start();
        }
        
        if ( !$ajax ) {
            ?><a href="#" class="wsko-open-modal" data-modal-title="<?php 
            echo  esc_html( $title ) ;
            ?>" data-modal-class="<?php 
            echo  $class ;
            ?>" data-modal-content="<?php 
            echo  esc_html( $content ) ;
            ?>"><?php 
            echo  $link ;
            ?></a><?php 
        } else {
            ?><a href="#" class="wsko-open-modal" data-modal-title="<?php 
            echo  esc_html( $title ) ;
            ?>" data-modal-class="<?php 
            echo  $class ;
            ?>" data-modal-ajax="wsko_<?php 
            echo  $ajax['action'] ;
            ?>" data-modal-nonce="<?php 
            echo  wp_create_nonce( 'wsko_' . $ajax['action'] ) ;
            ?>" data-modal-data="<?php 
            echo  esc_html( json_encode( $ajax['data'] ) ) ;
            ?>"><?php 
            echo  $link ;
            ?></a><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_diff_bar(
        $value,
        $ref_value,
        $args = array(),
        $return = false
    )
    {
        $ajax = ( isset( $args['ajax'] ) ? $args['ajax'] : false );
        if ( $return ) {
            ob_start();
        }
        ?><div style="overflow:hidden">
			<div style="position:relative;width:100%;height:0px;margin:2px;border: 1px solid #ccc;margin-top:10px">
				<?php 
        if ( $value > 0 ) {
            
            if ( $value < 100 ) {
                ?>
					<div style="position:absolute;height:10px;right:50%;bottom:50%;left:<?php 
                echo  (100 - $value) / 2 ;
                ?>%;background-color:#4ea04e;">
					</div>
				<?php 
            } else {
                ?>
					<div style="position:absolute;height:10px;left:50%;bottom:50%;right:<?php 
                echo  (200 - $value) / 2 ;
                ?>%;background-color:#d9534f;">
					</div>
				<?php 
            }
        
        }
        ?>
					<div style="position:absolute;top:40%;left:49%;bottom:40%;right:49%;background-color:black;">
					</div>
			</div>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_http_code_cell( $code, $args = array(), $return = false )
    {
        $ajax = ( isset( $args['ajax'] ) ? $args['ajax'] : false );
        if ( $return ) {
            ob_start();
        }
        $a = intval( $code );
        
        if ( $code === null || $code === false ) {
            ?><small class="text-off"><?php 
            echo  __( 'uncrawled', 'wsko' ) ;
            ?> <?php 
            WSKO_Class_Template::render_infoTooltip( __( 'This page was not found by the crawler or was left out due to your license-bound limits.', 'wsko' ), '' );
            ?></small>
			<div class="wsko-table-style-trigger" data-attr="border-left" data-val="solid 3px rgba(218, 218, 218)"></div><?php 
        } else {
            ?><a href="#" class="wsko-http-code" data-toggle="tooltip" data-title="<?php 
            echo  ( $a == 200 ? __( 'Reachable', 'wsko' ) : __( 'Unreachable', 'wsko' ) ) ;
            ?>">
				<?php 
            echo  $a ;
            
            if ( $a < 200 || $a >= 400 ) {
                ?><div class="wsko-table-style-trigger" data-attr="border-left" data-val="solid 3px rgba(217, 83, 79)"></div><?php 
            } else {
                
                if ( $a > 200 ) {
                    ?><div class="wsko-table-style-trigger" data-attr="border-left" data-val="solid 3px rgb(240, 173, 78)"></div><?php 
                } else {
                    ?><div class="wsko-table-style-trigger" data-attr="border-left" data-val="solid 3px rgb(92, 184, 92)"></div><?php 
                }
            
            }
            
            ?></a><?php 
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_http_code_badge( $code, $args = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        
        if ( $code == 'loop' ) {
            $code_color = 'default';
        } else {
            
            if ( $code == 200 ) {
                $code_color = 'success';
            } else {
                
                if ( $code > 200 && $code < 400 ) {
                    $code_color = 'warning';
                } else {
                    $code_color = 'error';
                }
            
            }
        
        }
        
        ?><span class="wsko-badge badge-<?php 
        echo  $code_color ;
        ?>" style="float:unset;margin-left:unset;padding:3px 5px;" data-tooltip="<?php 
        echo  __( 'HTTP Status Code', 'wsko' ) ;
        ?>"><?php 
        echo  ( $code ? $code : 'unknown' ) ;
        ?></span><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function get_status_code_color( $code, $args = array(), $return = false )
    {
        
        if ( $code == 'loop' ) {
            $code_color = 'error';
        } else {
            
            if ( $code == 200 ) {
                $code_color = 'success';
            } else {
                
                if ( $code > 200 && $code < 400 ) {
                    $code_color = 'warning';
                } else {
                    $code_color = 'error';
                }
            
            }
        
        }
        
        return $code_color;
    }
    
    public static function render_badge( $active, $args = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        $class = ( isset( $args['class'] ) ? $args['class'] : false );
        $style = ( isset( $args['style'] ) ? $args['style'] : false );
        $title = ( isset( $args['title'] ) ? $args['title'] : false );
        
        if ( $active ) {
            echo  '<span class="wsko-badge badge badge-success ' . $class . '" style="' . $style . '">' . (( $title ? $title : __( 'Active', 'wsko' ) )) . '</span>' ;
        } else {
            echo  '<span class="wsko-badge badge badge-error ' . $class . '" style="' . $style . '">' . (( $title ? $title : __( 'Not active', 'wsko' ) )) . '</span>' ;
        }
        
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_widget_structure( $type, $args = array(), $return = false )
    {
        if ( $return ) {
            ob_start();
        }
        switch ( $type ) {
            case 'breadcrumbs':
                echo  htmlentities( '<div class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb', '.', array(
                    'color' => 'red',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( __( '--Prefix--', 'wsko' ) ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<a class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link', '.bst-breadcrumb .', array(
                    'color' => 'green',
                ) );
                echo  ' ' ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link-main', '.bst-breadcrumb .', array(
                    'color' => 'blue',
                ) );
                echo  htmlentities( '">' . __( '--Home Breadcrumb--', 'wsko' ) . '</a>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<p class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-separator', '.bst-breadcrumb .', array(
                    'color' => 'limegreen',
                ) );
                echo  htmlentities( '">' . __( '--Separator--', 'wsko' ) . '</p>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<a class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link', '.bst-breadcrumb .', array(
                    'color' => 'green',
                ) );
                echo  ' ' ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link-archive', '.bst-breadcrumb .', array(
                    'color' => 'orange',
                ) );
                echo  htmlentities( '">' . __( '--Archive Breadcrumb--', 'wsko' ) . '</a>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<p class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-separator', '.bst-breadcrumb .', array(
                    'color' => 'limegreen',
                ) );
                echo  htmlentities( '">' . __( '--Separator--', 'wsko' ) . '</p>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<a class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link', '.bst-breadcrumb .', array(
                    'color' => 'green',
                ) );
                echo  ' ' ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link-tax', '.bst-breadcrumb .', array(
                    'color' => 'purple',
                ) );
                echo  htmlentities( '">' . __( '--Taxonomy Breadcrumb--', 'wsko' ) . '</a>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<p class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-separator', '.bst-breadcrumb .', array(
                    'color' => 'limegreen',
                ) );
                echo  htmlentities( '">' . __( '--Separator--', 'wsko' ) . '</p>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '<a class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link', '.bst-breadcrumb .', array(
                    'color' => 'green',
                ) );
                echo  ' ' ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-breadcrumb-link-post', '.bst-breadcrumb .', array(
                    'color' => 'brown',
                ) );
                echo  htmlentities( '">' . __( '--Post Breadcrumb--', 'wsko' ) . '</a>' ) ;
                echo  '<br/>' ;
                echo  htmlentities( '--Suffix--' ) ;
                echo  '</div>' ;
                echo  htmlentities( '</div>' ) . '<br/>' ;
                break;
            case 'content_table':
                echo  htmlentities( '<div class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-wrapper', '.', array(
                    'color' => 'red',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<div class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-heading', '.', array(
                    'color' => 'limegreen',
                ) );
                echo  htmlentities( '">' . __( '--Table of Contents Heading--', 'wsko' ) . '</div>' ) . '<br/>' ;
                echo  htmlentities( '<ol class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-gen', '.', array(
                    'color' => 'green',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<li class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row', '.', array(
                    'color' => 'purple',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<a href="#" class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-link', '.', array(
                    'color' => 'orange',
                ) );
                echo  htmlentities( '">' ) . __( '--Heading 1--', 'wsko' ) ;
                echo  htmlentities( '</a>' ) . '<br/>' ;
                echo  htmlentities( '<ol class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row-subitems', '.', array(
                    'color' => 'brown',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<li class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row', '.', array(
                    'color' => 'purple',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<a href="#" class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-link', '.', array(
                    'color' => 'orange',
                ) );
                echo  htmlentities( '">' ) . __( '--Heading 1.1--', 'wsko' ) ;
                echo  htmlentities( '</a>' ) . '<br/>' ;
                echo  htmlentities( '<ol class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row-subitems', '.', array(
                    'color' => 'brown',
                ) );
                echo  htmlentities( '">' ) . '<br/>' . __( '...Recursive subitems', 'wsko' ) . '<br/>' ;
                echo  htmlentities( '</ol>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</li>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</ol>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</li>' ) . '<br/>' ;
                echo  htmlentities( '<li class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row', '.', array(
                    'color' => 'purple',
                ) );
                echo  htmlentities( '">' ) . '<br/>' ;
                echo  '<div style="display:block;width:100%;margin-left:20px;">' ;
                echo  htmlentities( '<a href="#" class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-link', '.', array(
                    'color' => 'orange',
                ) );
                echo  htmlentities( '">' ) . __( '--Heading--', 'wsko' ) ;
                echo  htmlentities( '</a>' ) . '<br/>' ;
                echo  htmlentities( '<ol class="' ) ;
                WSKO_Class_Template::render_insert_widget_style_link( 'bst-content-table-row-subitems', '.', array(
                    'color' => 'brown',
                ) );
                echo  htmlentities( '">' ) . '<br/>' . __( '...Recursive subitems', 'wsko' ) . '<br/>' ;
                echo  htmlentities( '</ol>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</li>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</ol>' ) . '<br/>' ;
                echo  '</div>' ;
                echo  htmlentities( '</div>' ) . '<br/>' ;
                break;
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_insert_widget_style_link(
        $widget,
        $selector_prefix,
        $args = array(),
        $return = false
    )
    {
        $color = ( isset( $args['color'] ) ? $args['color'] : false );
        if ( $return ) {
            ob_start();
        }
        ?><a href="#" class="wsko-insert-widget-style" data-selector="<?php 
        echo  $selector_prefix . $widget ;
        ?>" style="<?php 
        echo  ( $color ? 'color:' . $color : '' ) ;
        ?>"><?php 
        echo  $widget ;
        ?></a><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_type_icon( $type, $return = false )
    {
        //$color = isset($args['color']) ? $args['color'] : false;
        if ( $return ) {
            ob_start();
        }
        switch ( $type ) {
            case 'table':
                echo  '<a href="#" data-tooltip="' . __( 'Table', 'wsko' ) . '"><i class="fa fa-table"></i></a>' ;
                break;
            case 'chart':
                echo  '<a href="#" data-tooltip="' . __( 'Chart', 'wsko' ) . '"><i class="fa fa-area-chart"></i></a>' ;
                break;
            case 'custom':
                echo  '<a href="#" data-tooltip="' . __( 'Generic View', 'wsko' ) . '"><i class="fa fa-file-code-o"></i></a>' ;
                break;
        }
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_rich_snippet_preview(
        $type,
        $data,
        $args = array(),
        $return = false
    )
    {
        $post = ( isset( $args['post'] ) ? $args['post'] : false );
        echo  $type ;
        switch ( $type ) {
            case 'article':
                break;
            case 'recipe':
                break;
        }
    }
    
    public static function render_rich_snippet_config(
        $snippet_structure,
        $path = false,
        $depth = 1,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $data = ( isset( $args['data'] ) ? $args['data'] : array() );
        $template = ( isset( $args['template'] ) ? $args['template'] : false );
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function render_dragndrop_metas(
        $type,
        $arg,
        $args = array(),
        $return = false
    )
    {
        if ( $return ) {
            ob_start();
        }
        $metas = array();
        $tax_m = array();
        switch ( $type ) {
            case 'post_id':
                $pms = get_post_meta( $arg );
                foreach ( $pms as $pm => $val ) {
                    
                    if ( !isset( $metas[$pm] ) ) {
                        $metas[$pm] = 1;
                    } else {
                        $metas[$pm]++;
                    }
                
                }
                $pts = get_post_taxonomies( $arg );
                foreach ( $pts as $pt ) {
                    
                    if ( !isset( $tax_m[$pt] ) ) {
                        $tax_m[$pt] = 1;
                    } else {
                        $tax_m[$pt]++;
                    }
                
                }
                break;
            case 'post_type':
                $rand_posts = WSKO_Class_Helper::get_random_post( $arg, 5 );
                if ( $rand_posts ) {
                    foreach ( $rand_posts as $r ) {
                        $pms = get_post_meta( $r->ID );
                        foreach ( $pms as $pm => $val ) {
                            
                            if ( !isset( $metas[$pm] ) ) {
                                $metas[$pm] = 1;
                            } else {
                                $metas[$pm]++;
                            }
                        
                        }
                        $pts = get_post_taxonomies( $r->ID );
                        foreach ( $pts as $pt ) {
                            
                            if ( !isset( $tax_m[$pt] ) ) {
                                $tax_m[$pt] = 1;
                            } else {
                                $tax_m[$pt]++;
                            }
                        
                        }
                    }
                }
                break;
            case 'post_tax':
                $terms = get_terms( array(
                    'taxonomy'   => $arg,
                    'hide_empty' => false,
                ) );
                foreach ( $terms as $term ) {
                    $pms = get_term_meta( $term->term_id );
                    foreach ( $pms as $pm => $val ) {
                        
                        if ( !isset( $metas[$pm] ) ) {
                            $metas[$pm] = 1;
                        } else {
                            $metas[$pm]++;
                        }
                    
                    }
                }
                break;
            case 'post_term':
                $pms = get_term_meta( $arg );
                foreach ( $pms as $pm => $val ) {
                    
                    if ( !isset( $metas[$pm] ) ) {
                        $metas[$pm] = 1;
                    } else {
                        $metas[$pm]++;
                    }
                
                }
                break;
            case 'global':
                $metas = WSKO_Class_Helper::get_all_meta_keys( 'post' );
                $tax_m = WSKO_Class_Helper::get_all_meta_keys( 'tax' );
                break;
        }
        ?><div class="wsko-metas-search-wrapper">
			<input type="text" class="wsko-metas-search-field form-control wsko-mb10" placeholder="Filter post fields">
			<!--<span class="font-unimportant"><?php 
        echo  __( 'Use the identified dragables from the sidebox or add "%meta:meta_name%"/"%tax:tax_name%" to your text to display dynamic post data within your SEO data.', 'wsko' ) ;
        ?></span>-->
			<p class="text-off pull-right"><small><i class="fa fa-compress"></i> <?php 
        echo  __( 'Drag & Drop', 'wsko' ) ;
        ?></small></p>
			<label><?php 
        echo  __( 'Site Attributes', 'wsko' ) ;
        ?></label>
			<ul class="wsko-snippet-fields" style="list-style-type:none;">
				<li class="wsko-metas-placeholder" data-tag="%site:blog_name%" data-search="site title site name"><i class="fa fa-compress"></i> <?php 
        echo  __( 'Site Name', 'wsko' ) ;
        ?></li>
				<li class="wsko-metas-placeholder" data-tag="%site:blog_tagline%" data-search="site tagline site tag line"><i class="fa fa-compress"></i> <?php 
        echo  __( 'Site Tagline', 'wsko' ) ;
        ?></li>
			</ul>
			<?php 
        switch ( $type ) {
            case 'post_id':
            case 'post_type':
            case 'other':
            case 'global':
                ?>
				<label><?php 
                echo  __( 'Post Attributes', 'wsko' ) ;
                ?></label>
				<ul class="wsko-snippet-fields" style="list-style-type:none;">
					<li class="wsko-metas-placeholder" data-tag="%post:post_title%" data-search="post title"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Post Title', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-placeholder" data-tag="%post:post_content%" data-search="post content"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Post Content', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-placeholder" data-tag="%post:post_excerpt%" data-search="post excerpt"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Post Excerpt', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-placeholder" data-tag="%post:post_author%" data-search="post author display name"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Post Author (Display Name)', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-search-no-items wsko-text-off" style="display:none;"><?php 
                echo  __( 'No matches found.', 'wsko' ) ;
                ?></li>
				</ul>
				<label><?php 
                echo  __( 'Custom Fields', 'wsko' ) ;
                ?></label>			
				<ul class="wsko-snippet-fields" style="list-style-type:none;">
					<?php 
                
                if ( !empty($metas) ) {
                    foreach ( $metas as $meta => $count ) {
                        ?><li class="wsko-metas-placeholder" data-tag="%meta:<?php 
                        echo  $meta ;
                        ?>%" data-search="<?php 
                        echo  strtolower( $meta ) ;
                        ?>"><i class="fa fa-compress"></i> <?php 
                        echo  $meta ;
                        ?></li><?php 
                    }
                } else {
                    ?><li>No custom fields found.</li><?php 
                }
                
                ?>
					<li class="wsko-metas-search-no-items wsko-text-off" style="display:none;"><?php 
                echo  __( 'No matches found.', 'wsko' ) ;
                ?></li>
				</ul>

				<label><?php 
                echo  __( 'Taxonomies', 'wsko' ) ;
                ?></label>
				<ul class="wsko-snippet-fields" style="list-style-type:none;">
					<?php 
                
                if ( !empty($tax_m) ) {
                    foreach ( $tax_m as $t => $count ) {
                        ?><li class="wsko-metas-placeholder" data-tag="%tax:<?php 
                        echo  $t ;
                        ?>%" data-search="<?php 
                        echo  strtolower( $meta ) ;
                        ?>"><i class="fa fa-compress"></i> <?php 
                        echo  $t ;
                        ?></li><?php 
                    }
                } else {
                    ?><li><?php 
                    echo  __( 'No taxonomies found.', 'wsko' ) ;
                    ?></li><?php 
                }
                
                ?>
					<li class="wsko-metas-search-no-items wsko-text-off" style="display:none;"><?php 
                echo  __( 'No matches found.', 'wsko' ) ;
                ?></li>
				</ul>
				<?php 
                break;
            case 'post_tax':
            case 'post_term':
                ?>
				<label><?php 
                echo  __( 'Term Attributes', 'wsko' ) ;
                ?></label>
				<ul class="wsko-snippet-fields" style="list-style-type:none;">
					<li class="wsko-metas-placeholder" data-tag="%term:term_title%" data-search="term title"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Term Title', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-placeholder" data-tag="%term:term_desc%" data-search="term description"><i class="fa fa-compress"></i> <?php 
                echo  __( 'Term Description', 'wsko' ) ;
                ?></li>
					<li class="wsko-metas-search-no-items" style="display:none;background-color:wheat;"><i><?php 
                echo  __( 'No matches found.', 'wsko' ) ;
                ?></i></li>
				</ul>
				<label><?php 
                echo  __( 'Term Custom Fields', 'wsko' ) ;
                ?></label>			
				<ul class="wsko-snippet-fields" style="list-style-type:none;">
					<?php 
                
                if ( !empty($metas) ) {
                    foreach ( $metas as $meta => $count ) {
                        ?><li class="wsko-metas-placeholder" data-tag="%term_meta:<?php 
                        echo  $meta ;
                        ?>%" data-search="<?php 
                        echo  strtolower( $meta ) ;
                        ?>"><i class="fa fa-compress"></i> <?php 
                        echo  $meta ;
                        ?></li><?php 
                    }
                } else {
                    ?><li><?php 
                    echo  __( 'No custom fields found.', 'wsko' ) ;
                    ?></li><?php 
                }
                
                ?>
					<li class="wsko-metas-search-no-items" style="display:none;background-color:wheat;"><i><?php 
                echo  __( 'No matches found.', 'wsko' ) ;
                ?></i></li>
				</ul><?php 
                break;
        }
        ?>
		</div><?php 
        if ( $return ) {
            return ob_get_clean();
        }
    }
    
    public static function get_color_step(
        $min,
        $max,
        $val,
        $yellow = false
    )
    {
        if ( $min > 0 && $val <= 0 ) {
            return 'red';
        }
        if ( $val < $min || $val > $max ) {
            return ( $yellow ? 'orange' : 'red' );
        }
        return 'green';
    }
    
    public static function get_color_step_5(
        $min1,
        $min2,
        $max1,
        $max2,
        $val
    )
    {
        if ( $min1 > 0 && $val <= 0 ) {
            return 'red';
        }
        if ( $val < $min1 || $val > $max2 ) {
            return 'red';
        }
        if ( $val < $min2 || $val > $max1 ) {
            return 'orange';
        }
        return 'green';
    }

}