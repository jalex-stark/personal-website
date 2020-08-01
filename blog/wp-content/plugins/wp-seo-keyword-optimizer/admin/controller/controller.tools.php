<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller_Tools extends WSKO_Controller
{
    //Options
    public  $icon = "wrench" ;
    public  $link = "tools" ;
    public  $scripts = array( 'tools' ) ;
    public  $styles = array( 'tools' ) ;
    public  $template_folder = "tools" ;
    public  $ajax_actions = array(
        'table_tools',
        'set_metas',
        'remove_auto_redirect',
        'save_robots',
        'save_htaccess',
        'add_redirect',
        'check_redirect',
        'update_automatic_redirect',
        'remove_redirect',
        'remove_page_redirect',
        'update_sitemap',
        'update_sitemap_real',
        'upload_sitemap',
        'get_link_popup',
        'get_widget_preview',
        'get_rich_snippet_config',
        'create_rich_snippet',
        'save_rich_snippet_config',
        'delete_rich_snippet'
    ) ;
    public  $subpages = array(
        'metas'         => array(
        'template' => 'tools/page-metas.php',
    ),
        'rich_snippets' => array(
        'template'   => 'tools/page-rich-snippets.php',
        'is_premium' => true,
        'is_beta'    => true,
    ),
        'links'         => array(
        'template' => 'tools/page-links.php',
    ),
        'linking'       => array(
        'template'   => 'tools/page-linking.php',
        'is_premium' => true,
    ),
        'redirects'     => array(
        'template' => 'tools/page-redirects.php',
    ),
        'sitemap'       => array(
        'template' => 'tools/page-sitemap.php',
    ),
        'robots'        => array(
        'template' => 'tools/page-robots.php',
    ),
        'widgets'       => array(
        'template' => 'tools/page-widgets.php',
        'is_beta'  => true,
    ),
    ) ;
    public function get_title()
    {
        return __( 'Tools', 'wsko' );
    }
    
    public function get_subpage_title( $subpage )
    {
        switch ( $subpage ) {
            case 'metas':
                return __( 'Metas & Social Snippets', 'wsko' );
            case 'rich_snippets':
                return __( 'Rich Snippets', 'wsko' );
            case 'widgets':
                return __( 'Frontend Widgets', 'wsko' );
            case 'links':
                return __( 'Permalinks', 'wsko' );
            case 'linking':
                return __( 'Internal Links', 'wsko' );
            case 'redirects':
                return __( 'Redirect Manager', 'wsko' );
            case 'sitemap':
                return __( 'Sitemap', 'wsko' );
            case 'robots':
                return __( '.htaccess & robots.txt', 'wsko' );
        }
        return '';
    }
    
    public function get_knowledge_base_tags( $subpage )
    {
        $res = array( 'tools' );
        switch ( $subpage ) {
            case 'metas':
                $res[] = "tools_metas";
                $res[] = "tools_social_snippets";
                break;
            case 'rich_snippets':
                $res[] = "tools_rich_snippets";
                break;
            case 'widgets':
                $res[] = "tools_widgets";
                break;
            case 'links':
                $res[] = "tools_permalinks";
                break;
            case 'linking':
                $res[] = "tools_internal_links";
                break;
            case 'redirects':
                $res[] = "tools_redirect_manager";
                break;
            case 'sitemap':
                $res[] = "tools_sitemap";
                break;
            case 'robots':
                $res[] = "tools_htaccess";
                break;
        }
        return $res;
    }
    
    public function load_lazy_page_data( $lazy_data )
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $notif = "";
        $data = array();
        $page = $this->get_current_subpage();
        
        if ( $page ) {
            $accessible = WSKO_Class_Onpage::seo_plugins_disabled();
            switch ( $page ) {
                case 'metas':
                    
                    if ( $accessible ) {
                        $post_types = WSKO_Class_Helper::get_public_post_types( 'objects' );
                        $taxonomies = WSKO_Class_Helper::get_public_taxonomies( 'objects' );
                        foreach ( $post_types as $type ) {
                            //$data['post_type_'.$type->name] = WSKO_Class_Template::render_template('tools/template-metas-view.php', array('post_type' => $type->name, 'type' => 'post_type', 'meta_view' => 'metas'), true);
                            $data['post_type_' . $type->name] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                                'post_type' => $type->name,
                                'type'      => 'post_type',
                                'meta_view' => 'co',
                            ), true );
                        }
                        foreach ( $taxonomies as $tax ) {
                            //$data['post_tax_'.$tax->name] = WSKO_Class_Template::render_template('tools/template-metas-view.php', array('post_tax' => $tax->name, 'type' => 'post_tax', 'meta_view' => 'metas'), true);
                            $data['post_tax_' . $tax->name] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                                'post_tax'  => $tax->name,
                                'type'      => 'post_tax',
                                'meta_view' => 'co',
                            ), true );
                        }
                        $data['other_home'] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                            'arg'       => 'home',
                            'type'      => 'other',
                            'meta_view' => 'co',
                        ), true );
                        $data['other_blog'] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                            'arg'       => 'blog',
                            'type'      => 'other',
                            'meta_view' => 'co',
                        ), true );
                    }
                    
                    break;
                case 'links':
                    
                    if ( $accessible ) {
                        $post_types = WSKO_Class_Helper::get_public_post_types( 'objects' );
                        unset( $post_types['post'] );
                        unset( $post_types['page'] );
                        $taxonomies = WSKO_Class_Helper::get_public_taxonomies( 'objects' );
                        foreach ( $post_types as $type ) {
                            $data['post_type_' . $type->name] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                                'post_type' => $type->name,
                                'type'      => 'post_type',
                                'meta_view' => 'links',
                            ), true );
                        }
                        foreach ( $taxonomies as $tax ) {
                            $data['post_tax_' . $tax->name] = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                                'post_tax'  => $tax->name,
                                'type'      => 'post_tax',
                                'meta_view' => 'links',
                            ), true );
                        }
                    }
                    
                    break;
                case 'redirects':
                    $redirects = WSKO_Class_Onpage::get_redirects();
                    $page_redirects = WSKO_Class_Onpage::get_page_redirects();
                    $auto_post_redirects = WSKO_Class_Onpage::get_auto_redirects( 'post_id' );
                    $auto_post_type_redirects = WSKO_Class_Onpage::get_auto_redirects( 'post_type' );
                    $table_redirects = array();
                    $table_page_redirects = array();
                    $table_auto_redirects = array();
                    $is_admin = current_user_can( 'manage_options' );
                    if ( $redirects ) {
                        foreach ( $redirects as $k => $r ) {
                            
                            if ( $r['type'] == 2 ) {
                                //old system
                                $r['type'] = 302;
                            } else {
                                if ( $r['type'] == 1 ) {
                                    $r['type'] = 301;
                                }
                            }
                            
                            $table_redirects[] = array(
                                array(
                                'value' => $r['comp'],
                            ),
                                array(
                                'value' => $r['page'],
                            ),
                                array(
                                'value' => $r['type'],
                            ),
                                array(
                                'value' => $r['comp_to'],
                            ),
                                array(
                                'value' => $r['target'] . '<br/><p class="font-unimportant">' . WSKO_Class_Helper::format_url( $r['target'] ) . '</p>',
                            ),
                                array(
                                'value' => ( $is_admin ? WSKO_Class_Template::render_ajax_button(
                                '<i class="fa fa-times"></i>',
                                'remove_redirect',
                                array(
                                'redirects' => $k,
                            ),
                                array(
                                'no_button' => true,
                            ),
                                true
                            ) : '' ),
                            )
                            );
                        }
                    }
                    if ( $page_redirects ) {
                        foreach ( $page_redirects as $k => $r ) {
                            
                            if ( $r['type'] == 2 ) {
                                //old system
                                $r['type'] = 302;
                            } else {
                                if ( $r['type'] == 1 ) {
                                    $r['type'] = 301;
                                }
                            }
                            
                            $table_page_redirects[] = array(
                                array(
                                'value' => WSKO_Class_Template::render_url_post_field( $r['post'], array(), true ),
                            ),
                                array(
                                'value' => $r['type'],
                            ),
                                array(
                                'value' => $r['to'] . '<br/><p class="font-unimportant">' . WSKO_Class_Helper::home_url( $r['to'] ) . '</p>',
                            ),
                                array(
                                'value' => ( $is_admin ? WSKO_Class_Template::render_ajax_button(
                                '<i class="fa fa-times"></i>',
                                'remove_page_redirect',
                                array(
                                'post' => $r['post'],
                            ),
                                array(
                                'no_button' => true,
                            ),
                                true
                            ) : '' ),
                            )
                            );
                        }
                    }
                    if ( $auto_post_redirects ) {
                        foreach ( $auto_post_redirects as $p => $links ) {
                            $is_blocked = false;
                            $status = get_post_status( $p );
                            if ( in_array( $status, WSKO_Class_Onpage::get_blocked_post_stati( 'redirects' ) ) ) {
                                $is_blocked = true;
                            }
                            ob_start();
                            ?><br/><ul><?php 
                            foreach ( $links as $k => $l ) {
                                ?><li>
								<i><?php 
                                echo  $l ;
                                ?></i><?php 
                                if ( $is_admin ) {
                                    WSKO_Class_Template::render_ajax_button(
                                        '<i class="fa fa-times"></i>',
                                        'remove_auto_redirect',
                                        array(
                                        'type' => 'post_id',
                                        'arg'  => $p,
                                        'key'  => $k,
                                    ),
                                        array(
                                        'no_button' => true,
                                    )
                                    );
                                }
                                ?></li><?php 
                            }
                            $links_view = ob_get_clean();
                            $table_auto_redirects[] = array(
                                array(
                                'value' => WSKO_Class_Template::render_url_post_field( $p, array(), true ) . (( $is_blocked ? sprintf( __( 'Redirect is disabled, because the post is in status "%s"', 'wsko' ), $status ) : '' )),
                            ),
                                array(
                                'value' => 'post',
                            ),
                                array(
                                'order' => count( $links ),
                                'value' => sprintf( __( '%d redirect(s) from %s', 'wsko' ), count( $links ), $links_view ),
                            ),
                                array(
                                'value' => ( $is_admin ? WSKO_Class_Template::render_ajax_button(
                                '<i class="fa fa-times"></i>',
                                'remove_auto_redirect',
                                array(
                                'type' => 'post_id',
                                'arg'  => $p,
                            ),
                                array(
                                'no_button' => true,
                            ),
                                true
                            ) : '' ),
                            )
                            );
                        }
                    }
                    if ( $auto_post_type_redirects ) {
                        foreach ( $auto_post_type_redirects as $pt => $redirects ) {
                            $red_temp = "";
                            $sources = $redirects['source'];
                            if ( $sources ) {
                                foreach ( $sources as $slug => $link_snapshot ) {
                                    $red_temp .= sprintf( __( 'From: %s (%d)', 'wsko' ), $slug, count( $link_snapshot ) ) . (( $is_admin ? WSKO_Class_Template::render_ajax_button(
                                        '<i class="fa fa-times"></i>',
                                        'remove_auto_redirect',
                                        array(
                                        'type' => 'post_type',
                                        'arg'  => $pt,
                                        'key'  => $slug,
                                    ),
                                        array(
                                        'no_button' => true,
                                    ),
                                        true
                                    ) : '' )) . '<br/>';
                                }
                            }
                            if ( $red_temp ) {
                                $table_auto_redirects[] = array(
                                    array(
                                    'value' => $pt,
                                ),
                                    array(
                                    'value' => 'post type',
                                ),
                                    array(
                                    'value' => $red_temp,
                                ),
                                    array(
                                    'value' => ( $is_admin ? WSKO_Class_Template::render_ajax_button(
                                    '<i class="fa fa-times"></i>',
                                    'remove_auto_redirect',
                                    array(
                                    'type' => 'post_type',
                                    'arg'  => $pt,
                                ),
                                    array(
                                    'no_button' => true,
                                ),
                                    true
                                ) : '' ),
                                )
                                );
                            }
                        }
                    }
                    $data['redirects'] = WSKO_Class_Template::render_template( 'tools/view-redirects.php', array(
                        'redirects'      => $table_redirects,
                        'page_redirects' => $table_page_redirects,
                        'auto_redirects' => $table_auto_redirects,
                    ), true );
                    break;
                case 'linking':
                    break;
            }
            return array(
                'success' => true,
                'data'    => $data,
                'notif'   => $notif,
            );
        }
    
    }
    
    public function action_table_tools()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $data = array();
        $is_custom = false;
        $is_formated = false;
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        $order = ( isset( $_POST['order'] ) ? $_POST['order'] : false );
        $offset = ( isset( $_POST['start'] ) ? intval( $_POST['start'] ) : 0 );
        $count = ( isset( $_POST['length'] ) ? intval( $_POST['length'] ) : 10 );
        $custom_filter = ( isset( $_POST['custom_filter'] ) ? $_POST['custom_filter'] : false );
        $search = ( isset( $_POST['search']['value'] ) ? sanitize_text_field( $_POST['search']['value'] ) : false );
        if ( $offset < 0 ) {
            $offset = 0;
        }
        if ( $count < 1 ) {
            $count = 1;
        }
        
        if ( $order && $order[0] ) {
            
            if ( $order[0]['dir'] == "asc" ) {
                $orderdir = 1;
            } else {
                $orderdir = 0;
            }
            
            $order = $order[0]['column'];
        } else {
            $order = 0;
            $orderdir = 1;
        }
        
        $order = intval( $order );
        $filtered_data = 0;
        $total_data = 0;
        switch ( $arg ) {
            case 'bulk_metas':
                $is_custom = true;
                $post_type = 'any';
                if ( $custom_filter ) {
                    foreach ( $custom_filter as $cf ) {
                        if ( $cf['key'] == 'post_type' ) {
                            $post_type = $cf['val'];
                        }
                    }
                }
                $orderby = "";
                $keys = array( 'post_title', 'post_title' );
                if ( isset( $keys[$order] ) ) {
                    $orderby = ( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] );
                }
                $orderdir = ( $orderdir ? 'ASC' : 'DESC' );
                $query = new WP_Query( array(
                    's'              => $search,
                    'post_type'      => $post_type,
                    'posts_per_page' => $count,
                    'offset'         => $offset,
                    'fields'         => 'ids',
                    'orderby'        => $orderby,
                    'order'          => $orderdir,
                ) );
                $res = array();
                foreach ( $query->posts as $p ) {
                    $url = get_permalink( $p );
                    $t = get_the_title( $p );
                    $res[] = array(
                        WSKO_Class_Template::render_url_post_field( $p, array(
                            'open_tab' => 'metas',
                        ), true ),
                        //'<div class="wsko_nowrap" title="'.$url.'"><p style="float:right">'.WSKO_Class_Template::render_content_optimizer_link($p, array(), true).'</p><span><p>'.($t?$t:WSKO_Class_Helper::get_empty_page_title()).'</p><a class="font-unimportant" href="'.$url.'">'.$url.'</a></span></div>',
                        WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                            'post_id'   => $p,
                            'type'      => 'post_id',
                            'meta_view' => 'co',
                            'collapse'  => true,
                        ), true ),
                    );
                }
                $data = array(
                    'data'     => $res,
                    'filtered' => $query->found_posts,
                    'total'    => $query->found_posts,
                );
                break;
            case 'bulk_links':
                $is_custom = true;
                $post_type = 'any';
                if ( $custom_filter ) {
                    foreach ( $custom_filter as $cf ) {
                        if ( $cf['key'] == 'post_type' ) {
                            $post_type = $cf['val'];
                        }
                    }
                }
                $orderby = "";
                $keys = array( 'post_title', 'post_title' );
                if ( isset( $keys[$order] ) ) {
                    $orderby = ( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] );
                }
                $orderdir = ( $orderdir ? 'ASC' : 'DESC' );
                $query = new WP_Query( array(
                    's'              => $search,
                    'post_type'      => $post_type,
                    'posts_per_page' => $count,
                    'offset'         => $offset,
                    'fields'         => 'ids',
                    'orderby'        => $orderby,
                    'order'          => $orderdir,
                ) );
                $res = array();
                foreach ( $query->posts as $p ) {
                    $url = get_permalink( $p );
                    $t = get_the_title( $p );
                    $res[] = array(
                        WSKO_Class_Template::render_url_post_field( $p, array(), true ),
                        //'<div class="wsko_nowrap" title="'.$url.'"><p style="float:right">'.WSKO_Class_Template::render_content_optimizer_link($p, array(), true).'</p><span><p>'.($t?$t:WSKO_Class_Helper::get_empty_page_title()).'</p><a class="font-unimportant" href="'.$url.'">'.$url.'</a></span></div>',
                        WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                            'post_id'   => $p,
                            'type'      => 'post_id',
                            'meta_view' => 'links',
                            'collapse'  => true,
                        ), true ),
                    );
                }
                $data = array(
                    'data'     => $res,
                    'filtered' => $query->found_posts,
                    'total'    => $query->found_posts,
                );
                break;
            case 'linking_pages':
                break;
            case 'linking_orphaned':
                break;
            case 'sitemap':
                $data = array();
                $sitemap_real_path = get_home_path() . 'sitemap_bst.xml';
                
                if ( file_exists( $sitemap_real_path ) ) {
                    $xml = simplexml_load_file( $sitemap_real_path );
                    
                    if ( $xml ) {
                        $childs = $xml->children();
                        foreach ( $childs as $child ) {
                            $data[] = array(
                                'page'          => $child->loc->__toString(),
                                'last_modified' => strtotime( $child->lastmod->__toString() ),
                                'freq'          => ( $child->changefreq ? $child->changefreq->__toString() : '' ),
                                'prio'          => ( $child->priority ? $child->priority->__toString() : '0.5 (default)' ),
                            );
                        }
                    }
                
                }
                
                $params = array(
                    'format' => array(
                    '0' => 'url',
                ),
                );
                break;
            case 'rich_snippets':
                $data = array();
                $snippets = WSKO_Class_Cache::get_wsko_option( 'rich_snippets' );
                foreach ( $snippets as $loc => $snippets_l ) {
                    foreach ( $snippets_l as $s_key => $snippets_i ) {
                        $post_id = false;
                        
                        if ( $loc == 'post_type' ) {
                            $post_id = WSKO_Class_Helper::get_random_post( $snippets_i['loc']['post_type'], 1, 'ids' );
                        } else {
                            if ( $loc == 'post' ) {
                                $post_id = $snippets_i['loc']['posts'][mt_rand( 0, count( $snippets_i['loc']['posts'] ) - 1 )];
                            }
                        }
                        
                        $snippet_o = WSKO_Class_Snippets::get_available_snippets( $snippets_i['type'] );
                        $data[] = array(
                            'temp1'    => array( $snippet_o['icon'], $loc, $snippets_i['type'] ),
                            'temp2'    => array(
                            $snippets_i['type'],
                            $snippets_i['data'],
                            $s_key,
                            $loc,
                            $post_id
                        ),
                            'location' => $loc,
                        );
                    }
                }
                $params = array(
                    'specific_keys' => array( 'temp1', 'temp2' ),
                    'format'        => array(
                    '0' => function ( $a ) {
                    return '<i class="fa fa-' . $a[0] . ' fa-3x"></i><br/>' . $a[2] . '<br/><small class="text-off">' . $a[1] . '</small>';
                },
                    '1' => function ( $a ) {
                    ob_start();
                    $snippet = WSKO_Class_Snippets::get_available_snippets( $a[0] );
                    $ld_json = WSKO_Class_Snippets::get_rich_snippets( array(
                        'post' => $a[4],
                    ), true );
                    $id = WSKO_Class_Helper::get_unique_id( 'wsko_rich_snippet_details_data' );
                    $id2 = WSKO_Class_Helper::get_unique_id( 'wsko_rich_snippet_details_edit' );
                    WSKO_Class_Template::render_rich_snippet_preview( $a[0], $a[1], array(
                        'post' => $a[4],
                    ) );
                    ?><button class="button" data-toggle="collapse" data-target="#<?php 
                    echo  $id ;
                    ?>"><?php 
                    echo  __( 'Data', 'wsko' ) ;
                    ?></button>
						<button class="button" data-toggle="collapse" data-target="#<?php 
                    echo  $id2 ;
                    ?>"><?php 
                    echo  __( 'Edit', 'wsko' ) ;
                    ?></button>
						<?php 
                    WSKO_Class_Template::render_ajax_button(
                        __( 'Delete', 'wsko' ),
                        'delete_rich_snippet',
                        array(
                        'location' => $a[3],
                        'key'      => $a[2],
                    ),
                        array(
                        'table_reload' => true,
                        'no_reload'    => true,
                    )
                    );
                    ?>
						
						<div id="<?php 
                    echo  $id ;
                    ?>" class="collapse">
							<pre><?php 
                    echo  htmlentities( $ld_json ) ;
                    ?></pre>
							<form method="post" target="_blank" action="https://search.google.com/structured-data/testing-tool">
								<textarea name="code" style="display:none"><?php 
                    echo  htmlentities( $ld_json ) ;
                    ?></textarea>
							<input type="submit" class="button" value="<?php 
                    echo  __( 'Test Markup', 'wsko' ) ;
                    ?>">
						</form>
						</div>
						<div id="<?php 
                    echo  $id2 ;
                    ?>" class="collapse wsko-edit-rich-snippet-container">
							<div class="row">
								<div class="col-md-9">
									<?php 
                    WSKO_Class_Template::render_rich_snippet_config(
                        $snippet,
                        false,
                        1,
                        array(
                        'data' => $a[1],
                    )
                    );
                    ?>
								</div>
								<div class="col-md-3">
                                	<?php 
                    WSKO_Class_Template::render_dragndrop_metas( 'global', false, array() );
                    ?>
								</div>
							</div>
							<a href="#" class="button wsko-edit-rich-snippet-save" data-location="<?php 
                    echo  $a[3] ;
                    ?>" data-key="<?php 
                    echo  $a[2] ;
                    ?>" data-nonce="<?php 
                    echo  wp_create_nonce( 'wsko_save_rich_snippet_config' ) ;
                    ?>"><?php 
                    echo  __( 'Save Snippet', 'wsko' ) ;
                    ?></a>
						</div><?php 
                    return ob_get_clean();
                },
                ),
                );
                break;
            case 'rich_snippets_single':
                $data = array();
                $post_type = 'any';
                if ( $custom_filter ) {
                    foreach ( $custom_filter as $cf ) {
                        if ( $cf['key'] == 'post_type' ) {
                            $post_type = $cf['val'];
                        }
                    }
                }
                $orderby = "";
                $keys = array( 'post_title', 'post_title' );
                if ( isset( $keys[$order] ) ) {
                    $orderby = ( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] );
                }
                $orderdir = ( $orderdir ? 'ASC' : 'DESC' );
                $query = new WP_Query( array(
                    'post_type'      => $post_type,
                    'posts_per_page' => -1,
                    'offset'         => $offset,
                    'fields'         => 'ids',
                    'meta_query'     => array( array(
                    'key'     => '_wsko_rich_snippets',
                    'compare' => 'EXISTS',
                ) ),
                ) );
                $res = array();
                foreach ( $query->posts as $p ) {
                    $snippets = WSKO_Class_Core::get_post_data( $p, 'rich_snippets' );
                    foreach ( $snippets as $s_key => $snippets_i ) {
                        $snippet_o = WSKO_Class_Snippets::get_available_snippets( $snippets_i['type'] );
                        $data[] = array(
                            'post'  => $p,
                            'temp1' => array( $snippet_o['icon'], $snippets_i['type'] ),
                            'temp2' => array(
                            $snippets_i['type'],
                            $snippets_i['data'],
                            $s_key,
                            $p
                        ),
                        );
                    }
                }
                $params = array(
                    'specific_keys' => array(
                    'post',
                    'temp1',
                    'temp2',
                    'post'
                ),
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_url_post_field( $arg, array(), true );
                },
                    '1' => function ( $arg ) {
                    return '<i class="fa fa-' . $arg[0] . ' fa-3x"></i><br/>' . $arg[1];
                },
                    '2' => function ( $arg ) {
                    ob_start();
                    $snippet = WSKO_Class_Snippets::get_available_snippets( $arg[0] );
                    $ld_json = WSKO_Class_Snippets::get_rich_snippets( array(
                        'post' => $arg[3],
                    ), true );
                    $id = WSKO_Class_Helper::get_unique_id( 'wsko_rich_snippet_details_data' );
                    $id2 = WSKO_Class_Helper::get_unique_id( 'wsko_rich_snippet_details_edit' );
                    WSKO_Class_Template::render_rich_snippet_preview( $arg[0], $arg[1], array(
                        'post' => $arg[3],
                    ) );
                    ?><button class="button" data-toggle="collapse" data-target="#<?php 
                    echo  $id ;
                    ?>"><?php 
                    echo  __( 'Data', 'wsko' ) ;
                    ?></button>
						<button class="button" data-toggle="collapse" data-target="#<?php 
                    echo  $id2 ;
                    ?>"><?php 
                    echo  __( 'Edit', 'wsko' ) ;
                    ?></button>
						<?php 
                    WSKO_Class_Template::render_ajax_button(
                        __( 'Delete', 'wsko' ),
                        'delete_rich_snippet',
                        array(
                        'post' => $arg[3],
                        'key'  => $arg[2],
                    ),
                        array(
                        'table_reload' => true,
                        'no_reload'    => true,
                    )
                    );
                    ?>
						
						<div id="<?php 
                    echo  $id ;
                    ?>" class="collapse">
							<pre><?php 
                    echo  htmlentities( $ld_json ) ;
                    ?></pre>
							<form method="post" target="_blank" action="https://search.google.com/structured-data/testing-tool">
								<textarea name="code" style="display:none"><?php 
                    echo  htmlentities( $ld_json ) ;
                    ?></textarea>
							<input type="submit" class="button" value="<?php 
                    echo  __( 'Test Markup', 'wsko' ) ;
                    ?>">
						</form>
						</div>
						<div id="<?php 
                    echo  $id2 ;
                    ?>" class="collapse wsko-edit-rich-snippet-container">
							<div class="row">
								<div class="col-md-9">
									<?php 
                    WSKO_Class_Template::render_rich_snippet_config(
                        $snippet,
                        false,
                        1,
                        array(
                        'data' => $arg[1],
                    )
                    );
                    ?>
								</div>
								<div class="col-md-3">
                                	<?php 
                    WSKO_Class_Template::render_dragndrop_metas( 'global', false, array() );
                    ?>
								</div>
							</div>
							<a href="#" class="button wsko-edit-rich-snippet-save" data-post="<?php 
                    echo  $arg[3] ;
                    ?>" data-key="<?php 
                    echo  $arg[2] ;
                    ?>" data-nonce="<?php 
                    echo  wp_create_nonce( 'wsko_save_rich_snippet_config' ) ;
                    ?>"><?php 
                    echo  __( 'Save Snippet', 'wsko' ) ;
                    ?></a>
						</div><?php 
                    return ob_get_clean();
                },
                    '3' => function ( $arg ) {
                    return WSKO_Class_Template::render_content_optimizer_link( $arg, array(), true );
                },
                ),
                );
                break;
        }
        
        if ( $data ) {
            if ( !$is_custom ) {
                $data = $this->get_table_data( $data, $params, $is_formated );
            }
            if ( !$filtered_data && isset( $data['filtered'] ) ) {
                $filtered_data = $data['filtered'];
            }
            if ( !$total_data && isset( $data['total'] ) ) {
                $total_data = $data['total'];
            }
        }
        
        return array(
            'success'         => true,
            'data'            => ( $data ? $data['data'] : array() ),
            'recordsFiltered' => $filtered_data,
            'recordsTotal'    => $total_data,
        );
    }
    
    public function action_set_metas()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        if ( !WSKO_Class_Onpage::seo_plugins_disabled() ) {
            return array(
                'success' => false,
                'msg'     => __( "You can't edit metas from other SEO plugins!", 'wsko' ),
            );
        }
        $data = false;
        $data_str = ( isset( $_POST['data'] ) ? $_POST['data'] : false );
        if ( $data_str ) {
            parse_str( $data_str, $data );
        }
        
        if ( isset( $data['robots_ni'] ) || isset( $data['robots_nf'] ) ) {
            $robots_ni = ( isset( $data['robots_ni'] ) && $data['robots_ni'] && $data['robots_ni'] != 'false' ? true : false );
            $robots_nf = ( isset( $data['robots_nf'] ) && $data['robots_nf'] && $data['robots_nf'] != 'false' ? true : false );
            unset( $data['robots_ni'] );
            unset( $data['robots_nf'] );
            $data['robots'] = ( $robots_ni ? ( $robots_nf ? 3 : 2 ) : (( $robots_nf ? 1 : 0 )) );
        }
        
        $canon_data = false;
        
        if ( isset( $data['canon_t'] ) ) {
            $canon_d = $data['canon'];
            $canon_data = array(
                'type' => ( is_numeric( $canon_d ) ? 2 : 3 ),
                'arg'  => ( is_numeric( $canon_d ) ? intval( $canon_d ) : $canon_d ),
            );
            
            if ( $data['canon_t'] === '0' ) {
                $canon_data['type'] = 0;
            } else {
                if ( $data['canon_t'] === '1' ) {
                    $canon_data['type'] = 1;
                }
            }
        
        }
        
        if ( isset( $data['canon_t'] ) ) {
            unset( $data['canon_t'] );
        }
        if ( isset( $data['hide_slug'] ) ) {
            unset( $data['hide_slug'] );
        }
        //temporary disabled
        if ( isset( $data['hide_slug'] ) ) {
            $data['hide_slug'] = ( $data['hide_slug'] && $data['hide_slug'] != 'false' ? true : false );
        }
        $meta_view = ( isset( $data['meta_view'] ) ? sanitize_text_field( $data['meta_view'] ) : false );
        $collapse = ( isset( $data['collapse'] ) && $data['collapse'] && $data['collapse'] != 'false' ? true : false );
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        
        if ( $type && $arg ) {
            $data = array_map( 'stripslashes', array_map( 'sanitize_text_field', $data ) );
            $data_pre = WSKO_Class_Onpage::get_meta_object( $arg, $type );
            if ( $canon_data ) {
                $data['canon'] = $canon_data;
            }
            
            if ( isset( $data['url'] ) ) {
                
                if ( $type === "post_id" ) {
                    $post = intval( $arg );
                    $url = sanitize_title_with_dashes( WSKO_Class_Helper::map_special_chars( $data['url'] ) );
                    if ( $post && $url ) {
                        wp_update_post( array(
                            'ID'        => $post,
                            'post_name' => $url,
                        ) );
                    }
                } else {
                    if ( $type === "post_type" || $type === "post_tax" ) {
                        $data['slug'] = trim( $data['url'], ' ' );
                    }
                }
                
                unset( $data['url'] );
            }
            
            if ( $type !== "post_type" ) {
                unset( $data['hide_slug'] );
            }
            WSKO_Class_Onpage::update_automatic_redirects(
                $type,
                $arg,
                ( isset( $data['hide_slug'] ) && $data['hide_slug'] ? '/' : (( isset( $data['slug'] ) && $data['slug'] ? $data['slug'] : false )) ),
                ( isset( $data['create_redirects'] ) && $data['create_redirects'] && $data['create_redirects'] !== 'false' ? true : false )
            );
            if ( isset( $data['create_redirects'] ) ) {
                unset( $data['create_redirects'] );
            }
            if ( $data_pre ) {
                $data = $data + $data_pre;
            }
            //array_merge($data_pre, $data);
            if ( isset( $data['meta_view'] ) ) {
                unset( $data['meta_view'] );
            }
            if ( isset( $data['collapse'] ) ) {
                unset( $data['collapse'] );
            }
            
            if ( isset( $_POST['reset'] ) && $_POST['reset'] ) {
                WSKO_Class_Onpage::unset_meta_object( $arg, $type );
            } else {
                WSKO_Class_Onpage::set_meta_object( $arg, $data, $type );
            }
            
            $type_s = ( $type === 'post_archive' ? 'post_type' : (( $type === 'other' ? 'arg' : $type )) );
            WSKO_Class_Helper::refresh_permalinks();
            $view = WSKO_Class_Template::render_template( 'tools/template-metas-view.php', array(
                $type_s            => $arg,
                'type'             => $type,
                'meta_view'        => $meta_view,
                'collapse'         => $collapse,
                'is_collapse_open' => true,
            ), true );
        }
        
        if ( $view ) {
            return array(
                'success'  => true,
                'new_view' => $view,
            );
        }
    }
    
    public function action_remove_auto_redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        $key = ( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : false );
        if ( $type && $arg ) {
            WSKO_Class_Onpage::remove_auto_redirect( $type, $arg, $key );
        }
        return true;
    }
    
    public function action_save_robots()
    {
        if ( !$this->can_execute_action( true ) ) {
            return false;
        }
        
        if ( WSKO_Class_Core::get_setting( 'activate_editor_robots' ) ) {
            $robots_path = get_home_path() . 'robots.txt';
            $robots_exists = file_exists( $robots_path );
            
            if ( $robots_exists && is_writable( $robots_path ) || !$robots_exists && is_writable( get_home_path() ) ) {
                $robots = ( isset( $_POST['robots'] ) ? "" . $_POST['robots'] : "" );
                
                if ( $robots_exists ) {
                    $path = WSKO_Class_Helper::get_temp_dir( 'backups' );
                    if ( $path ) {
                        copy( $robots_path, $path . basename( $robots_path ) . '-' . time() . '-' . date( 'd.m.Y-H:i' ) . '.bak' );
                    }
                }
                
                file_put_contents( $robots_path, $robots );
                return true;
            }
        
        }
    
    }
    
    public function action_save_htaccess()
    {
        if ( !$this->can_execute_action( true ) ) {
            return false;
        }
        
        if ( WSKO_Class_Core::get_setting( 'activate_editor_htaccess' ) ) {
            $htaccess_path = get_home_path() . '.htaccess';
            $htaccess_exists = file_exists( $htaccess_path );
            
            if ( $htaccess_exists && is_writable( $htaccess_path ) && is_readable( $htaccess_path ) || !$htaccess_exists && is_writable( get_home_path() ) ) {
                $home_url = home_url();
                $http_code = false;
                $http_codes = WSKO_Class_Helper::get_http_status_codes( $home_url, array(), true );
                
                if ( $http_codes ) {
                    $last = end( $http_codes );
                    
                    if ( isset( $last['url'] ) && isset( $last['code'] ) && $last['code'] == 200 ) {
                        $home_url = $last['url'];
                        $http_code = $last['code'];
                    }
                
                }
                
                if ( $http_code != 200 ) {
                    return array(
                        'success' => false,
                        'msg'     => __( 'Could not find a non redirecting home-url. Your changes have been blocked for security reasons.', 'wsko' ),
                    );
                }
                $htaccess = ( isset( $_POST['htaccess'] ) ? "" . $_POST['htaccess'] : "" );
                $backup_r = '';
                
                if ( $htaccess_exists ) {
                    $path = WSKO_Class_Helper::get_temp_dir( 'backups' );
                    if ( $path ) {
                        copy( $htaccess_path, $path . basename( $htaccess_path ) . '-' . time() . '-' . date( 'd.m.Y-H:i' ) . '.bak' );
                    }
                    $backup_r = file_get_contents( $htaccess_path );
                }
                
                file_put_contents( $htaccess_path, $htaccess );
                $http_code = false;
                $http_codes = WSKO_Class_Helper::get_http_status_codes( $home_url, array(), false );
                if ( $http_codes && isset( $http_codes[0]['code'] ) ) {
                    $http_code = $http_codes[0]['code'];
                }
                
                if ( $http_code == 200 ) {
                    return true;
                } else {
                    
                    if ( $htaccess_exists ) {
                        file_put_contents( $htaccess_path, $backup_r );
                    } else {
                        unlink( $htaccess_path );
                    }
                    
                    return array(
                        'success' => false,
                        'msg'     => __( 'Your changes have made the home page inaccessible! They were reverted for security reasons.', 'wsko' ),
                    );
                }
            
            }
        
        }
    
    }
    
    public function action_add_redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $comp = ( isset( $_POST['comp'] ) ? sanitize_text_field( $_POST['comp'] ) : false );
        $comp_to = ( isset( $_POST['comp_to'] ) ? sanitize_text_field( $_POST['comp_to'] ) : false );
        $type = ( isset( $_POST['type'] ) ? intval( $_POST['type'] ) : false );
        $redirect_to = ( isset( $_POST['redirect_to'] ) ? sanitize_text_field( $_POST['redirect_to'] ) : false );
        $page = ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : false );
        
        if ( $page && $redirect_to && $comp && $comp_to && $type ) {
            
            if ( $comp == 'exact' ) {
                $comp_to = 'exact';
                $page_s = WSKO_Class_Helper::format_url( $page );
                $redirect_to_s = WSKO_Class_Helper::format_url( $redirect_to );
                if ( $page_s == $redirect_to_s ) {
                    return array(
                        'success' => false,
                        'msg'     => __( 'You are about to create a redirect loop!', 'wsko' ),
                    );
                }
            } else {
                if ( $page == $redirect_to ) {
                    return array(
                        'success' => false,
                        'msg'     => __( 'You are about to create a redirect loop!', 'wsko' ),
                    );
                }
            }
            
            WSKO_Class_Onpage::add_redirect(
                $page,
                $comp,
                $redirect_to,
                $comp_to,
                $type
            );
            return true;
        }
    
    }
    
    public function action_check_redirect()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $url = ( isset( $_POST['url'] ) ? esc_url( $_POST['url'] ) : false );
        $status_check = ( isset( $_POST['status_check'] ) && $_POST['status_check'] && $_POST['status_check'] !== 'false' ? true : false );
        $view = false;
        if ( $url ) {
            $view = WSKO_Class_Template::render_template( 'tools/view-url-check.php', array(
                'url'          => $url,
                'status_check' => $status_check,
            ), true );
        }
        if ( $view ) {
            return array(
                'success' => true,
                'view'    => $view,
            );
        }
    }
    
    public function action_remove_redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $redirects = ( isset( $_POST['redirects'] ) ? ( is_array( $_POST['redirects'] ) ? array_map( 'sanitize_text_field', $_POST['redirects'] ) : sanitize_text_field( $_POST['redirects'] ) ) : false );
        if ( ($redirects || $redirects == '0') && !is_array( $redirects ) ) {
            $redirects = array( $redirects );
        }
        
        if ( $redirects ) {
            WSKO_Class_Onpage::remove_redirects( $redirects );
            return array(
                'success'   => true,
                'redirects' => $redirects,
            );
        }
    
    }
    
    public function action_remove_page_redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $post = ( isset( $_POST['post'] ) ? intval( $_POST['post'] ) : false );
        
        if ( $post ) {
            WSKO_Class_Onpage::remove_page_redirects( $post );
            return true;
        }
    
    }
    
    public function action_update_automatic_redirect()
    {
        if ( !$this->can_execute_action( WSKO_Class_Core::is_demo() ) ) {
            return false;
        }
        $activate = ( isset( $_POST['activate'] ) && $_POST['activate'] && $_POST['activate'] != 'false' ? true : false );
        $type = ( isset( $_POST['type'] ) ? intval( $_POST['type'] ) : "" );
        $custom = ( isset( $_POST['custom'] ) ? sanitize_text_field( $_POST['custom'] ) : "" );
        
        if ( $type ) {
            WSKO_Class_Onpage::set_redirect_404( $activate, $type, $custom );
            return array(
                'success'          => true,
                'dynamic_elements' => array(
                '#wsko_404_to_301_status' => WSKO_Class_Template::render_badge( $activate, array(), true ),
            ),
            );
        }
    
    }
    
    public function action_change_sitemap()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $res = WSKO_Class_Onpage::generate_sitemap();
        if ( $res ) {
            return true;
        }
    }
    
    public function action_update_sitemap()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        $types = array();
        if ( isset( $_POST['types'] ) && is_array( $_POST['types'] ) && $_POST['types'] ) {
            foreach ( $_POST['types'] as $type ) {
                $types[sanitize_text_field( $type['name'] )] = array(
                    'freq' => sanitize_text_field( $type['freq'] ),
                    'prio' => floatval( str_replace( ',', '.', $type['prio'] ) ),
                );
            }
        }
        $taxs = array();
        if ( isset( $_POST['tax'] ) && is_array( $_POST['tax'] ) && $_POST['tax'] ) {
            foreach ( $_POST['tax'] as $tax ) {
                $taxs[sanitize_text_field( $tax['name'] )] = array(
                    'freq' => sanitize_text_field( $tax['freq'] ),
                    'prio' => floatval( str_replace( ',', '.', $tax['prio'] ) ),
                );
            }
        }
        $stati = array();
        if ( isset( $_POST['stati'] ) && is_array( $_POST['stati'] ) && $_POST['stati'] ) {
            $stati = array_map( 'sanitize_text_field', $_POST['stati'] );
        }
        $gen_params = WSKO_Class_Onpage::get_sitemap_params();
        $gen_params['types'] = $types;
        $gen_params['tax'] = $taxs;
        $gen_params['stati'] = $stati;
        WSKO_Class_Onpage::set_sitemap_params( $gen_params );
        
        if ( isset( $_POST['ping'] ) && $_POST['ping'] && $_POST['ping'] != 'false' ) {
            WSKO_Class_Core::save_setting( 'automatic_sitemap_ping', true );
        } else {
            WSKO_Class_Core::save_setting( 'automatic_sitemap_ping', false );
        }
        
        $sitemap_active = false;
        
        if ( isset( $_POST['auto_generation'] ) && $_POST['auto_generation'] && $_POST['auto_generation'] != 'false' ) {
            $sitemap_active = true;
            WSKO_Class_Core::save_setting( 'automatic_sitemap', true );
            WSKO_Class_Crons::bind_sitemap_generation();
        } else {
            WSKO_Class_Core::save_setting( 'automatic_sitemap', false );
            WSKO_Class_Crons::unbind_sitemap_generation();
        }
        
        //$res = WSKO_Class_Onpage::generate_sitemap();
        //if ($res)
        //{
        return array(
            'success'          => true,
            'dynamic_elements' => array(
            '#wsko_sitemap_status_beacon' => WSKO_Class_Template::render_badge( $sitemap_active, array(
            'class' => 'pull-right',
            'title' => ( $sitemap_active ? __( 'Active - Last update', 'wsko' ) . ' ' . (( isset( $onpage_data['last_sitemap_generation'] ) ? date( 'd M, Y H:i', $onpage_data['last_sitemap_generation'] ) : __( 'Never', 'wsko' ) )) : __( 'Inactive', 'wsko' ) ),
            'style' => 'float: none !important; margin: 0px; margin-left: 10px;',
        ), true ),
        ),
        );
        //}
    }
    
    public function action_update_sitemap_real()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $res = WSKO_Class_Onpage::generate_sitemap();
        if ( $res ) {
            return true;
        }
    }
    
    public function action_upload_sitemap()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        $res = WSKO_Class_Onpage::upload_sitemap();
        if ( $res ) {
            return true;
        }
    }
    
    public function action_get_link_popup()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $url = ( isset( $_POST['url'] ) ? esc_url( $_POST['url'] ) : false );
        $ext = ( isset( $_POST['ext'] ) && $_POST['ext'] ? true : false );
        $rec = ( isset( $_POST['rec'] ) && $_POST['rec'] ? true : false );
    }
    
    public function action_get_widget_preview()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $widget = ( isset( $_POST['widget'] ) ? sanitize_text_field( $_POST['widget'] ) : false );
        if ( $widget ) {
            switch ( $widget ) {
                case 'breacrumbs':
                    $view = '<p class="wsko-label-sm">' . __( 'Post Preview', 'wsko' ) . '</p>';
                    $view .= do_shortcode( '[bst_breadcrumbs override_type="post" override_arg="' . WSKO_Class_Helper::get_random_post( 'page', 1, 'ids' ) . '"]' );
                    $view .= '<p class="wsko-label-sm">' . __( 'Taxonomy Preview', 'wsko' ) . '</p>';
                    $rand_term = WSKO_Class_Helper::get_random_term( 'category', 1 );
                    $view .= do_shortcode( '[bst_breadcrumbs override_type="term" override_arg="' . (( $rand_term ? $rand_term->taxonomy . ':' . $rand_term->term_id : '' )) . '"]' );
                    $view .= '<p class="wsko-label-sm">' . __( 'Archives Preview', 'wsko' ) . '</p>';
                    $view .= do_shortcode( '[bst_breadcrumbs override_type="post_type" override_arg="page"]' );
                    return array(
                        'success' => true,
                        'view'    => $view,
                    );
                case 'content_table':
                    $view = '<div id="wsko_content_table_source" style="display:none">
						<h1>' . __( 'Example Heading H1', 'wsko' ) . '</h1>
						Lorem ipsum
						<h2>' . __( 'Example Sub-Heading H2', 'wsko' ) . '</h2>
						Lorem ipsum
						<h3>' . __( 'Example Sub-Heading H3', 'wsko' ) . '</h3>
						Lorem ipsum
						<h2>' . __( 'Example Sub-Heading H2', 'wsko' ) . '</h2>
						Lorem ipsum
						<h2>' . __( 'Example Sub-Heading H2', 'wsko' ) . '</h2>
						Lorem ipsum
						<h2>' . __( 'Example Sub-Heading H2', 'wsko' ) . '</h2>
						Lorem ipsum
					</div>
					<div id="wsko_content_table_target"></div>' . do_shortcode( '[bst_content_table source="#wsko_content_table_source" target="#wsko_content_table_target"]' );
                    return array(
                        'success' => true,
                        'view'    => $view,
                    );
            }
        }
    }
    
    public function action_get_rich_snippet_config()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
    }
    
    public function action_create_rich_snippet()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : false );
        $post = ( isset( $_POST['post'] ) ? intval( $_POST['post'] ) : false );
        $location = ( isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : false );
        $post_types = ( isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', $_POST['post_types'] ) : array() );
        $posts = ( isset( $_POST['posts'] ) && is_array( $_POST['posts'] ) ? array_map( 'sanitize_text_field', $_POST['posts'] ) : array() );
        $data = ( isset( $_POST['data'] ) ? $_POST['data'] : array() );
        $data_s = false;
        
        if ( $data ) {
            parse_str( $data, $data_s );
            
            if ( $data_s && is_array( $data_s ) ) {
                $data_s = WSKO_Class_Helper::sanitize_array( $data_s, 'sanitize_text_field', true );
            } else {
                $data_s = false;
            }
        
        }
    
    }
    
    public function action_save_rich_snippet_config()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $location = ( isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : false );
        $post = ( isset( $_POST['post'] ) ? intval( $_POST['post'] ) : false );
        $key = ( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : false );
        $data = ( isset( $_POST['data'] ) ? $_POST['data'] : array() );
        $data_s = false;
        
        if ( $data ) {
            parse_str( $data, $data_s );
            
            if ( $data_s && is_array( $data_s ) ) {
                $data_s = WSKO_Class_Helper::sanitize_array( $data_s, 'sanitize_text_field', true );
            } else {
                $data_s = false;
            }
        
        }
        
        if ( $data_s ) {
            
            if ( $post ) {
                WSKO_Class_Snippets::save_snippet(
                    'post_id',
                    $key,
                    $data_s,
                    array(
                    'post' => $post,
                )
                );
                return true;
            } else {
                WSKO_Class_Snippets::save_snippet( $location, $key, $data_s );
                return true;
            }
        
        }
        return false;
    }
    
    public function action_delete_rich_snippet()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $location = ( isset( $_POST['location'] ) ? sanitize_text_field( $_POST['location'] ) : false );
        $post = ( isset( $_POST['post'] ) ? intval( $_POST['post'] ) : false );
        $key = ( isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : false );
        
        if ( $post ) {
            WSKO_Class_Snippets::delete_snippet( 'post_id', $key, array(
                'post' => $post,
            ) );
            return true;
        } else {
            WSKO_Class_Snippets::delete_snippet( $location, $key );
            return true;
        }
        
        return false;
    }
    
    public function _is_accessible( $subpage = false )
    {
        if ( $subpage == "metas" || $subpage == "rich_snippets" || $subpage == "widgets" || $subpage == "links" || $subpage == "sitemap" ) {
            return WSKO_Class_Onpage::seo_plugins_disabled();
        }
        return true;
    }
    
    //Singleton
    static  $instance ;
}
WSKO_Controller_Tools::init_controller();