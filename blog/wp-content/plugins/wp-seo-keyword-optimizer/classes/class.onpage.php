<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Onpage
{
    public static function seo_plugins_disabled()
    {
        return !WSKO_Class_Compatibility::is_seo_plugin_active();
    }
    
    public static function register_hooks()
    {
        $inst = WSKO_Class_Onpage::get_instance();
        
        if ( WSKO_Class_Core::is_configured() && WSKO_Class_Onpage::seo_plugins_disabled() ) {
            add_filter( 'pre_get_document_title', array( $inst, 'get_seo_title' ), 20 );
            add_filter( 'wp_title', array( $inst, 'get_seo_title' ), 20 );
            add_action( 'wp_head', array( $inst, 'get_seo_head' ) );
            remove_action( 'wp_head', 'rel_canonical' );
            //remove default canonical
            if ( get_option( 'blog_public' ) ) {
                //if search engines allowed
                remove_action( 'wp_head', 'noindex', 1 );
            }
            //remove default noindex
            add_action( 'template_redirect', array( $inst, 'redirect_pages' ), 0 );
            //add_action('redirect_canonical', array($inst, 'redirect_pages'), 1);
            add_action( 'save_post', function ( $post_id ) {
                $post_type = get_post_type( $post_id );
                $post_types = get_post_types( array(
                    'public' => true,
                ), 'names' );
                if ( in_array( $post_type, $post_types ) ) {
                    WSKO_Class_Core::save_option( 'sitemap_dirty', true );
                }
                WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'save' );
            } );
            add_action(
                'transition_post_status',
                function ( $new_status, $old_status, $post ) {
                
                if ( $new_status != $old_status ) {
                    $post_type = get_post_type( $post->ID );
                    $post_types = get_post_types( array(
                        'public' => true,
                    ), 'names' );
                    if ( in_array( $post_type, $post_types ) ) {
                        WSKO_Class_Core::save_option( 'sitemap_dirty', true );
                    }
                }
            
            },
                10,
                3
            );
            add_filter(
                'register_post_type_args',
                array( $inst, 'change_post_type_slugs' ),
                1000,
                2
            );
            add_filter(
                'register_taxonomy_args',
                array( $inst, 'change_taxonomy_slugs' ),
                1000,
                3
            );
            //add_filter('pre_get_posts', array($inst, 'include_pages_without_slug'), 10);
            //add_filter('parse_query', array($inst, 'include_pages_without_slug_args'), 10);
            add_filter( 'request', array( $inst, 'include_pages_without_slug_args' ), 1000 );
            add_filter( 'single_template', array( $inst, 'redirect_single_templates' ), 10 );
            add_filter(
                'wp_insert_post_data',
                array( $inst, 'create_auto_post_redirects' ),
                10,
                2
            );
            //create redirect from old link
            add_action(
                'save_post',
                array( $inst, 'check_auto_post_redirects' ),
                10,
                3
            );
            //check new url and set new target or delete redirect loop
            add_action(
                'delete_post',
                array( $inst, 'remove_auto_post_redirects' ),
                10,
                1
            );
            //delete redirects
        }
    
    }
    
    public function check_auto_post_redirects( $post_id, $post_args, $update )
    {
        
        if ( $post_id && $update ) {
            $blocked_stati = WSKO_Class_Onpage::get_blocked_post_stati( 'redirects' );
            $old_post = get_post( $post_id );
            if ( $old_post && !wp_is_post_revision( $post_id ) && !in_array( $old_post->post_status, $blocked_stati ) ) {
                WSKO_Class_Onpage::update_automatic_redirects(
                    'post_id',
                    $post_id,
                    get_permalink( $post_id ),
                    false
                );
            }
        }
    
    }
    
    public function create_auto_post_redirects( $post_args, $postarr )
    {
        
        if ( WSKO_Class_Core::get_setting( 'auto_post_slug_redirects' ) ) {
            $blocked_stati = WSKO_Class_Onpage::get_blocked_post_stati( 'redirects' );
            $post_id = false;
            if ( isset( $postarr['ID'] ) && $postarr['ID'] ) {
                $post_id = $postarr['ID'];
            }
            
            if ( $post_id ) {
                $old_post = get_post( $post_id );
                
                if ( $old_post && !wp_is_post_revision( $post_id ) && isset( $postarr['post_status'] ) && isset( $post_args['post_status'] ) && $post_args['post_status'] && !in_array( $post_args['post_status'], $blocked_stati ) && !in_array( $old_post->post_status, $blocked_stati ) ) {
                    $old_link = get_permalink( $post_id );
                    WSKO_Class_Onpage::update_automatic_redirects(
                        'post_id',
                        $post_id,
                        false,
                        true
                    );
                }
            
            }
        
        }
        
        return $post_args;
    }
    
    public function remove_auto_post_redirects( $post_id )
    {
        WSKO_Class_Onpage::remove_auto_redirect( 'post_id', $post_id );
    }
    
    public function redirect_single_templates( $single_template )
    {
        global  $post ;
        
        if ( $post ) {
            $pt = get_post_type_object( $post->post_type );
            
            if ( $pt ) {
                //$slug = $pt->rewrite ? $pt->rewrite['slug'] : $post->post_type;
                $new_tpl = get_stylesheet_directory() . '/single-' . $post->post_type . '.php';
                if ( file_exists( $new_tpl ) ) {
                    $single_template = $new_tpl;
                }
            }
        
        }
        
        return $single_template;
    }
    
    public function change_post_type_slugs( $args, $post_type )
    {
        $post_type_meta = WSKO_Class_Onpage::get_meta_object( $post_type, 'post_type' );
        if ( $post_type_meta ) {
            /*if (isset($post_type_meta['hide_slug']) && $post_type_meta['hide_slug'])
            		{
            			if (isset($post_type_meta['slug']) && $post_type_meta['slug'])
            				$args['has_archive'] = $post_type_meta['slug'];
            			else if (isset($args['rewrite']['slug']))
            				$args['has_archive'] = $args['rewrite']['slug'];
            			else
            				$args['has_archive'] = $post_type;
            			
            			$args['rewrite']['slug'] = '/';
            			$args['rewrite']['with_front'] = false;
            		}
            		else*/
            if ( isset( $post_type_meta['slug'] ) && $post_type_meta['slug'] ) {
                $args['rewrite']['slug'] = $post_type_meta['slug'];
            }
        }
        return $args;
    }
    
    public function change_taxonomy_slugs( $args, $tax, $obj )
    {
        $tax_meta = WSKO_Class_Onpage::get_meta_object( $tax, 'post_tax' );
        if ( $tax_meta ) {
            if ( isset( $tax_meta['slug'] ) && $tax_meta['slug'] ) {
                $args['rewrite']['slug'] = $tax_meta['slug'];
            }
        }
        return $args;
    }
    
    public function include_pages_without_slug_args( $wp_query )
    {
        if ( is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX || defined( 'DOING_CRON' ) && DOING_CRON ) {
            return $wp_query;
        }
        global  $wsko_first_query ;
        if ( $wsko_first_query ) {
            return $wp_query;
        }
        //$wsko_first_query = true;
        $hide_slug = WSKO_Class_Core::get_setting( 'hide_category_slug' );
        $is_cat = false;
        
        if ( $hide_slug && (isset( $wp_query['name'] ) || isset( $wp_query['pagename'] )) ) {
            $page_t = ( isset( $wp_query['name'] ) ? $wp_query['name'] : $wp_query['pagename'] );
            
            if ( $ct = WSKO_Class_Helper::is_term_url( $page_t, 'category' ) ) {
                $wp_query = array(
                    'category_name' => $ct->slug,
                );
                $is_cat = true;
            }
        
        }
        
        /*if (!$is_cat)
        		{
        			$url = trim(WSKO_Class_Helper::get_current_url(false), '/');
        			$post_types = WSKO_Class_Onpage::get_post_types_without_slug();
        			//unset($post_types['post']);
        			//unset($post_types['page']);
        			foreach($post_types as $pt)
        			{
        				$page = get_page_by_path($url, OBJECT, $pt);
        				if ($page)
        				{
        					if ($pt === 'page')
        						$wp_query = array('page' => '', 'pagename' => $url);
        					else if ($pt === 'post')
        						$wp_query = array('page' => '', 'name' => $url);
        					else
        						$wp_query = array('page' => '', 'post_type' => $pt, $pt => $url, $pt.'name' => $url, 'name' => $url);
        					break;
        				}
        			}
        		}*/
        return $wp_query;
    }
    
    public function get_post_types_without_slug()
    {
        $post_types_a = WSKO_Class_Onpage::get_special_meta_object( 'post_type' );
        $post_types = array( 'post', 'page' );
        foreach ( $post_types_a as $k => $pt ) {
            
            if ( $k && isset( $pt['hide_slug'] ) && $pt['hide_slug'] ) {
                $post_types[] = $k;
            } else {
                if ( ($k === 'post' || $k === 'page') && (!isset( $pt['hide_slug'] ) || !$pt['hide_slug']) ) {
                    unset( $post_types[( $k === 'post' ? 0 : 1 )] );
                }
            }
        
        }
        return $post_types;
    }
    
    public function redirect_pages()
    {
        if ( is_admin() || defined( 'DOING_AJAX' ) && DOING_AJAX || defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;
        }
        
        if ( is_singular() && is_attachment() ) {
            global  $post ;
            
            if ( $post ) {
                $type = WSKO_Class_Core::get_setting( 'redirect_attachment_pages' );
                switch ( $type ) {
                    case 'post':
                        if ( $post->post_parent ) {
                            wp_redirect( get_permalink( $post->post_parent ), 301 );
                        }
                        break;
                    case 'file':
                        wp_redirect( wp_get_attachment_url( $post->ID ), 301 );
                        break;
                }
            }
        
        }
        
        $hide_slug = WSKO_Class_Core::get_setting( 'hide_category_slug' );
        $hide_slug_redirect = WSKO_Class_Core::get_setting( 'hide_category_slug_redirect' );
        if ( $hide_slug_redirect ) {
            
            if ( $hide_slug ) {
                
                if ( !is_home() && is_category() ) {
                    $target = home_url( WSKO_Class_Helper::get_term_url( get_term_by(
                        'slug',
                        get_query_var( 'category_name' ),
                        'category',
                        OBJECT
                    ), 'category' ) );
                    
                    if ( trim( $target, '/' ) != trim( WSKO_Class_Helper::get_current_url( true ), '/' ) ) {
                        wp_redirect( $target, 301 );
                        exit;
                    }
                
                }
            
            } else {
                $url = trim( WSKO_Class_Helper::get_current_url( false ), '/' );
                
                if ( $ct = WSKO_Class_Helper::is_term_url( $url, 'category' ) ) {
                    wp_redirect( get_term_link( $ct, 'category' ), 301 );
                    exit;
                }
            
            }
        
        }
        $redirect = WSKO_Class_Onpage::get_redirect_for_url( WSKO_Class_Helper::get_current_url() );
        
        if ( $redirect ) {
            
            if ( $redirect['code'] == 2 ) {
                //old system
                $redirect['code'] = 302;
            } else {
                if ( $redirect['code'] == 1 ) {
                    $redirect['code'] = 301;
                }
            }
            
            wp_redirect( $redirect['to'], $redirect['code'] );
            exit;
        }
    
    }
    
    public static function get_redirect_for_url( $current_url, $test = false )
    {
        $current_url = WSKO_Class_Helper::format_url( $current_url, true );
        $reds = array();
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        $post_id = false;
        
        if ( $test ) {
            $post_id = WSKO_Class_Helper::url_to_postid( $current_url );
        } else {
            global  $post ;
            if ( $post && $post->ID && is_singular() ) {
                $post_id = $post->ID;
            }
        }
        
        
        if ( $post_id && $post_id != get_option( 'page_on_front' ) && $post_id != get_option( 'page_for_posts' ) ) {
            $page_redirect = WSKO_Class_Onpage::get_page_redirect( $post_id );
            
            if ( $page_redirect ) {
                $link = WSKO_Class_Helper::format_url( $page_redirect['to'] );
                $reds[] = $res = array(
                    'type' => 'post',
                    'to'   => $link,
                    'code' => $page_redirect['type'],
                    'data' => ( $test ? array(
                    'post' => $post_id,
                    're'   => $page_redirect,
                ) : false ),
                );
                if ( !$test ) {
                    return $res;
                }
            }
        
        }
        
        if ( isset( $redirect_data['redirects'] ) && $redirect_data['redirects'] ) {
            foreach ( $redirect_data['redirects'] as $key => $redirect ) {
                $hit = false;
                $page_link = WSKO_Class_Helper::format_url( $redirect['page'], true );
                switch ( $redirect['comp'] ) {
                    //case 'starts_with': if (WSKO_Class_Helper::starts_with($current_url, $page_link)) $hit = true; break;
                    case 'exact':
                        if ( $page_link === $current_url ) {
                            $hit = true;
                        }
                        break;
                    case 'contains':
                        if ( stripos( $current_url, $redirect['page'] ) !== false ) {
                            $hit = true;
                        }
                        break;
                        //case 'replace': if (stripos($current_url, $redirect['page']) !== false) { $hit = true; $redirect['target'] = str_ireplace($redirect['page'], $redirect['target'], $current_url); } break;
                }
                
                if ( $hit ) {
                    if ( isset( $redirect['comp_to'] ) && $redirect['comp_to'] === 'replace' ) {
                        $redirect['target'] = str_ireplace( $redirect['page'], $redirect['target'], WSKO_Class_Helper::get_current_url() );
                    }
                    
                    if ( $redirect['type'] == 2 ) {
                        //old system
                        $redirect['type'] = 302;
                    } else {
                        if ( $redirect['type'] == 1 ) {
                            $redirect['type'] = 301;
                        }
                    }
                    
                    $reds[] = $res = array(
                        'type' => 'custom',
                        'to'   => WSKO_Class_Helper::format_url( $redirect['target'] ),
                        'code' => $redirect['type'],
                        'data' => ( $test ? array(
                        'key' => $key,
                        're'  => $redirect,
                    ) : false ),
                    );
                    if ( !$test ) {
                        return $res;
                    }
                }
            
            }
        }
        
        if ( isset( $redirect_data['auto_redirects'] ) && $redirect_data['auto_redirects'] ) {
            $blocked_stati = WSKO_Class_Onpage::get_blocked_post_stati( 'redirects' );
            foreach ( $redirect_data['auto_redirects'] as $type => $types ) {
                foreach ( $types as $arg => $redirects ) {
                    
                    if ( $type == 'post_id' ) {
                        foreach ( $redirects as $key => $link ) {
                            $link = WSKO_Class_Helper::format_url( $link, true );
                            if ( $link === $current_url ) {
                                
                                if ( in_array( get_post_status( $arg ), $blocked_stati ) ) {
                                    $reds[] = $res = array(
                                        'type' => 'auto_post',
                                        'to'   => get_permalink( $arg ),
                                        'code' => 301,
                                        'data' => ( $test ? array(
                                        'type' => $type,
                                        'arg'  => $arg,
                                        'key'  => $key,
                                        'from' => $link,
                                        're'   => $redirects,
                                    ) : false ),
                                    );
                                    if ( !$test ) {
                                        return $res;
                                    }
                                }
                            
                            }
                        }
                    } else {
                        $sources = $redirects['source'];
                        if ( $sources ) {
                            foreach ( $sources as $slug => $link_snapshot ) {
                                foreach ( $link_snapshot as $p => $link ) {
                                    $link = WSKO_Class_Helper::format_url( $link, true );
                                    if ( $link === $current_url ) {
                                        
                                        if ( in_array( get_post_status( $p ), $blocked_stati ) ) {
                                            $reds[] = $res = array(
                                                'type' => 'auto_post_type',
                                                'to'   => ( $type == 'post_tax' ? get_term_link( intval( $p ), $arg ) : get_permalink( intval( $p ) ) ),
                                                'code' => 301,
                                                'data' => ( $test ? array(
                                                'type' => $type,
                                                'arg'  => $arg,
                                                'slug' => $slug,
                                                're'   => $redirects,
                                            ) : false ),
                                            );
                                            if ( !$test ) {
                                                return $res;
                                            }
                                        }
                                    
                                    }
                                }
                            }
                        }
                    }
                
                }
            }
        }
        
        if ( is_404() && isset( $redirect_data['redirect_404'] ) && $redirect_data['redirect_404']['activate'] && !$test ) {
            switch ( $redirect_data['redirect_404']['type'] ) {
                case '1':
                    $res = array(
                        'type' => '404',
                        'to'   => home_url(),
                        'code' => 301,
                    );
                    if ( !$test ) {
                        return $res;
                    }
                    break;
                case '2':
                    $res = array(
                        'type' => '404',
                        'to'   => WSKO_Class_Helper::get_host_base(),
                        'code' => 301,
                    );
                    if ( !$test ) {
                        return $res;
                    }
                    break;
                case '3':
                    $res = array(
                        'type' => '404',
                        'to'   => WSKO_Class_Helper::format_url( $redirect_data['redirect_404']['custom'] ),
                        'code' => 301,
                    );
                    if ( !$test ) {
                        return $res;
                    }
                    break;
                case '4':
                    $res = array(
                        'type' => '404',
                        'to'   => WSKO_Class_Helper::get_parent_url( $current_url, 1 ),
                        'code' => 301,
                    );
                    if ( !$test ) {
                        return $res;
                    }
                    break;
            }
        }
        if ( $test ) {
            return $reds;
        }
    }
    
    public static function get_link_snapshot( $type, $arg )
    {
        $res = array();
        
        if ( $type == 'post_type' ) {
            $query = new WP_Query( array(
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'post_status'    => 'publish',
                'post_type'      => $arg,
            ) );
            foreach ( $query->posts as $p ) {
                $res[$p] = get_permalink( $p );
            }
        } else {
            
            if ( $type == 'post_tax' ) {
                //$args['tax_query'] = array(array('value' => $arg, 'compare' => 'EXISTS'));
                $query = get_terms( array(
                    'taxonomy'   => $arg,
                    'hide_empty' => false,
                ) );
                foreach ( $query as $t ) {
                    $res[$t->term_id] = get_term_link( $t, $arg );
                }
            }
        
        }
        
        return $res;
    }
    
    public function get_seo_title( $old_title )
    {
        if ( !WSKO_Class_Onpage::seo_plugins_disabled() ) {
            return;
        }
        global  $post ;
        $metas = WSKO_Class_Onpage::get_post_metas();
        $post_r = false;
        
        if ( is_front_page() && is_home() ) {
            if ( $t_post = get_option( 'page_on_front' ) ) {
                $post_r = get_post( $t_post );
            }
        } else {
            
            if ( is_front_page() ) {
                if ( $t_post = get_option( 'page_on_front' ) ) {
                    $post_r = get_post( $t_post );
                }
            } else {
                
                if ( is_home() ) {
                    if ( $t_post = get_option( 'page_for_posts' ) ) {
                        $post_r = get_post( $t_post );
                    }
                } else {
                    if ( is_singular() ) {
                        $post_r = $post;
                    }
                }
            
            }
        
        }
        
        if ( $post_r ) {
            $overrides = array(
                'post' => $post_r->ID,
            );
        }
        
        if ( $metas && isset( $metas['title'] ) && $metas['title'] ) {
            $meta = WSKO_Class_Onpage::calculate_meta( $metas['title'], $overrides );
            if ( $meta ) {
                return $meta;
            }
        }
        
        
        if ( is_tax() || is_category() || is_tag() ) {
            $queried_object = get_queried_object();
            
            if ( $queried_object && $queried_object->taxonomy ) {
                $taxonomy = $queried_object->taxonomy;
                $term = $queried_object->term_id;
                
                if ( $term ) {
                    $term = get_term_by( 'id', $term, $taxonomy );
                    return WSKO_Class_Helper::get_default_page_title( $term->name );
                } else {
                    $tax = get_taxonomy( $taxonomy );
                    return WSKO_Class_Helper::get_default_page_title( $tax->label );
                }
            
            }
        
        } else {
            if ( $post ) {
                return WSKO_Class_Helper::get_default_page_title( get_the_title( $post->ID ) );
            }
        }
        
        return $old_title;
    }
    
    public function get_seo_head()
    {
        if ( !WSKO_Class_Onpage::seo_plugins_disabled() ) {
            return;
        }
        global  $post ;
        $auto_snippet = WSKO_Class_Core::get_setting( 'auto_social_snippet' );
        $auto_thumbnail = WSKO_Class_Core::get_setting( 'auto_social_thumbnail' );
        $hide_canon = false;
        $m_title = "";
        $m_desc = "";
        $m_tw_title = "";
        $m_tw_desc = "";
        $m_tw_img = "";
        $m_tw_url = "";
        $m_og_title = "";
        $m_og_desc = "";
        $m_og_img = "";
        $m_og_url = "";
        $m_canon = "";
        $m_og_type = "";
        $metas = WSKO_Class_Onpage::get_post_metas();
        $post_r = false;
        
        if ( is_front_page() && is_home() ) {
            if ( $t_post = get_option( 'page_on_front' ) ) {
                $post_r = get_post( $t_post );
            }
        } else {
            
            if ( is_front_page() ) {
                if ( $t_post = get_option( 'page_on_front' ) ) {
                    $post_r = get_post( $t_post );
                }
            } else {
                
                if ( is_home() ) {
                    if ( $t_post = get_option( 'page_for_posts' ) ) {
                        $post_r = get_post( $t_post );
                    }
                } else {
                    if ( is_singular() ) {
                        $post_r = $post;
                    }
                }
            
            }
        
        }
        
        if ( $post_r ) {
            $overrides = array(
                'post' => $post_r->ID,
            );
        }
        
        if ( $metas ) {
            
            if ( isset( $metas['desc'] ) && $metas['desc'] ) {
                $meta = WSKO_Class_Onpage::calculate_meta( $metas['desc'], $overrides );
                if ( $meta ) {
                    $m_desc = $meta;
                }
            }
            
            
            if ( isset( $metas['og_title'] ) && $metas['og_title'] ) {
                $meta = WSKO_Class_Onpage::calculate_meta( $metas['og_title'], $overrides );
                if ( $meta ) {
                    $m_og_title = $meta;
                }
            } else {
                
                if ( isset( $metas['title'] ) && $metas['title'] ) {
                    $meta = WSKO_Class_Onpage::calculate_meta( $metas['title'], $overrides );
                    if ( $meta ) {
                        $m_og_title = $meta;
                    }
                }
            
            }
            
            
            if ( isset( $metas['og_desc'] ) && $metas['og_desc'] ) {
                $meta = WSKO_Class_Onpage::calculate_meta( $metas['og_desc'], $overrides );
                if ( $meta ) {
                    $m_og_desc = $meta;
                }
            } else {
                
                if ( isset( $metas['desc'] ) && $metas['desc'] ) {
                    $meta = WSKO_Class_Onpage::calculate_meta( $metas['desc'], $overrides );
                    if ( $meta ) {
                        $m_og_desc = $meta;
                    }
                }
            
            }
            
            if ( isset( $metas['og_img'] ) && $metas['og_img'] ) {
                $m_og_img = $metas['og_img'];
            }
            
            if ( isset( $metas['tw_title'] ) && $metas['tw_title'] ) {
                $meta = WSKO_Class_Onpage::calculate_meta( $metas['tw_title'], $overrides );
                if ( $meta ) {
                    $m_tw_title = $meta;
                }
            } else {
                
                if ( isset( $metas['title'] ) && $metas['title'] ) {
                    $meta = WSKO_Class_Onpage::calculate_meta( $metas['title'], $overrides );
                    if ( $meta ) {
                        $m_tw_title = $meta;
                    }
                }
            
            }
            
            
            if ( isset( $metas['tw_desc'] ) && $metas['tw_desc'] ) {
                $meta = WSKO_Class_Onpage::calculate_meta( $metas['tw_desc'], $overrides );
                if ( $meta ) {
                    $m_tw_desc = $meta;
                }
            } else {
                
                if ( isset( $metas['desc'] ) && $metas['desc'] ) {
                    $meta = WSKO_Class_Onpage::calculate_meta( $metas['desc'], $overrides );
                    if ( $meta ) {
                        $m_tw_desc = $meta;
                    }
                }
            
            }
            
            if ( isset( $metas['tw_img'] ) && $metas['tw_img'] ) {
                $m_tw_img = $metas['tw_img'];
            }
            if ( isset( $metas['robots'] ) ) {
                switch ( $metas['robots'] ) {
                    case 1:
                        echo  '<meta name="robots" content="nofollow">' ;
                        break;
                    case 2:
                        echo  '<meta name="robots" content="noindex">' ;
                        $hide_canon = true;
                        break;
                    case 3:
                        echo  '<meta name="robots" content="noindex,nofollow">' ;
                        $hide_canon = true;
                        break;
                }
            }
            if ( isset( $metas['canon'] ) ) {
                switch ( $metas['canon']['type'] ) {
                    case 0:
                        $m_canon = -1;
                        break;
                        //no tag
                    //no tag
                    case 1:
                        break;
                        //auto
                    //auto
                    case 2:
                        $m_canon = get_permalink( $metas['canon']['arg'] );
                        break;
                    case 3:
                        $m_canon = WSKO_Class_Helper::format_url( $metas['canon']['arg'] );
                        break;
                }
            }
        }
        
        if ( !$m_canon && WSKO_Class_Core::get_setting( 'auto_canonical' ) ) {
            
            if ( is_tax() || is_category() || is_tag() ) {
                $queried_object = get_queried_object();
                
                if ( $queried_object && $queried_object->taxonomy ) {
                    $taxonomy = $queried_object->taxonomy;
                    $term = $queried_object->term_id;
                    if ( $term ) {
                        $m_canon = get_term_link( $term );
                    }
                }
            
            }
        
        }
        
        if ( is_singular() && $post && $post->ID ) {
            //$content = substr(WSKO_Class_Helper::sanitize_meta($post->post_content, 'the_content'), 0, WSKO_ONPAGE_DESC_MAX-10);
            if ( !$m_title ) {
                $m_title = WSKO_Class_Helper::sanitize_meta( get_the_title( $post->ID ), false ) . ' - ' . get_bloginfo( 'name' );
            }
            //if (!$m_desc)
            //$m_desc = $content;
            
            if ( $auto_snippet ) {
                if ( !$m_tw_title ) {
                    $m_tw_title = $m_title;
                }
                if ( !$m_og_title ) {
                    $m_og_title = $m_title;
                }
                if ( !$m_tw_desc ) {
                    $m_tw_desc = $m_desc;
                }
                if ( !$m_og_desc ) {
                    $m_og_desc = $m_desc;
                }
            }
            
            
            if ( $auto_thumbnail && has_post_thumbnail( $post->ID ) ) {
                if ( ($auto_snippet || $m_tw_title || $m_tw_desc) && !$m_tw_img ) {
                    $m_tw_img = get_the_post_thumbnail_url( $post->ID );
                }
                if ( ($auto_snippet || $m_og_title || $m_og_desc) && !$m_og_img ) {
                    $m_og_img = get_the_post_thumbnail_url( $post->ID );
                }
            }
            
            if ( !$m_canon && WSKO_Class_Core::get_setting( 'auto_canonical' ) ) {
                $m_canon = get_permalink( $post->ID );
            }
            
            if ( $auto_snippet || $m_og_title || $m_og_desc ) {
                $m_og_url = get_permalink( $post->ID );
                
                if ( get_post_format( $post->ID ) ) {
                    $m_og_type = "article";
                } else {
                    $m_og_type = "website";
                }
            
            }
            
            if ( $auto_snippet || $m_tw_title || $m_tw_desc ) {
                $m_tw_url = get_permalink( $post->ID );
            }
        }
        
        if ( $m_desc ) {
            echo  '<meta name="description" content="' . esc_attr( $m_desc ) . '">' ;
        }
        if ( $m_tw_title ) {
            echo  '<meta name="twitter:title" content="' . esc_attr( $m_tw_title ) . '">' ;
        }
        if ( $m_tw_desc ) {
            echo  '<meta name="twitter:description" content="' . esc_attr( $m_tw_desc ) . '">' ;
        }
        if ( $m_tw_img ) {
            echo  '<meta name="twitter:image" content="' . esc_attr( $m_tw_img ) . '">' ;
        }
        if ( $m_og_title ) {
            echo  '<meta property="og:title" content="' . esc_attr( $m_og_title ) . '">' ;
        }
        if ( $m_og_desc ) {
            echo  '<meta property="og:description" content="' . esc_attr( $m_og_desc ) . '">' ;
        }
        if ( $m_og_img ) {
            echo  '<meta property="og:image" content="' . esc_attr( $m_og_img ) . '">' ;
        }
        if ( !$hide_canon ) {
            
            if ( $m_canon === -1 ) {
                //no canonical
            } else {
                
                if ( $m_canon ) {
                    echo  '<link rel="canonical" href="' . $m_canon . '"/>' ;
                } else {
                    if ( WSKO_Class_Core::get_setting( 'auto_canonical' ) ) {
                        echo  '<link rel="canonical" href="' . WSKO_Class_Helper::get_current_url() . '"/>' ;
                    }
                }
            
            }
        
        }
        
        if ( $m_tw_url ) {
            echo  '<meta name="twitter:url" content="' . esc_attr( $m_tw_url ) . '">' ;
        } else {
            if ( $auto_snippet ) {
                echo  '<meta name="twitter:url" content="' . WSKO_Class_Helper::get_current_url() . '">' ;
            }
        }
        
        
        if ( $m_og_url ) {
            echo  '<meta property="og:url" content="' . esc_attr( $m_og_url ) . '">' ;
        } else {
            if ( $auto_snippet ) {
                echo  '<meta property="og:url" content="' . WSKO_Class_Helper::get_current_url() . '">' ;
            }
        }
        
        if ( $m_og_type ) {
            echo  '<meta property="og:type" content="' . $m_og_type . '">' ;
        }
    }
    
    public static function get_onpage_data()
    {
        $wsko_data = WSKO_Class_Core::get_data();
        
        if ( !isset( $wsko_data['onpage_data'] ) || !is_array( $wsko_data['onpage_data'] ) ) {
            $onpage_data = array(
                'post_metas'      => array(),
                'post_type_metas' => array(),
            );
        } else {
            $onpage_data = $wsko_data['onpage_data'];
        }
        
        return $onpage_data;
    }
    
    public static function set_onpage_data( $data )
    {
        $wsko_data = WSKO_Class_Core::get_data();
        $wsko_data['onpage_data'] = $data;
        WSKO_Class_Core::save_data( $wsko_data );
    }
    
    public static function get_onpage_analysis( $refetch = false )
    {
        return WSKO_Class_Cache::get_wp_option( 'wsko_op_analysis', false, $refetch );
    }
    
    public static function save_onpage_analysis( $analysis )
    {
        WSKO_Class_Cache::save_wp_option( 'wsko_op_analysis', $analysis );
    }
    
    public static function clear_onpage_analysis()
    {
        WSKO_Class_Cache::remove_wp_option( 'wsko_op_analysis' );
    }
    
    public static function has_current_report()
    {
        $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
        if ( isset( $global_analysis['current_report']['started'] ) ) {
            return true;
        }
        return false;
    }
    
    public static function get_technical_seo_data( $post_id = false )
    {
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        
        if ( $post_id ) {
            if ( isset( $onpage_data['technical_seo'][$post_id] ) ) {
                return $onpage_data['technical_seo'][$post_id];
            }
        } else {
            if ( isset( $onpage_data['technical_seo'] ) ) {
                return $onpage_data['technical_seo'];
            }
        }
        
        return false;
    }
    
    public static function set_technical_seo_data( $post_id, $data )
    {
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        
        if ( isset( $onpage_data['technical_seo'] ) && is_array( $onpage_data['technical_seo'] ) ) {
            
            if ( $data ) {
                $onpage_data['technical_seo'][$post_id] = $data;
            } else {
                unset( $onpage_data['technical_seo'][$post_id] );
            }
        
        } else {
            if ( $data ) {
                $onpage_data['technical_seo'] = array(
                    $post_id => $data,
                );
            }
        }
        
        WSKO_Class_Onpage::set_onpage_data( $onpage_data );
    }
    
    public static function get_onpage_report(
        $post_id,
        $preview = false,
        $log_error = true,
        $reset_post = true
    )
    {
        try {
            $post_t = get_post( $post_id );
            
            if ( $post_t ) {
                global  $post ;
                $post = $post_t;
                setup_postdata( $post );
                $res = array(
                    'onpage_score' => 100,
                    'issues'       => array(),
                );
                $h1_pt = WSKO_Class_Helper::safe_explode( ",", WSKO_Class_Core::get_setting( 'onpage_title_h1' ) );
                $h1_outside = in_array( $post->post_type, $h1_pt );
                $link = get_permalink( $post_id );
                
                if ( $preview && $preview['post_slug'] ) {
                    $ends_with_dash = substr( $link, strlen( $link ) - 1 ) == '/';
                    $link = WSKO_Class_Helper::safe_explode( '/', rtrim( $link, '/' ) );
                    array_pop( $link );
                    $link[] = $preview['post_slug'];
                    $link = implode( '/', $link ) . (( $ends_with_dash ? '/' : '' ));
                }
                
                $res['link'] = $link;
                $meta_obj = false;
                $meta_obj_r = false;
                $is_meta_custom_title = false;
                $title = "";
                if ( $preview && $preview['post_title'] ) {
                    $title = $preview['post_title'];
                }
                $desc = false;
                $og_title = false;
                $og_desc = false;
                $og_img = false;
                $tw_title = false;
                $tw_desc = false;
                $tw_img = false;
                
                if ( WSKO_Class_Onpage::seo_plugins_disabled() ) {
                    $meta_obj = WSKO_Class_Onpage::get_meta_object( $post_id, 'post_id' );
                    $meta_obj_r = WSKO_Class_Onpage::get_meta_object( get_post_type( $post_id ), 'post_type' );
                } else {
                    $source = WSKO_Class_Compatibility::get_seo_plugin_preview( 'metas' );
                    
                    if ( $source ) {
                        $meta_obj = WSKO_Class_Compatibility::get_meta_object_ext( $post_id, 'post_id', $source );
                        $meta_obj_r = WSKO_Class_Compatibility::get_meta_object_ext( get_post_type( $post_id ), 'post_type', $source );
                    }
                
                }
                
                $overrides = array(
                    'tax'          => false,
                    'term'         => false,
                    'post'         => $post_id,
                    'post_title'   => ( $preview ? $preview['post_title'] : false ),
                    'post_content' => ( $preview ? $preview['post_content'] : false ),
                );
                
                if ( $meta_obj_r ) {
                    
                    if ( isset( $meta_obj_r['title'] ) && $meta_obj_r['title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['title'], $overrides );
                        if ( $meta ) {
                            $title = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['desc'] ) && $meta_obj_r['desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['desc'], $overrides );
                        if ( $meta ) {
                            $desc = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_title'] ) && $meta_obj_r['og_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_title'], $overrides );
                        if ( $meta ) {
                            $og_title = $meta;
                        }
                    } else {
                        $og_title = $title;
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_desc'] ) && $meta_obj_r['og_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_desc'], $overrides );
                        if ( $meta ) {
                            $og_desc = $meta;
                        }
                    } else {
                        $og_desc = $desc;
                    }
                    
                    
                    if ( isset( $meta_obj_r['og_img'] ) && $meta_obj_r['og_img'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['og_img'], $overrides );
                        if ( $meta ) {
                            $og_img = $meta;
                        }
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_title'] ) && $meta_obj_r['tw_title'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_title'], $overrides );
                        if ( $meta ) {
                            $tw_title = $meta;
                        }
                    } else {
                        $tw_title = $title;
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_desc'] ) && $meta_obj_r['tw_desc'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_desc'], $overrides );
                        if ( $meta ) {
                            $tw_desc = $meta;
                        }
                    } else {
                        $tw_desc = $desc;
                    }
                    
                    
                    if ( isset( $meta_obj_r['tw_img'] ) && $meta_obj_r['tw_img'] ) {
                        $meta = WSKO_Class_Onpage::calculate_meta( $meta_obj_r['tw_img'], $overrides );
                        if ( $meta ) {
                            $tw_img = $meta;
                        }
                    }
                
                }
                
                
                if ( $meta_obj ) {
                    
                    if ( isset( $meta_obj['title'] ) && $meta_obj['title'] ) {
                        $is_meta_custom_title = true;
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
                
                }
                
                /*$head = WSKO_Class_Helper::get_url_head($link);
                		$dom = new DOMDocument;
                		$dom->recover = TRUE;
                		@$dom->loadHTML($head);
                		$titles = $dom->getElementsByTagName('title');
                		if ($titles->length)
                			$title = WSKO_Class_Helper::convert_utf8($titles->item(0)->nodeValue);
                		//$title = WSKO_Class_Onpage::get_instance()->get_seo_title($title);
                		$metas = $dom->getElementsByTagName('meta');
                		foreach ($metas as $m)
                		{
                			if ($desc === false) { if ($m->getAttribute('name') == "description" || $m->getAttribute('property') == "description") { $desc = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($og_title === false) { if ($m->getAttribute('name') == "og:title" || $m->getAttribute('property') == "og:title") { $og_title = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($og_desc === false) { if ($m->getAttribute('name') == "og:description" || $m->getAttribute('property') == "og:description") { $og_desc = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($og_img === false) { if ($m->getAttribute('name') == "og:image" || $m->getAttribute('property') == "og:image") { $og_img = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($tw_title === false) { if ($m->getAttribute('name') == "twitter:title" || $m->getAttribute('property') == "twitter:title") { $tw_title = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($tw_desc === false) { if ($m->getAttribute('name') == "twitter:description" || $m->getAttribute('property') == "twitter:description") { $tw_desc = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                			if ($tw_img === false) { if ($m->getAttribute('name') == "twitter:image" || $m->getAttribute('property') == "twitter:image") { $tw_img = WSKO_Class_Helper::convert_utf8($m->getAttribute('content')); } }
                		}*/
                $res['meta_title'] = ( $title ? $title : "" );
                $res['meta_title_custom'] = $is_meta_custom_title;
                $res['meta_desc'] = ( $desc ? $desc : "" );
                $res['meta_og_title'] = ( $og_title ? $og_title : "" );
                $res['meta_og_desc'] = ( $og_desc ? $og_desc : "" );
                $res['meta_og_img'] = ( $og_img ? $og_img : "" );
                $res['meta_tw_title'] = ( $tw_title ? $tw_title : "" );
                $res['meta_tw_desc'] = ( $tw_desc ? $tw_desc : "" );
                $res['meta_tw_img'] = ( $tw_img ? $tw_img : "" );
                $title_length = ( $title ? mb_strlen( $title ) : 0 );
                $desc_length = ( $desc ? mb_strlen( $desc ) : 0 );
                if ( !$preview || !$preview['post_content'] ) {
                    //empty fetch (visual composer fix, first content is with faulty encoding on some environments)
                    $content = WSKO_Class_Helper::handle_shortcodes( ( $preview && $preview['post_content'] ? $preview['post_content'] : WSKO_Class_Helper::get_real_post_content( $post_id ) ) );
                }
                $content = WSKO_Class_Helper::handle_shortcodes( ( $preview && $preview['post_content'] ? $preview['post_content'] : WSKO_Class_Helper::get_real_post_content( $post_id ) ) );
                $content_plain = WSKO_Class_Helper::get_plain_string( $content );
                $content_length = ( $content_plain ? mb_strlen( $content_plain ) : 0 );
                $word_count = WSKO_Class_Helper::get_word_count( $content_plain );
                $res['issues']['html_errors'] = 1;
                libxml_use_internal_errors( true );
                $dom = new DOMDocument();
                $dom->recover = TRUE;
                $content = '<div>' . (( $h1_outside ? '<h1>' . get_the_title( $post_id ) . '</h1>' : '' )) . $content . (( has_post_thumbnail( $post_id ) ? get_the_post_thumbnail( $post_id ) : '' )) . '</div>';
                
                if ( $dom->loadHTML( $content ) ) {
                    $h1s = $dom->getElementsByTagName( 'h1' );
                    $count_h1 = $h1s->length;
                    // +($h1_outside?1:0);
                    $h2s = $dom->getElementsByTagName( 'h2' );
                    $count_h2 = $h2s->length;
                    $h3s = $dom->getElementsByTagName( 'h3' );
                    $count_h3 = $h3s->length;
                    $imgs = $dom->getElementsByTagName( 'img' );
                    $videos = $dom->getElementsByTagName( 'video' );
                    $iframes = $dom->getElementsByTagName( 'iframe' );
                    $tags = array(
                        'h1' => $count_h1,
                        'h2' => $h2s->length,
                        'h3' => $h3s->length,
                        'h4' => $dom->getElementsByTagName( 'h4' )->length,
                        'h5' => $dom->getElementsByTagName( 'h5' )->length,
                        'h6' => $dom->getElementsByTagName( 'h6' )->length,
                    );
                } else {
                    $h1s = array();
                    $count_h1 = 0;
                    // + ($h1_outside?1:0);
                    $h2s = array();
                    $count_h2 = 0;
                    $h3s = array();
                    $count_h3 = 0;
                    $imgs = false;
                    $videos = false;
                    $iframes = false;
                    $tags = array(
                        'h1' => 0,
                        'h2' => 0,
                        'h3' => 0,
                        'h4' => 0,
                        'h5' => 0,
                        'h6' => 0,
                    );
                    $res['issues']['html_errors'] = 0;
                }
                
                $errors = libxml_get_errors();
                if ( $errors ) {
                    $res['issues']['html_errors'] = 0;
                }
                $res['issues']['html_error_infos'] = $errors;
                libxml_clear_errors();
                $res['tags'] = $tags;
                $res['content'] = $content_plain;
                $res['content_length'] = $content_length;
                $res['word_count'] = $word_count;
                $has_prio1 = 0;
                $title_has_prio1 = false;
                $desc_has_prio1 = false;
                $url_has_prio1 = false;
                $h1_has_prio1 = 0;
                $prio1_h1_den = 0;
                $keyword_den = array();
                $kw_score = 0;
                $content_san = WSKO_Class_Helper::densify_string( $content_plain );
                //, true);
                $keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
                
                if ( $keywords && !empty($keywords) ) {
                    $kw_eff_sum = 0;
                    $kw_eff_c = 0;
                    foreach ( $keywords as $kw => $kw_data ) {
                        $prio = $kw_data['prio'];
                        $den_type = 0;
                        $kw_eff = 0;
                        $kw_den = 0;
                        $kw_san = WSKO_Class_Helper::densify_string( $kw );
                        foreach ( $kw_data['similar'] as $k => $d ) {
                            $kw_data['similar'][$k] = array(
                                'kw'     => $k,
                                'kw_san' => WSKO_Class_Helper::densify_string( $k ),
                            );
                        }
                        
                        if ( $word_count ) {
                            $kw_den = substr_count( $content_san, $kw_san );
                            foreach ( $kw_data['similar'] as $k => $d ) {
                                if ( !$kw_den || stripos( $d['kw_san'], $kw_san ) === false ) {
                                    $kw_den += substr_count( $content_san, $d['kw_san'] );
                                }
                            }
                            if ( $kw_den ) {
                                $kw_den = $kw_den / $word_count * 100;
                            }
                        }
                        
                        switch ( $prio ) {
                            case 1:
                                
                                if ( $kw_den < 0.5 ) {
                                    $kw_eff = 0;
                                    $den_type = 0;
                                    $den_type_det = 0;
                                } else {
                                    
                                    if ( $kw_den >= 0.5 && $kw_den < 0.75 ) {
                                        $kw_eff = ($kw_den - 0.5) / 0.25 * 0.5;
                                        $den_type = 2;
                                        $den_type_det = -2;
                                    } else {
                                        
                                        if ( $kw_den >= 0.75 && $kw_den < 1 ) {
                                            $kw_eff = ($kw_den - 0.75) / 0.25 * 1;
                                            $den_type = 2;
                                            $den_type_det = -1;
                                        } else {
                                            
                                            if ( $kw_den >= 1 && $kw_den < 3.5 ) {
                                                $kw_eff = 1;
                                                $den_type = 1;
                                                $den_type_det = 1;
                                            } else {
                                                
                                                if ( $kw_den >= 3.5 && $kw_den < 5 ) {
                                                    $kw_eff = ($kw_den - 3.5) / 1.5 * 1;
                                                    $den_type = 3;
                                                    $den_type_det = 2;
                                                } else {
                                                    
                                                    if ( $kw_den >= 5 && $kw_den < 8 ) {
                                                        $kw_eff = ($kw_den - 5) / 3 * 0.5;
                                                        $den_type = 3;
                                                        $den_type_det = 3;
                                                    } else {
                                                        $kw_eff = 1;
                                                        $den_type = 3;
                                                        $den_type_det = 4;
                                                    }
                                                
                                                }
                                            
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                                
                                $kw_eff_sum += $kw_eff * 4;
                                $kw_eff_c += 4;
                                $has_prio1++;
                                
                                if ( !$title_has_prio1 && (stripos( $title, $kw_san ) !== false || stripos( $title, $kw ) !== false) ) {
                                    $title_has_prio1 = true;
                                } else {
                                    foreach ( $kw_data['similar'] as $k => $d ) {
                                        if ( !$title_has_prio1 && (stripos( $title, $d['kw_san'] ) !== false || stripos( $title, $d['kw'] ) !== false) ) {
                                            $title_has_prio1 = true;
                                        }
                                    }
                                }
                                
                                
                                if ( !$desc_has_prio1 && (stripos( $desc, $kw_san ) !== false || stripos( $desc, $kw ) !== false) ) {
                                    $desc_has_prio1 = true;
                                } else {
                                    foreach ( $kw_data['similar'] as $k => $d ) {
                                        if ( !$desc_has_prio1 && (stripos( $desc, $d['kw_san'] ) !== false || stripos( $desc, $d['kw'] ) !== false) ) {
                                            $desc_has_prio1 = true;
                                        }
                                    }
                                }
                                
                                
                                if ( !$url_has_prio1 && stripos( str_replace( array( '/', '-', '_' ), '', $link ), $kw_san ) !== false ) {
                                    $url_has_prio1 = true;
                                } else {
                                    foreach ( $kw_data['similar'] as $k => $d ) {
                                        if ( !$url_has_prio1 && stripos( str_replace( array( '/', '-', '_' ), '', $link ), $d['kw_san'] ) !== false ) {
                                            $url_has_prio1 = true;
                                        }
                                    }
                                }
                                
                                /*$post_words = WSKO_Class_Helper::get_word_count($post->post_title);
                                		if (!$h1_outside)
                                		{
                                			$h1_san = WSKO_Class_Helper::densify_string($post->post_title);
                                			if (stripos($post->post_title, $kw) !== false || stripos($h1_san, $kw_san) !== false)
                                			{
                                				$prio1_h1_den += substr_count($h1_san, $kw_san) / $post_words;
                                				$h1_has_prio1++;
                                			}
                                			else
                                			{
                                				foreach ($kw_data['similar'] as $k => $d)
                                				{
                                					if (stripos($post->post_title, $d['kw']) !== false || stripos($h1_san, $d['kw_san']) !== false)
                                					{
                                						$prio1_h1_den += substr_count($h1_san, $d['kw_san']) / $post_words;
                                						$h1_has_prio1++;
                                					}
                                				}
                                			}
                                		}*/
                                foreach ( $h1s as $h1 ) {
                                    $h1_words = WSKO_Class_Helper::get_word_count( $h1->nodeValue );
                                    $h1_san = WSKO_Class_Helper::densify_string( $h1->nodeValue );
                                    
                                    if ( stripos( $h1->nodeValue, $kw ) !== false || stripos( $h1_san, $kw_san ) !== false ) {
                                        $prio1_h1_den += substr_count( $h1_san, $kw_san ) / $h1_words;
                                        $h1_has_prio1++;
                                    } else {
                                        foreach ( $kw_data['similar'] as $k => $d ) {
                                            
                                            if ( stripos( $h1->nodeValue, $d['kw'] ) !== false || stripos( $h1_san, $d['kw_san'] ) !== false ) {
                                                $prio1_h1_den += substr_count( $h1_san, $d['kw_san'] ) / $h1_words;
                                                $h1_has_prio1++;
                                            }
                                        
                                        }
                                    }
                                
                                }
                                break;
                            case 2:
                                
                                if ( $kw_den < 0.2 ) {
                                    $kw_eff = 0;
                                    $den_type = 0;
                                    $den_type_det = 0;
                                } else {
                                    
                                    if ( $kw_den >= 0.2 && $kw_den < 0.5 ) {
                                        $kw_eff = ($kw_den - 0.2) / 0.4 * 1;
                                        $den_type = 2;
                                        $den_type_det = -1;
                                    } else {
                                        
                                        if ( $kw_den >= 0.5 && $kw_den < 3.5 ) {
                                            $kw_eff = 1;
                                            $den_type = 1;
                                            $den_type_det = 1;
                                        } else {
                                            
                                            if ( $kw_den >= 3.5 && $kw_den < 5 ) {
                                                $kw_eff = ($kw_den - 3.5) / 1.5 * 1;
                                                $den_type = 3;
                                                $den_type_det = 2;
                                            } else {
                                                
                                                if ( $kw_den >= 5 && $kw_den < 8 ) {
                                                    $kw_eff = ($kw_den - 5) / 3 * 0.5;
                                                    $den_type = 3;
                                                    $den_type_det = 3;
                                                } else {
                                                    $kw_eff = 1;
                                                    $den_type = 3;
                                                    $den_type_det = 4;
                                                }
                                            
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                                
                                $kw_eff_sum += $kw_eff * 2;
                                $kw_eff_c += 2;
                                break;
                        }
                        $keyword_den[$kw] = array(
                            'density'  => round( $kw_den, 2 ),
                            'type'     => $den_type,
                            'det_type' => $den_type_det,
                            'prio'     => $prio,
                        );
                    }
                    if ( $h1_has_prio1 && $count_h1 ) {
                        $prio1_h1_den = round( $prio1_h1_den / $count_h1, 2 );
                    }
                    if ( $kw_eff_c > 0 ) {
                        $kw_score = $kw_eff_sum / $kw_eff_c;
                    }
                    if ( !$word_count ) {
                        $kw_score = 0;
                    }
                }
                
                //if (!$keyword_den)
                //$keyword_den = array('no-keyword' => array('density' => 0, 'type' => 0, 'prio' => 1));
                $res['issues']['keyword_density'] = $keyword_den;
                $res['title_length'] = $title_length;
                $res['issues']['title_length'] = 1;
                $res['issues']['title_prio1'] = $has_prio1 && $title_has_prio1;
                $title_score = 1;
                
                if ( $title_length ) {
                    
                    if ( $title_length < WSKO_ONPAGE_TITLE_MIN ) {
                        $res['issues']['title_length'] = 2;
                        $title_score = 0.75;
                    } else {
                        
                        if ( $title_length > WSKO_ONPAGE_TITLE_MAX ) {
                            $res['issues']['title_length'] = 3;
                            $title_score = 0.75;
                        } else {
                        }
                    
                    }
                    
                    if ( $has_prio1 && !$title_has_prio1 ) {
                        $title_score = 0.25;
                    }
                } else {
                    $res['issues']['title_length'] = 0;
                    $title_score = 0;
                }
                
                $res['desc_length'] = $desc_length;
                $res['issues']['desc_length'] = 1;
                $res['issues']['desc_prio1'] = $has_prio1 && $desc_has_prio1;
                $desc_score = 1;
                
                if ( $desc_length ) {
                    
                    if ( $desc_length < WSKO_ONPAGE_DESC_MIN ) {
                        $res['issues']['desc_length'] = 2;
                        $desc_score = 0.5;
                    } else {
                        
                        if ( $desc_length > WSKO_ONPAGE_DESC_MAX ) {
                            $res['issues']['desc_length'] = 3;
                            $desc_score = 0.5;
                        }
                    
                    }
                    
                    if ( $has_prio1 && !$desc_has_prio1 ) {
                        $desc_score = 0.5;
                    }
                } else {
                    $res['issues']['desc_length'] = 0;
                    $desc_score = 0;
                }
                
                $res['issues']['url_length'] = 1;
                $res['issues']['url_prio1'] = $has_prio1 && $url_has_prio1;
                $res['url_length'] = $url_length = ( $link ? mb_strlen( str_ireplace( WSKO_Class_Helper::get_host_base( false ), '', $link ) ) : 0 );
                $url_score = 1;
                
                if ( $url_length > WSKO_ONPAGE_URL_MAX ) {
                    $res['issues']['url_length'] = 0;
                    $url_score = 0.5;
                }
                
                if ( !$has_prio1 || !$url_has_prio1 ) {
                    $url_score = 0;
                }
                $res['issues']['heading_h1_prio1'] = ( $h1_has_prio1 ? 1 : 0 );
                $res['issues']['heading_h1_count'] = 0;
                $res['issues']['heading_h1_prio1_count'] = 0;
                $heading1_score = 0;
                
                if ( $count_h1 ) {
                    $res['issues']['heading_h1_count'] = 1;
                    $heading1_score = 1;
                    
                    if ( $has_prio1 && $h1_has_prio1 / $has_prio1 <= 0.5 ) {
                        $res['issues']['heading_h1_prio1_count'] = 0;
                        $heading1_score = 0.66;
                    } else {
                        $res['issues']['heading_h1_prio1_count'] = 1;
                    }
                    
                    
                    if ( $count_h1 > 1 ) {
                        $res['issues']['heading_h1_count'] = 2;
                        
                        if ( $heading1_score > 0.66 ) {
                            $heading1_score -= 0.66;
                        } else {
                            $heading1_score = 0;
                        }
                    
                    }
                
                }
                
                $res['issues']['heading_h2_250'] = 1;
                $res['issues']['heading_h2_500'] = 1;
                $res['issues']['heading_h2h3_count'] = 0;
                $heading2_score = 0;
                
                if ( $count_h2 || $count_h3 ) {
                    $res['issues']['heading_h2h3_count'] = 1;
                    $heading2_score = 1;
                    
                    if ( !$count_h2 && $word_count > 500 ) {
                        $res['issues']['heading_h2_500'] = 0;
                        $heading2_score = 0;
                    } else {
                        
                        if ( !$count_h2 && $word_count > 250 ) {
                            $res['issues']['heading_h2_250'] = 0;
                            $heading2_score = 0.5;
                        }
                    
                    }
                
                }
                
                $res['issues']['word_count'] = 1;
                $content_score = 1;
                
                if ( $word_count < 50 ) {
                    $res['issues']['word_count'] = 2;
                    $content_score = 0;
                } else {
                    
                    if ( $word_count < 100 ) {
                        $res['issues']['word_count'] = 3;
                        $content_score = 0.25;
                    } else {
                        
                        if ( $word_count < 200 ) {
                            $res['issues']['word_count'] = 4;
                            $content_score = 0.5;
                        } else {
                            
                            if ( $word_count < 400 ) {
                                $res['issues']['word_count'] = 5;
                                $content_score = 0.75;
                            }
                        
                        }
                    
                    }
                
                }
                
                if ( !$word_count ) {
                    $res['issues']['word_count'] = 0;
                }
                $res['issues']['media'] = 0;
                $res['issues']['media_missing_alt'] = 1;
                $media_score = 0;
                
                if ( $imgs && $imgs->length || $videos && $videos->length ) {
                    $media_score = 1;
                    if ( $imgs ) {
                        foreach ( $imgs as $img ) {
                            
                            if ( !$img->getAttribute( 'alt' ) ) {
                                $res['issues']['media_missing_alt'] = 0;
                                $media_score = 0.5;
                                break;
                            }
                        
                        }
                    }
                    $res['issues']['media'] = 1;
                }
                
                if ( $iframes && $iframes->length ) {
                    foreach ( $iframes as $iframe ) {
                        $src = $iframe->getAttribute( 'src' );
                        if ( stripos( $src, 'youtube.' ) !== false || stripos( $src, 'youtu.be' ) !== false || stripos( $src, 'vimeo.com' ) !== false || stripos( $src, 'dailymotion.com' ) !== false || stripos( $src, 'dai.ly' ) !== false ) {
                            $res['issues']['media'] = 1;
                        }
                    }
                }
                $res['onpage_score'] = round( ($kw_score * 0.25 + $title_score * 0.2 + $desc_score * 0.05 + $url_score * 0.05 + $heading1_score * 0.15 + $heading2_score * 0.1 + $content_score * 0.15 + $media_score * 0.05) * 100, 2 );
                if ( $reset_post ) {
                    wp_reset_postdata();
                }
                return $res;
            }
        
        } catch ( \Exception $error ) {
            if ( $log_error ) {
                WSKO_Class_Helper::report_error( 'exception', 'E2: ' . __( 'Onpage - Single Report Error', 'wsko' ), $error );
            }
        }
        return false;
    }
    
    public static function generate_onpage_analysis()
    {
        try {
            $is_segment = false;
            /*$crawl_interval = intval(WSKO_Class_Core::get_setting('onpage_analysis_interval'));
            		if (!$crawl_interval)
            			$crawl_interval = 7;
            			
            		if ($crawl_interval <= 0)
            			$crawl_interval = 1;
            		else if ($crawl_interval > 30)
            			$crawl_interval = 30;*/
            $crawl_interval = WSKO_ONPAGE_FETCH_INTERVAL;
            $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
            $last_offset = intval( WSKO_Class_Core::get_option( 'last_onpage_offset' ) );
            if ( $last_offset ) {
                $is_segment = true;
            }
            
            if ( $global_analysis != false ) {
                
                if ( isset( $global_analysis['new_report'] ) && $global_analysis['new_report'] ) {
                    
                    if ( $global_analysis['new_report']['query_expire'] > time() ) {
                        
                        if ( isset( $global_analysis['new_report']['local_running'] ) && !$global_analysis['new_report']['local_running'] ) {
                            WSKO_Class_Onpage::finish_onpage_analysis();
                            return;
                        }
                        
                        if ( WSKO_Class_Core::get_option( 'last_onpage_segment_fetch' ) > time() - (WSKO_LRS_TIMEOUT + 10) ) {
                            return;
                        }
                    } else {
                        $is_segment = false;
                    }
                
                } else {
                    if ( isset( $global_analysis['current_report']['total_pages'] ) && $global_analysis['current_report']['total_pages'] && (isset( $global_analysis['current_report']['started'] ) && $global_analysis['current_report']['started'] > time() - 60 * 60 * 24 * $crawl_interval) ) {
                        
                        if ( defined( 'WSKO_UNLOCK_ONPAGE' ) && WSKO_UNLOCK_ONPAGE ) {
                        } else {
                            return;
                        }
                    
                    }
                }
                
                if ( !$is_segment ) {
                    $global_analysis['new_report'] = array(
                        'query_running' => true,
                        'query_expire'  => time() + 60 * 60 * 24,
                    );
                }
            } else {
                $global_analysis = array(
                    'new_report' => array(
                    'query_running' => true,
                    'local_running' => true,
                    'query_expire'  => time() + 60 * 60 * 24,
                ),
                );
            }
            
            WSKO_Class_Onpage::save_onpage_analysis( $global_analysis );
            WSKO_Class_Helper::prepare_heavy_operation( 'onpage_analysis' );
            WSKO_Class_Core::save_option( 'onpage_analysis_running', true, true );
            $analysis = array();
            if ( $is_segment ) {
                $analysis = get_option( 'wsko_onpage_temp' );
            }
            
            if ( !$is_segment || !$analysis ) {
                WSKO_Class_Cache::delete_cache_rows( array( 'onpage' ), false );
                //reset cache if first segment
                delete_option( 'wsko_onpage_temp' );
                $analysis = array(
                    'started'                    => time(),
                    'onpage_score'               => 0,
                    'total_pages'                => 0,
                    'post_types'                 => array(),
                    'issues'                     => array(
                    'keywords'               => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'keyword_density'        => array(
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                )
                ),
                    'title_length'           => array(
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                )
                ),
                    'title_prio1'            => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'desc_length'            => array(
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                )
                ),
                    'desc_prio1'             => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h1_count'       => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h1_prio1'       => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h1_prio1_count' => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h2_250'         => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h2_500'         => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'heading_h2h3_count'     => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'word_count'             => array(
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                ),
                    array(
                    'sum'   => 0,
                    'posts' => array(),
                )
                ),
                    'url_length'             => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'url_prio1'              => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'media'                  => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                    'media_missing_alt'      => array( array(
                    'sum'   => 0,
                    'posts' => array(),
                ), array(
                    'sum'   => 0,
                    'posts' => array(),
                ) ),
                ),
                    'title_length_dist'          => array(
                    'ok'            => 0,
                    'too_long'      => 0,
                    'too_short'     => 0,
                    'not_set'       => 0,
                    'no_custom_set' => 0,
                ),
                    'desc_length_dist'           => array(
                    'ok'        => 0,
                    'too_long'  => 0,
                    'too_short' => 0,
                    'not_set'   => 0,
                ),
                    'title_duplicate_dist'       => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    'desc_duplicate_dist'        => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    'priority_kw_duplicate_dist' => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    'url_length_dist'            => array(
                    'ok'       => 0,
                    'too_long' => 0,
                ),
                    'facebook_meta_dist'         => array(
                    'title_length' => array(
                    0,
                    0,
                    0,
                    0
                ),
                    'desc_length'  => array(
                    0,
                    0,
                    0,
                    0
                ),
                    'image'        => array( 0, 0 ),
                ),
                    'twitter_meta_dist'          => array(
                    'title_length' => array(
                    0,
                    0,
                    0,
                    0
                ),
                    'desc_length'  => array(
                    0,
                    0,
                    0,
                    0
                ),
                    'image'        => array( 0, 0 ),
                ),
                    'content_length_dist'        => array(
                    '0-100'     => 0,
                    '100-250'   => 0,
                    '250-500'   => 0,
                    '500-1000'  => 0,
                    '1000-2000' => 0,
                    '2000-3000' => 0,
                    '3000+'     => 0,
                ),
                    'heading_dist'               => array(
                    "h1" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    "h2" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    "h3" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    "h4" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    "h5" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                    "h6" => array(
                    0,
                    0,
                    0,
                    0,
                    0,
                    0
                ),
                ),
                    'priority_kw_den_dist'       => array(
                    '< 0.5 %'     => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                    '0.5 - 1.5 %' => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                    '1.5 - 2.5 %' => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                    '2.5 - 3.5 %' => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                    '3.5 - 5 %'   => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                    '> 5 %'       => array(
                    'prio1' => 0,
                    'prio2' => 0,
                ),
                ),
                    'priority_kw_dist'           => array(
                    'prio1' => array( 0, 0 ),
                    'prio2' => array( 0, 0 ),
                ),
                    'priority_kw_duplicates'     => array(),
                    'max'                        => array(
                    'onpage_score'   => 0,
                    'title_length'   => 0,
                    'title_dupl'     => 0,
                    'desc_length'    => 0,
                    'desc_dupl'      => 0,
                    'kw_dupl'        => 0,
                    'url_length'     => 0,
                    'content_length' => 0,
                    'word_count'     => 0,
                    'heading_count'  => 0,
                ),
                    't_titles'                   => array(),
                    't_desc'                     => array(),
                    't_keywords'                 => array(),
                );
                
                if ( !WSKO_Class_Onpage::seo_plugins_disabled() ) {
                    $source = WSKO_Class_Compatibility::get_seo_plugin_preview( 'metas' );
                    if ( $source ) {
                        $analysis['ext_source'] = array(
                            'metas' => $source,
                        );
                    }
                }
            
            }
            
            $is_segment = true;
            //$post_types_ex = WSKO_Class_Helper::safe_explode(',', WSKO_Class_Core::get_setting('onpage_exclude_post_types'));
            $post_types_in = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_include_post_types' ) );
            $posts_ex = WSKO_Class_Helper::safe_explode( ',', WSKO_Class_Core::get_setting( 'onpage_exclude_posts' ) );
            $offset = ( $last_offset ? $last_offset : 0 );
            $step = 5000;
            //batch queries
            //global $post;
            //$t_post = $post;
            $onpage_row_limit = 1000;
            $limit_hit_flag = false;
            WSKO_Class_Core::save_option( 'last_onpage_segment_fetch', time(), true );
            //do
            //{
            $op_errors = 0;
            $new_rows = array();
            
            if ( $post_types_in ) {
                $query = new WP_Query( array(
                    'posts_per_page' => $step,
                    'offset'         => $offset * $step,
                    'post_type'      => $post_types_in,
                    'post_status'    => 'publish',
                ) );
                $results = $query->posts;
                foreach ( $results as $res ) {
                    /*if ($post_types_ex && in_array($res->post_type, $post_types_ex))
                    		continue;*/
                    if ( $posts_ex && in_array( $res->ID, $posts_ex ) ) {
                        continue;
                    }
                    
                    if ( $analysis['total_pages'] >= $onpage_row_limit ) {
                        $limit_hit_flag = true;
                        break;
                    }
                    
                    $post = $res;
                    $op_report = WSKO_Class_Onpage::get_onpage_report(
                        $res->ID,
                        false,
                        false,
                        false
                    );
                    
                    if ( $op_report ) {
                        $title = $op_report['meta_title'];
                        $desc = $op_report['meta_desc'];
                        
                        if ( $op_report['meta_og_title'] ) {
                            
                            if ( $op_report['meta_og_title'] < WSKO_ONPAGE_FB_TITLE_MIN ) {
                                $analysis['facebook_meta_dist']['title_length'][2]++;
                            } else {
                                
                                if ( $op_report['meta_og_title'] > WSKO_ONPAGE_FB_TITLE_MAX ) {
                                    $analysis['facebook_meta_dist']['title_length'][3]++;
                                } else {
                                    $analysis['facebook_meta_dist']['title_length'][1]++;
                                }
                            
                            }
                        
                        } else {
                            $analysis['facebook_meta_dist']['title_length'][0]++;
                        }
                        
                        
                        if ( $op_report['meta_og_desc'] ) {
                            
                            if ( $op_report['meta_og_desc'] < WSKO_ONPAGE_FB_DESC_MIN ) {
                                $analysis['facebook_meta_dist']['desc_length'][2]++;
                            } else {
                                
                                if ( $op_report['meta_og_desc'] > WSKO_ONPAGE_FB_DESC_MAX ) {
                                    $analysis['facebook_meta_dist']['desc_length'][3]++;
                                } else {
                                    $analysis['facebook_meta_dist']['desc_length'][1]++;
                                }
                            
                            }
                        
                        } else {
                            $analysis['facebook_meta_dist']['desc_length'][0]++;
                        }
                        
                        
                        if ( $op_report['meta_og_img'] ) {
                            $analysis['facebook_meta_dist']['image'][1]++;
                        } else {
                            $analysis['facebook_meta_dist']['image'][0]++;
                        }
                        
                        
                        if ( $op_report['meta_tw_title'] ) {
                            
                            if ( $op_report['meta_tw_title'] < WSKO_ONPAGE_TW_TITLE_MIN ) {
                                $analysis['twitter_meta_dist']['title_length'][2]++;
                            } else {
                                
                                if ( $op_report['meta_tw_title'] > WSKO_ONPAGE_TW_TITLE_MAX ) {
                                    $analysis['twitter_meta_dist']['title_length'][3]++;
                                } else {
                                    $analysis['twitter_meta_dist']['title_length'][1]++;
                                }
                            
                            }
                        
                        } else {
                            $analysis['twitter_meta_dist']['title_length'][0]++;
                        }
                        
                        
                        if ( $op_report['meta_tw_desc'] ) {
                            
                            if ( $op_report['meta_tw_desc'] < WSKO_ONPAGE_TW_DESC_MIN ) {
                                $analysis['twitter_meta_dist']['desc_length'][2]++;
                            } else {
                                
                                if ( $op_report['meta_tw_desc'] > WSKO_ONPAGE_TW_DESC_MAX ) {
                                    $analysis['twitter_meta_dist']['desc_length'][3]++;
                                } else {
                                    $analysis['twitter_meta_dist']['desc_length'][1]++;
                                }
                            
                            }
                        
                        } else {
                            $analysis['twitter_meta_dist']['desc_length'][0]++;
                        }
                        
                        
                        if ( $op_report['meta_tw_img'] ) {
                            $analysis['twitter_meta_dist']['image'][1]++;
                        } else {
                            $analysis['twitter_meta_dist']['image'][0]++;
                        }
                        
                        $post_url = get_permalink( $res->ID );
                        $title_length = ( $title ? mb_strlen( $title ) : 0 );
                        $desc_length = ( $desc ? mb_strlen( $desc ) : 0 );
                        if ( $title_length > $analysis['max']['title_length'] ) {
                            $analysis['max']['title_length'] = $title_length;
                        }
                        if ( $desc_length > $analysis['max']['desc_length'] ) {
                            $analysis['max']['desc_length'] = $desc_length;
                        }
                        if ( !$op_report['meta_title_custom'] ) {
                            $analysis['title_length_dist']['no_custom_set']++;
                        }
                        
                        if ( $title ) {
                            
                            if ( isset( $analysis['t_titles'][$title] ) ) {
                                $analysis['t_titles'][$title]['dup_posts'][] = $res->ID;
                                $analysis['t_titles'][$title]['count']++;
                            } else {
                                $analysis['t_titles'][$title] = array(
                                    'dup_posts' => array( $res->ID ),
                                    'count'     => 1,
                                    'length'    => $title_length,
                                );
                            }
                        
                        } else {
                            $analysis['title_length_dist']['not_set']++;
                        }
                        
                        
                        if ( $desc ) {
                            
                            if ( isset( $analysis['t_desc'][$desc] ) ) {
                                $analysis['t_desc'][$desc]['dup_posts'][] = $res->ID;
                                $analysis['t_desc'][$desc]['count']++;
                            } else {
                                $analysis['t_desc'][$desc] = array(
                                    'dup_posts' => array( $res->ID ),
                                    'count'     => 1,
                                    'length'    => $desc_length,
                                );
                            }
                        
                        } else {
                            $analysis['desc_length_dist']['not_set']++;
                        }
                        
                        
                        if ( $op_report['url_length'] > WSKO_ONPAGE_URL_MAX ) {
                            $analysis['url_length_dist']['too_long']++;
                        } else {
                            $analysis['url_length_dist']['ok']++;
                        }
                        
                        if ( $op_report['url_length'] > $analysis['max']['url_length'] ) {
                            $analysis['max']['url_length'] = $op_report['url_length'];
                        }
                        $content_plain = $op_report['content'];
                        $content_length = $op_report['content_length'];
                        $word_count = $op_report['word_count'];
                        if ( $content_length > $analysis['max']['content_length'] ) {
                            $analysis['max']['content_length'] = $content_length;
                        }
                        if ( $word_count > $analysis['max']['word_count'] ) {
                            $analysis['max']['word_count'] = $word_count;
                        }
                        
                        if ( !$word_count || $word_count < 100 ) {
                            $analysis['content_length_dist']['0-100']++;
                        } else {
                            
                            if ( $word_count < 250 ) {
                                $analysis['content_length_dist']['100-250']++;
                            } else {
                                
                                if ( $word_count < 500 ) {
                                    $analysis['content_length_dist']['250-500']++;
                                } else {
                                    
                                    if ( $word_count < 1000 ) {
                                        $analysis['content_length_dist']['500-1000']++;
                                    } else {
                                        
                                        if ( $word_count < 2000 ) {
                                            $analysis['content_length_dist']['1000-2000']++;
                                        } else {
                                            
                                            if ( $word_count < 3000 ) {
                                                $analysis['content_length_dist']['2000-3000']++;
                                            } else {
                                                $analysis['content_length_dist']['3000+']++;
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                            
                            }
                        
                        }
                        
                        $tags = $op_report['tags'];
                        $max_h = max( $tags );
                        if ( $max_h > $analysis['max']['heading_count'] ) {
                            $analysis['max']['heading_count'] = $max_h;
                        }
                        
                        if ( $tags['h1'] <= 5 ) {
                            $analysis['heading_dist']['h1'][$tags['h1']]++;
                        } else {
                            $analysis['heading_dist']['h1'][5]++;
                        }
                        
                        
                        if ( $tags['h2'] <= 5 ) {
                            $analysis['heading_dist']['h2'][$tags['h2']]++;
                        } else {
                            $analysis['heading_dist']['h2'][5]++;
                        }
                        
                        
                        if ( $tags['h3'] <= 5 ) {
                            $analysis['heading_dist']['h3'][$tags['h3']]++;
                        } else {
                            $analysis['heading_dist']['h3'][5]++;
                        }
                        
                        
                        if ( $tags['h4'] <= 5 ) {
                            $analysis['heading_dist']['h4'][$tags['h4']]++;
                        } else {
                            $analysis['heading_dist']['h4'][5]++;
                        }
                        
                        
                        if ( $tags['h5'] <= 5 ) {
                            $analysis['heading_dist']['h5'][$tags['h5']]++;
                        } else {
                            $analysis['heading_dist']['h5'][5]++;
                        }
                        
                        
                        if ( $tags['h6'] <= 5 ) {
                            $analysis['heading_dist']['h6'][$tags['h6']]++;
                        } else {
                            $analysis['heading_dist']['h6'][5]++;
                        }
                        
                        $onpage_score = $op_report['onpage_score'];
                        if ( $onpage_score > $analysis['max']['onpage_score'] ) {
                            $analysis['max']['onpage_score'] = $onpage_score;
                        }
                        $analysis['onpage_score'] += $onpage_score;
                        $prio1_kw_den = array();
                        $prio2_kw_den = array();
                        
                        if ( $op_report['issues']['keyword_density'] ) {
                            $kw_issues = array();
                            foreach ( $op_report['issues']['keyword_density'] as $pk => $iss ) {
                                if ( $pk ) {
                                    
                                    if ( isset( $analysis['t_keywords'][$pk] ) ) {
                                        $analysis['t_keywords'][$pk]['dup_posts'][] = $res->ID;
                                        $analysis['t_keywords'][$pk]['count']++;
                                    } else {
                                        $analysis['t_keywords'][$pk] = array(
                                            'dup_posts' => array( $res->ID ),
                                            'count'     => 1,
                                        );
                                    }
                                
                                }
                                
                                if ( $iss['density'] < 0.5 ) {
                                    $analysis['priority_kw_den_dist']['< 0.5 %']['prio' . $iss['prio']]++;
                                } else {
                                    
                                    if ( $iss['density'] < 1.5 ) {
                                        $analysis['priority_kw_den_dist']['0.5 - 1.5 %']['prio' . $iss['prio']]++;
                                    } else {
                                        
                                        if ( $iss['density'] < 2.5 ) {
                                            $analysis['priority_kw_den_dist']['1.5 - 2.5 %']['prio' . $iss['prio']]++;
                                        } else {
                                            
                                            if ( $iss['density'] < 3.5 ) {
                                                $analysis['priority_kw_den_dist']['2.5 - 3.5 %']['prio' . $iss['prio']]++;
                                            } else {
                                                
                                                if ( $iss['density'] < 5 ) {
                                                    $analysis['priority_kw_den_dist']['3.5 - 5 %']['prio' . $iss['prio']]++;
                                                } else {
                                                    $analysis['priority_kw_den_dist']['> 5 %']['prio' . $iss['prio']]++;
                                                }
                                            
                                            }
                                        
                                        }
                                    
                                    }
                                
                                }
                                
                                switch ( $iss['prio'] ) {
                                    case 1:
                                        $prio1_kw_den[$pk] = $iss['density'];
                                        break;
                                    case 2:
                                        $prio2_kw_den[$pk] = $iss['density'];
                                        break;
                                }
                                if ( $iss['type'] != 1 ) {
                                    $kw_issues[$iss['type']] = true;
                                }
                            }
                            if ( $kw_issues ) {
                                foreach ( $kw_issues as $iss => $t ) {
                                    $analysis['issues']['keyword_density'][$iss]['sum']++;
                                    if ( $analysis['issues']['keyword_density'][$iss]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                        $analysis['issues']['keyword_density'][$iss]['posts'][] = $res->ID;
                                    }
                                }
                            }
                        } else {
                            $analysis['issues']['keywords'][0]['sum']++;
                            if ( $analysis['issues']['keywords'][0]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['keywords'][0]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $prio1_kw_den ) {
                            $analysis['priority_kw_dist']['prio1'][1]++;
                        } else {
                            $analysis['priority_kw_dist']['prio1'][0]++;
                        }
                        
                        
                        if ( $prio2_kw_den ) {
                            $analysis['priority_kw_dist']['prio2'][1]++;
                        } else {
                            $analysis['priority_kw_dist']['prio2'][0]++;
                        }
                        
                        $new_rows[] = array(
                            'onpage_score'          => $onpage_score,
                            'title'                 => $title,
                            'title_custom'          => $op_report['meta_title_custom'],
                            'title_length'          => $title_length,
                            'title_duplicates'      => 0,
                            'title_duplicate_posts' => serialize( array() ),
                            'desc_s'                => $desc,
                            'desc_length'           => $desc_length,
                            'desc_duplicate_posts'  => serialize( array() ),
                            'desc_duplicates'       => 0,
                            'count_h1'              => $tags['h1'],
                            'count_h2'              => $tags['h2'],
                            'count_h3'              => $tags['h3'],
                            'count_h4'              => $tags['h4'],
                            'count_h5'              => $tags['h5'],
                            'count_h6'              => $tags['h6'],
                            'word_count'            => $word_count,
                            'content_length'        => $content_length,
                            'url'                   => $post_url,
                            'url_length'            => $op_report['url_length'],
                            'post_id'               => $res->ID,
                            'post_type'             => $res->post_type,
                            'prio1_kw_den'          => serialize( $prio1_kw_den ),
                            'prio2_kw_den'          => serialize( $prio2_kw_den ),
                            'og_title_length'       => ( $op_report['meta_og_title'] ? mb_strlen( $op_report['meta_og_title'] ) : 0 ),
                            'og_desc_length'        => ( $op_report['meta_og_desc'] ? mb_strlen( $op_report['meta_og_desc'] ) : 0 ),
                            'og_img_provided'       => ( $op_report['meta_og_img'] ? 1 : 0 ),
                            'tw_title_length'       => ( $op_report['meta_tw_title'] ? mb_strlen( $op_report['meta_tw_title'] ) : 0 ),
                            'tw_desc_length'        => ( $op_report['meta_tw_desc'] ? mb_strlen( $op_report['meta_tw_desc'] ) : 0 ),
                            'tw_img_provided'       => ( $op_report['meta_tw_img'] ? 1 : 0 ),
                        );
                        if ( !in_array( $res->post_type, $analysis['post_types'] ) ) {
                            $analysis['post_types'][] = $res->post_type;
                        }
                        
                        if ( $op_report['issues']['title_length'] != 1 ) {
                            $analysis['issues']['title_length'][$op_report['issues']['title_length']]['sum']++;
                            if ( $analysis['issues']['title_length'][$op_report['issues']['title_length']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['title_length'][$op_report['issues']['title_length']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['title_prio1'] != 1 ) {
                            $analysis['issues']['title_prio1'][$op_report['issues']['title_prio1']]['sum']++;
                            if ( $analysis['issues']['title_prio1'][$op_report['issues']['title_prio1']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['title_prio1'][$op_report['issues']['title_prio1']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['desc_length'] != 1 ) {
                            $analysis['issues']['desc_length'][$op_report['issues']['desc_length']]['sum']++;
                            if ( $analysis['issues']['desc_length'][$op_report['issues']['desc_length']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['desc_length'][$op_report['issues']['desc_length']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['desc_prio1'] != 1 ) {
                            $analysis['issues']['desc_prio1'][$op_report['issues']['desc_prio1']]['sum']++;
                            if ( $analysis['issues']['desc_prio1'][$op_report['issues']['desc_prio1']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['desc_prio1'][$op_report['issues']['desc_prio1']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h1_count'] != 1 ) {
                            $analysis['issues']['heading_h1_count'][$op_report['issues']['heading_h1_count']]['sum']++;
                            if ( $analysis['issues']['heading_h1_count'][$op_report['issues']['heading_h1_count']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h1_count'][$op_report['issues']['heading_h1_count']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h1_prio1'] != 1 ) {
                            $analysis['issues']['heading_h1_prio1'][$op_report['issues']['heading_h1_prio1']]['sum']++;
                            if ( $analysis['issues']['heading_h1_prio1'][$op_report['issues']['heading_h1_prio1']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h1_prio1'][$op_report['issues']['heading_h1_prio1']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h1_prio1_count'] != 1 ) {
                            $analysis['issues']['heading_h1_prio1_count'][$op_report['issues']['heading_h1_prio1_count']]['sum']++;
                            if ( $analysis['issues']['heading_h1_prio1_count'][$op_report['issues']['heading_h1_prio1_count']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h1_prio1_count'][$op_report['issues']['heading_h1_prio1_count']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h2_250'] != 1 ) {
                            $analysis['issues']['heading_h2_250'][$op_report['issues']['heading_h2_250']]['sum']++;
                            if ( $analysis['issues']['heading_h2_250'][$op_report['issues']['heading_h2_250']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h2_250'][$op_report['issues']['heading_h2_250']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h2_500'] != 1 ) {
                            $analysis['issues']['heading_h2_500'][$op_report['issues']['heading_h2_500']]['sum']++;
                            if ( $analysis['issues']['heading_h2_500'][$op_report['issues']['heading_h2_500']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h2_500'][$op_report['issues']['heading_h2_500']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['heading_h2h3_count'] != 1 ) {
                            $analysis['issues']['heading_h2h3_count'][$op_report['issues']['heading_h2h3_count']]['sum']++;
                            if ( $analysis['issues']['heading_h2h3_count'][$op_report['issues']['heading_h2h3_count']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['heading_h2h3_count'][$op_report['issues']['heading_h2h3_count']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['word_count'] != 1 ) {
                            $analysis['issues']['word_count'][$op_report['issues']['word_count']]['sum']++;
                            if ( $analysis['issues']['word_count'][$op_report['issues']['word_count']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['word_count'][$op_report['issues']['word_count']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['url_length'] != 1 ) {
                            $analysis['issues']['url_length'][$op_report['issues']['url_length']]['sum']++;
                            if ( $analysis['issues']['url_length'][$op_report['issues']['url_length']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['url_length'][$op_report['issues']['url_length']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['url_prio1'] != 1 ) {
                            $analysis['issues']['url_prio1'][$op_report['issues']['url_prio1']]['sum']++;
                            if ( $analysis['issues']['url_prio1'][$op_report['issues']['url_prio1']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['url_prio1'][$op_report['issues']['url_prio1']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['media'] != 1 ) {
                            $analysis['issues']['media'][$op_report['issues']['media']]['sum']++;
                            if ( $analysis['issues']['media'][$op_report['issues']['media']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['media'][$op_report['issues']['media']]['posts'][] = $res->ID;
                            }
                        }
                        
                        
                        if ( $op_report['issues']['media_missing_alt'] != 1 ) {
                            $analysis['issues']['media_missing_alt'][$op_report['issues']['media_missing_alt']]['sum']++;
                            if ( $analysis['issues']['media_missing_alt'][$op_report['issues']['media_missing_alt']]['sum'] <= WSKO_ONPAGE_ISSUE_MAX_POSTS ) {
                                $analysis['issues']['media_missing_alt'][$op_report['issues']['media_missing_alt']]['posts'][] = $res->ID;
                            }
                        }
                        
                        $analysis['total_pages']++;
                    } else {
                        $op_errors++;
                    }
                
                }
                if ( $new_rows ) {
                    WSKO_Class_Cache::set_cache_row( 0, array( array(
                        'set'  => 'onpage',
                        'rows' => $new_rows,
                    ) ), true );
                }
                if ( $op_errors ) {
                    WSKO_Class_Helper::report_error( 'error', 'E3: ' . __( 'Onpage - Generation Errors', 'wsko' ), 'The last onpage analysis was unable to generate a report for all posts. ' . $op_errors . ' posts are missing.' );
                }
                $offset++;
                
                if ( $query->post_count >= $step && !$limit_hit_flag ) {
                    WSKO_Class_Core::save_option( 'last_onpage_segment_count', ceil( $query->found_posts / $step ), true );
                    WSKO_Class_Core::save_option( 'last_onpage_offset', $offset, true );
                    $is_segment = true;
                } else {
                    WSKO_Class_Core::save_option( 'last_onpage_segment_count', false, true );
                    WSKO_Class_Core::save_option( 'last_onpage_offset', false, true );
                    $is_segment = false;
                }
            
            } else {
                WSKO_Class_Helper::report_error( 'error', 'E4: ' . __( 'Onpage - Analysis No post types set', 'wsko' ), 'You have no post types set to be included in the onpage analysis!' );
                WSKO_Class_Helper::finish_heavy_operation( 'onpage_analysis' );
                return;
            }
            
            //}
            //while ($query->post_count >= $step && !$limit_hit_flag);
            //$post = $t_post;
            //setup_postdata($post);
            update_option( 'wsko_onpage_temp', $analysis );
            WSKO_Class_Helper::finish_heavy_operation( 'onpage_analysis' );
            if ( !$is_segment ) {
                WSKO_Class_Onpage::finish_onpage_analysis();
            }
        } catch ( \Exception $error ) {
            WSKO_Class_Helper::report_error( 'exception', 'E5: ' . __( 'Onpage - Analysis Generation Error', 'wsko' ), $error );
            WSKO_Class_Helper::finish_heavy_operation( 'onpage_analysis' );
            WSKO_Class_Core::save_option( 'onpage_analysis_running', false, true );
        }
    }
    
    public static function finish_onpage_analysis()
    {
        try {
            WSKO_Class_Helper::refresh_wp_cache( 'cron', true );
            $analysis = get_option( 'wsko_onpage_temp' );
            
            if ( $analysis ) {
                foreach ( $analysis['t_titles'] as $k => $title ) {
                    
                    if ( $title['length'] < WSKO_ONPAGE_TITLE_MIN ) {
                        $analysis['title_length_dist']['too_short'] += $title['count'];
                    } else {
                        
                        if ( $title['length'] > WSKO_ONPAGE_TITLE_MAX ) {
                            $analysis['title_length_dist']['too_long'] += $title['count'];
                        } else {
                            $analysis['title_length_dist']['ok'] += $title['count'];
                        }
                    
                    }
                    
                    
                    if ( $title['count'] > 1 ) {
                        
                        if ( $title['count'] > 10 ) {
                            $ind = 10;
                        } else {
                            $ind = $title['count'] - 1;
                        }
                        
                        $analysis['title_duplicate_dist'][$ind]++;
                        // += $title['count'];
                    } else {
                        $analysis['title_duplicate_dist'][0]++;
                    }
                    
                    $pages = $title['dup_posts'];
                    if ( $pages ) {
                        foreach ( $pages as $p ) {
                            $posts = $title['dup_posts'];
                            if ( ($key = array_search( $p, $posts )) !== false ) {
                                unset( $posts[$key] );
                            }
                            WSKO_Class_Cache::update_cache_data_row( array(
                                'post_id' => $p,
                            ), 'onpage', array(
                                'title_duplicates'      => $title['count'] - 1,
                                'title_duplicate_posts' => serialize( $posts ),
                            ) );
                            //$b_data);
                        }
                    }
                    if ( $title['count'] > $analysis['max']['title_dupl'] ) {
                        $analysis['max']['title_dupl'] = $title['count'];
                    }
                }
                foreach ( $analysis['t_desc'] as $k => $desc ) {
                    
                    if ( $desc['length'] < WSKO_ONPAGE_DESC_MIN ) {
                        $analysis['desc_length_dist']['too_short'] += $desc['count'];
                    } else {
                        
                        if ( $desc['length'] > WSKO_ONPAGE_DESC_MAX ) {
                            $analysis['desc_length_dist']['too_long'] += $desc['count'];
                        } else {
                            $analysis['desc_length_dist']['ok'] += $desc['count'];
                        }
                    
                    }
                    
                    
                    if ( $desc['count'] > 1 ) {
                        
                        if ( $desc['count'] > 10 ) {
                            $ind = 10;
                        } else {
                            $ind = $desc['count'] - 1;
                        }
                        
                        $analysis['desc_duplicate_dist'][$ind]++;
                        // += $desc['count'];
                    } else {
                        $analysis['desc_duplicate_dist'][0]++;
                    }
                    
                    $pages = $desc['dup_posts'];
                    if ( $pages ) {
                        foreach ( $pages as $p ) {
                            $posts = $desc['dup_posts'];
                            if ( ($key = array_search( $p, $posts )) !== false ) {
                                unset( $posts[$key] );
                            }
                            WSKO_Class_Cache::update_cache_data_row( array(
                                'post_id' => $p,
                            ), 'onpage', array(
                                'desc_duplicates'      => $title['count'] - 1,
                                'desc_duplicate_posts' => serialize( $posts ),
                            ) );
                            //$b_data);
                        }
                    }
                    if ( $desc['count'] > $analysis['max']['desc_dupl'] ) {
                        $analysis['max']['desc_dupl'] = $desc['count'];
                    }
                }
                foreach ( $analysis['t_keywords'] as $k => $keyword ) {
                    $analysis['priority_kw_duplicates'][$k] = array(
                        'keyword'    => $k,
                        'posts'      => $keyword['dup_posts'],
                        'post_count' => $keyword['count'],
                    );
                    
                    if ( $keyword['count'] > 1 ) {
                        
                        if ( $keyword['count'] > 8 ) {
                            $ind = 8;
                        } else {
                            $ind = $keyword['count'] - 1;
                        }
                        
                        $analysis['priority_kw_duplicate_dist'][$ind]++;
                        // += $desc['count'];
                    } else {
                        $analysis['priority_kw_duplicate_dist'][0]++;
                    }
                    
                    if ( $keyword['count'] > $analysis['max']['kw_dupl'] ) {
                        $analysis['max']['kw_dupl'] = $keyword['count'];
                    }
                }
                $analysis['titles_count'] = count( $analysis['t_titles'] );
                $analysis['desc_count'] = count( $analysis['t_desc'] );
                unset( $analysis['t_titles'] );
                unset( $analysis['t_desc'] );
                unset( $analysis['t_keywords'] );
                
                if ( $analysis['total_pages'] > 0 ) {
                    $analysis['onpage_score_avg'] = round( $analysis['onpage_score'] / $analysis['total_pages'], 2 );
                } else {
                    $analysis['onpage_score_avg'] = 0;
                }
                
                $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
                
                if ( isset( $global_analysis['current_report'] ) && $global_analysis['current_report'] ) {
                    $global_analysis['last_onpage_score'] = $global_analysis['current_report']['onpage_score_avg'];
                    $last_issues = array();
                    foreach ( $global_analysis['current_report']['issues'] as $k => $issue ) {
                        $last_issues[$k] = array();
                        foreach ( $issue as $type => $count ) {
                            $last_issues[$k][$type] = $count['sum'];
                        }
                    }
                    $global_analysis['last_issues'] = $last_issues;
                }
                
                $global_analysis['current_report'] = $analysis;
                $global_analysis['new_report'] = false;
                if ( !isset( $global_analysis['onpage_history'] ) ) {
                    $global_analysis['onpage_history'] = array();
                }
                $global_analysis['onpage_history'][WSKO_Class_Helper::get_midnight( $global_analysis['current_report']['started'] )] = $global_analysis['current_report']['onpage_score_avg'];
                $history_timeout = time() - 60 * 60 * 24 * WSKO_HISTORY_LIMIT;
                foreach ( $global_analysis['onpage_history'] as $k => $d ) {
                    if ( $k < $history_timeout ) {
                        unset( $global_analysis['onpage_history'][$k] );
                    }
                }
                WSKO_Class_Onpage::save_onpage_analysis( $global_analysis );
                WSKO_Class_Onpage::reset_op_dirty_posts();
                delete_option( 'wsko_onpage_temp' );
                WSKO_Class_Core::save_option( 'onpage_analysis_running', false, true );
            }
        
        } catch ( \Exception $error ) {
            WSKO_Class_Helper::report_error( 'exception', 'E6: ' . __( 'Onpage - Analysis Finish Error', 'wsko' ), $error );
            WSKO_Class_Core::save_option( 'onpage_analysis_running', false, true );
        }
    }
    
    public static function get_onpage_crawl_data( $options = array(), &$stats_out = false )
    {
        $limit = ( isset( $options['limit'] ) ? intval( $options['limit'] ) : false );
        $offset = ( isset( $options['offset'] ) ? intval( $options['offset'] ) : false );
        $orderby = ( isset( $options['orderby'] ) ? $options['orderby'] : false );
        $where = ( isset( $options['where'] ) ? $options['where'] : false );
        $having = ( isset( $options['having'] ) ? $options['having'] : false );
        $with_pre = ( isset( $options['with_pre'] ) ? $options['with_pre'] : false );
        $rows = array();
        $for_url = ( isset( $options['for_url'] ) ? $options['for_url'] : false );
        $for_post = ( isset( $options['for_post'] ) ? $options['for_post'] : false );
        $args = array(
            'onpage' => array(),
        );
        //$args_c = array('onpage' => array());
        if ( $for_url ) {
            $args['onpage']['url'] = array(
                'join' => false,
                'eval' => '="' . esc_sql( $for_url ) . '"',
            );
        }
        if ( $for_post ) {
            $args['onpage']['post_id'] = array(
                'join' => false,
                'eval' => '=' . intval( $for_post ),
            );
        }
        $override_join = false;
        //array('bl_domains' => array('key'=> 'domain', 'table_r' => 'analytics', 'key_r' => 'domain'));
        $table_onpage = WSKO_Class_Cache::get_table_prefix( 'onpage' );
        $vals = $table_onpage . '.*, wp_post.post_title';
        $rows = WSKO_Class_Cache::get_cache_rows(
            0,
            0,
            $args,
            array(
            'join_post'     => true,
            'suffix'        => (( $limit ? 'LIMIT ' . $limit . ' ' : '' )) . (( $offset ? 'OFFSET ' . $offset . ' ' : '' )),
            'having'        => $having,
            'where'         => $where,
            'vals'          => $vals,
            'override_join' => $override_join,
        ),
            $orderby
        );
        
        if ( $stats_out ) {
            $stats_out = array(
                'filtered' => 0,
                'total'    => 0,
            );
            $cache_stats = WSKO_Class_Cache::get_cache_rows(
                0,
                0,
                $args,
                array(
                'join_post'     => true,
                'override_join' => $override_join,
                'prefix'        => "SELECT COUNT(*) as num_filtered FROM(",
                'suffix'        => ') temp',
                'where'         => $where,
                'having'        => $having,
                'vals'          => $vals,
            ),
                false
            );
            
            if ( $cache_stats ) {
                $stats_out['filtered'] = $cache_stats[0]->num_filtered;
                $stats_out['total'] = $cache_stats[0]->num_filtered;
            }
        
        }
        
        foreach ( $rows as $k => $r ) {
            $rows[$k]->prio1_kw_den = unserialize( $rows[$k]->prio1_kw_den );
            $rows[$k]->prio2_kw_den = unserialize( $rows[$k]->prio2_kw_den );
            $rows[$k]->desc_duplicate_posts = unserialize( $rows[$k]->desc_duplicate_posts );
            $rows[$k]->title_duplicate_posts = unserialize( $rows[$k]->title_duplicate_posts );
        }
        return $rows;
    }
    
    public static function get_onpage_score_graph( $index = false )
    {
        $args = array(
            'onpage' => array(),
        );
        $override_join = false;
        $prefix = 'SELECT score_range, COUNT(*) as count_pages FROM(';
        $vals = 'CASE WHEN onpage_score >= 0 AND onpage_score < 10 THEN 10 ' . 'WHEN onpage_score >= 10 AND onpage_score < 20 THEN 20 ' . 'WHEN onpage_score >= 20 AND onpage_score < 30 THEN 30 ' . 'WHEN onpage_score >= 30 AND onpage_score < 40 THEN 40 ' . 'WHEN onpage_score >= 40 AND onpage_score < 50 THEN 50 ' . 'WHEN onpage_score >= 50 AND onpage_score < 60 THEN 60 ' . 'WHEN onpage_score >= 60 AND onpage_score < 70 THEN 70 ' . 'WHEN onpage_score >= 70 AND onpage_score < 80 THEN 80 ' . 'WHEN onpage_score >= 80 AND onpage_score < 90 THEN 90 ' . 'WHEN onpage_score >= 90 AND onpage_score <= 100 THEN 100 END as score_range ';
        $res = WSKO_Class_Cache::get_cache_rows(
            0,
            0,
            $args,
            array(
            'override_join' => $override_join,
            'prefix'        => $prefix,
            'vals'          => $vals,
            'where'         => array(
            'onpage_score' => ' IS NOT NULL',
        ),
            'suffix'        => ') temp GROUP BY score_range ORDER BY score_range',
        ),
            false
        );
        return $res;
    }
    
    public static function get_onpage_page_crawl_data( $url )
    {
        $crawl_data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
            'for_url' => $url,
        ) );
        if ( $crawl_data && isset( $crawl_data[0] ) ) {
            return $crawl_data[0];
        }
        return false;
    }
    
    public static function get_all_redirects()
    {
        return WSKO_Class_Cache::get_wp_option( 'wsko_redirects', function ( $a ) {
            if ( !$a ) {
                return array();
            }
            return $a;
        }, false );
    }
    
    public static function get_redirects()
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( isset( $redirect_data['redirects'] ) && is_array( $redirect_data['redirects'] ) ) {
            return $redirect_data['redirects'];
        }
        return array();
    }
    
    public static function get_page_redirects()
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        
        if ( isset( $redirect_data['post_redirects'] ) ) {
            $res = array();
            foreach ( $redirect_data['post_redirects'] as $post_id => $data ) {
                $res[] = array(
                    'post' => $post_id,
                    'to'   => $data['to'],
                    'type' => $data['type'],
                );
            }
            return $res;
        }
        
        return array();
    }
    
    public static function get_auto_redirects( $type )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( isset( $redirect_data['auto_redirects'] ) ) {
            
            if ( $type ) {
                if ( isset( $redirect_data['auto_redirects'][$type] ) && $redirect_data['auto_redirects'][$type] ) {
                    return $redirect_data['auto_redirects'][$type];
                }
            } else {
                return $redirect_data['auto_redirects'];
            }
        
        }
        return false;
    }
    
    public static function set_redirect_404( $activate, $type, $custom )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        $redirect_data['redirect_404'] = array(
            'activate' => $activate,
            'type'     => $type,
            'custom'   => $custom,
        );
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function add_redirect(
        $page,
        $comp,
        $redirect_to,
        $comp_to,
        $type
    )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( !isset( $redirect_data['redirects'] ) ) {
            $redirect_data['redirects'] = array();
        }
        $redirect_data['redirects'][$comp . '|' . $page] = array(
            'page'    => $page,
            'comp'    => $comp,
            'target'  => $redirect_to,
            'comp_to' => $comp_to,
            'type'    => $type,
        );
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function remove_redirects( $redirects_rem )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        foreach ( $redirect_data['redirects'] as $k => $r ) {
            if ( in_array( $k, $redirects_rem ) ) {
                unset( $redirect_data['redirects'][$k] );
            }
        }
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function get_page_redirect( $post )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( isset( $redirect_data['post_redirects'][$post] ) ) {
            return $redirect_data['post_redirects'][$post];
        }
        return false;
    }
    
    public static function add_page_redirect( $post, $to, $type )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( !isset( $redirect_data['post_redirects'] ) ) {
            $redirect_data['post_redirects'] = array();
        }
        $redirect_data['post_redirects'][$post] = array(
            'to'   => $to,
            'type' => $type,
        );
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function remove_page_redirects( $post )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( isset( $redirect_data['post_redirects'][$post] ) ) {
            unset( $redirect_data['post_redirects'][$post] );
        }
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function update_automatic_redirects(
        $type,
        $arg,
        $new_slug,
        $add_new = true
    )
    {
        
        if ( $type && $arg ) {
            // && $new_slug)
            $redirect_data = WSKO_Class_Onpage::get_all_redirects();
            $redirects = array();
            if ( isset( $redirect_data['auto_redirects'] ) && $redirect_data['auto_redirects'] ) {
                $redirects = $redirect_data['auto_redirects'];
            }
            if ( !isset( $redirects[$type] ) ) {
                $redirects[$type] = array();
            }
            $old_slug = false;
            
            if ( $type == 'post_id' ) {
                //$old_slug = get_post_field('post_name', $arg);
                $old_link = get_permalink( intval( $arg ) );
                
                if ( rtrim( $old_link, '/' ) !== rtrim( home_url(), '/' ) ) {
                    
                    if ( !isset( $redirects[$type][$arg] ) ) {
                        $redirects[$type][$arg] = array();
                        if ( $add_new ) {
                            $redirects[$type][$arg] = array( $old_link );
                        }
                    } else {
                        if ( $add_new && !in_array( $old_link, $redirects[$type][$arg] ) ) {
                            $redirects[$type][$arg][] = $old_link;
                        }
                    }
                    
                    if ( $new_slug && ($key = array_search( $new_slug, $redirects[$type][$arg] )) !== false ) {
                        unset( $redirects[$type][$arg][$key] );
                    }
                    if ( !$redirects[$type][$arg] ) {
                        unset( $redirects[$type][$arg] );
                    }
                    $redirect_data['auto_redirects'] = $redirects;
                    WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
                }
            
            } else {
                $obj = false;
                
                if ( $type === 'post_type' ) {
                    $obj = get_post_type_object( $arg );
                } else {
                    if ( $type === 'post_tax' ) {
                        $obj = get_taxonomy( $arg );
                    }
                }
                
                
                if ( $obj ) {
                    $old_slug = ( $obj->rewrite ? $obj->rewrite['slug'] : '/' );
                    
                    if ( !isset( $redirects[$type][$arg] ) ) {
                        if ( $new_slug === false ) {
                            $new_slug = $old_slug;
                        }
                        $redirects[$type][$arg] = array(
                            'first_slug' => $old_slug,
                            'target'     => $new_slug,
                            'source'     => array(),
                        );
                    } else {
                        if ( $new_slug === false ) {
                            $new_slug = $redirects[$type][$arg]['first_slug'];
                        }
                        $redirects[$type][$arg]['target'] = $new_slug;
                    }
                    
                    if ( $add_new ) {
                        $redirects[$type][$arg]['source'][$old_slug] = WSKO_Class_Onpage::get_link_snapshot( $type, $arg );
                    }
                    if ( $new_slug !== false ) {
                        unset( $redirects[$type][$arg]['source'][$new_slug] );
                    }
                    if ( !$redirects[$type][$arg]['source'] ) {
                        unset( $redirects[$type][$arg] );
                    }
                    $redirect_data['auto_redirects'] = $redirects;
                    WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
                }
            
            }
            
            WSKO_Class_Crons::bind_redirect_check( true, time() + 10 );
        }
    
    }
    
    public static function remove_auto_redirect( $type, $arg, $key = false )
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        $redirects = array();
        if ( isset( $redirect_data['auto_redirects'] ) && $redirect_data['auto_redirects'] ) {
            $redirects = $redirect_data['auto_redirects'];
        }
        
        if ( $key !== false ) {
            
            if ( $type === 'post_id' ) {
                
                if ( $redirects && isset( $redirects[$type][$arg][$key] ) ) {
                    unset( $redirects[$type][$arg][$key] );
                    if ( !$redirects[$type][$arg] ) {
                        unset( $redirects[$type][$arg] );
                    }
                }
            
            } else {
                
                if ( $redirects && isset( $redirects[$type][$arg]['source'][$key] ) ) {
                    unset( $redirects[$type][$arg]['source'][$key] );
                    if ( !$redirects[$type][$arg]['source'] ) {
                        unset( $redirects[$type][$arg] );
                    }
                }
            
            }
        
        } else {
            if ( $redirects && isset( $redirects[$type][$arg] ) ) {
                unset( $redirects[$type][$arg] );
            }
        }
        
        $redirect_data['auto_redirects'] = $redirects;
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function clean_auto_redirects()
    {
        $redirect_data = WSKO_Class_Onpage::get_all_redirects();
        if ( isset( $redirect_data['auto_redirects']['post_id'] ) ) {
            foreach ( $redirect_data['auto_redirects']['post_id'] as $k => $red ) {
                
                if ( !get_post( $k ) ) {
                    foreach ( $red as $k2 => $r ) {
                        if ( WSKO_Class_Helper::format_url( $r, true ) == rtrim( get_permalink( $r ), '/' ) . '/' ) {
                            unset( $redirect_data['auto_redirects']['post_id'][$k][$k2] );
                        }
                    }
                    unset( $redirect_data['auto_redirects']['post_id'][$k] );
                }
            
            }
        }
        WSKO_Class_Cache::save_wp_option( 'wsko_redirects', $redirect_data );
    }
    
    public static function get_blocked_post_stati( $type )
    {
        switch ( $type ) {
            case 'redirects':
                return array(
                    'draft',
                    'auto-draft',
                    'pending',
                    'trash'
                );
        }
        return array();
    }
    
    public static function get_special_meta_object( $from )
    {
        $from_m = false;
        switch ( $from ) {
            //case 'post_id': $from_m = 'post_metas'; break;
            case 'post_tax':
                $from_m = 'post_tax_metas';
                break;
            case 'post_type':
                $from_m = 'post_type_metas';
                break;
                //case 'post_term': $from_m = 'post_term_metas'; break;
            //case 'post_term': $from_m = 'post_term_metas'; break;
            case 'post_archive':
                $from_m = 'post_archive_metas';
                break;
            case 'other':
                $from_m = 'other_metas';
                break;
        }
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        if ( isset( $onpage_data[$from_m] ) ) {
            return $onpage_data[$from_m];
        }
        return false;
    }
    
    public static function get_meta_object( $identifier, $from )
    {
        $metas = false;
        
        if ( $from == 'post_id' ) {
            $data = WSKO_Class_Core::get_post_data( $identifier );
            if ( isset( $data['meta_data'] ) ) {
                $metas = $data['meta_data'];
            }
        } else {
            
            if ( $from == 'post_term' ) {
                $term_id = intval( WSKO_Class_Helper::safe_explode( ':', $identifier )[1] );
                $data = WSKO_Class_Core::get_term_data( $term_id );
                if ( isset( $data['meta_data'] ) ) {
                    $metas = $data['meta_data'];
                }
            } else {
                $metas_a = WSKO_Class_Onpage::get_special_meta_object( $from );
                if ( isset( $metas_a[$identifier] ) ) {
                    $metas = $metas_a[$identifier];
                }
            }
        
        }
        
        
        if ( $metas ) {
            foreach ( $metas as $k => $m ) {
                if ( !$m ) {
                    unset( $metas[$k] );
                }
            }
            return $metas;
        }
        
        return false;
    }
    
    public static function set_meta_object( $identifier, $data, $from )
    {
        
        if ( $from == 'post_id' ) {
            $p_data = WSKO_Class_Core::get_post_data( $identifier );
            if ( !isset( $p_data['meta_data'] ) ) {
                $p_data['meta_data'] = array();
            }
            $p_data['meta_data'] = $data;
            WSKO_Class_Core::set_post_data( $identifier, $p_data );
            WSKO_Class_Onpage::set_op_post_dirty( $identifier, 'metas' );
        } else {
            
            if ( $from == 'post_term' ) {
                $term_id = intval( WSKO_Class_Helper::safe_explode( ':', $identifier )[1] );
                $t_data = WSKO_Class_Core::get_term_data( $term_id );
                if ( !isset( $t_data['meta_data'] ) ) {
                    $t_data['meta_data'] = array();
                }
                $t_data['meta_data'] = $data;
                WSKO_Class_Core::set_term_data( $term_id, $t_data );
            } else {
                $from_m = false;
                switch ( $from ) {
                    //case 'post_id': $from_m = 'post_metas'; break;
                    case 'post_type':
                        $from_m = 'post_type_metas';
                        break;
                    case 'post_tax':
                        $from_m = 'post_tax_metas';
                        break;
                        //case 'post_term': $from_m = 'post_term_metas'; break;
                    //case 'post_term': $from_m = 'post_term_metas'; break;
                    case 'post_archive':
                        $from_m = 'post_archive_metas';
                        break;
                    case 'other':
                        $from_m = 'other_metas';
                        break;
                }
                $onpage_data = WSKO_Class_Onpage::get_onpage_data();
                
                if ( isset( $onpage_data[$from_m] ) && is_array( $onpage_data[$from_m] ) ) {
                    $onpage_data[$from_m][$identifier] = $data;
                } else {
                    $onpage_data[$from_m] = array(
                        $identifier => $data,
                    );
                }
                
                WSKO_Class_Onpage::set_onpage_data( $onpage_data );
            }
        
        }
    
    }
    
    public static function unset_meta_object( $identifier, $from )
    {
        $metas = false;
        
        if ( $from == 'post_id' ) {
            $p_data = WSKO_Class_Core::get_post_data( $identifier );
            if ( isset( $p_data['meta_data'] ) ) {
                unset( $p_data['meta_data'] );
            }
            WSKO_Class_Core::set_post_data( $identifier, $p_data );
            WSKO_Class_Onpage::set_op_post_dirty( $identifier, 'metas' );
        } else {
            
            if ( $from == 'post_term' ) {
                $t_data = WSKO_Class_Core::get_term_data( $identifier );
                if ( isset( $t_data['meta_data'] ) ) {
                    unset( $t_data['meta_data'] );
                }
                WSKO_Class_Core::set_term_data( $identifier, $t_data );
            } else {
                $from_m = false;
                switch ( $from ) {
                    //case 'post_id': $from_m = 'post_metas'; break;
                    case 'post_type':
                        $from_m = 'post_type_metas';
                        break;
                    case 'post_tax':
                        $from_m = 'post_tax_metas';
                        break;
                        //case 'post_term': $from_m = 'post_term_metas'; break;
                    //case 'post_term': $from_m = 'post_term_metas'; break;
                    case 'post_archive':
                        $from_m = 'post_archive_metas';
                        break;
                    case 'other':
                        $from_m = 'other_metas';
                        break;
                }
                $onpage_data = WSKO_Class_Onpage::get_onpage_data();
                if ( isset( $onpage_data[$from_m] ) && is_array( $onpage_data[$from_m] ) ) {
                    unset( $onpage_data[$from_m][$identifier] );
                }
                WSKO_Class_Onpage::set_onpage_data( $onpage_data );
            }
        
        }
    
    }
    
    static  $post_meta_cache ;
    public static function get_post_metas( $preview = false )
    {
        $metas = false;
        $is_special = false;
        
        if ( !$preview ) {
            if ( static::$post_meta_cache ) {
                return static::$post_meta_cache;
            }
            
            if ( is_front_page() && is_home() ) {
                // Default homepage
                $metas_t = WSKO_Class_Onpage::get_meta_object( 'home', 'other' );
                
                if ( $metas_t ) {
                    
                    if ( $metas ) {
                        $metas = $metas + $metas_t;
                    } else {
                        $metas = $metas_t;
                    }
                    
                    $is_special = true;
                }
            
            } else {
                
                if ( is_front_page() ) {
                    // static homepage
                    $metas_t = WSKO_Class_Onpage::get_meta_object( 'home', 'other' );
                    
                    if ( $metas_t ) {
                        
                        if ( $metas ) {
                            $metas = $metas + $metas_t;
                        } else {
                            $metas = $metas_t;
                        }
                        
                        $is_special = true;
                    }
                
                } else {
                    
                    if ( is_home() ) {
                        // blog homepage
                        $metas_t = WSKO_Class_Onpage::get_meta_object( 'blog', 'other' );
                        
                        if ( $metas_t ) {
                            
                            if ( $metas ) {
                                $metas = $metas + $metas_t;
                            } else {
                                $metas = $metas_t;
                            }
                            
                            $is_special = true;
                        }
                    
                    } else {
                        
                        if ( is_post_type_archive() ) {
                            $post_type = get_post_type();
                            $metas_t = WSKO_Class_Onpage::get_meta_object( $post_type, 'post_archive' );
                            if ( $metas_t ) {
                                
                                if ( $metas ) {
                                    $metas = $metas + $metas_t;
                                } else {
                                    $metas = $metas_t;
                                }
                            
                            }
                            $is_special = true;
                        } else {
                            
                            if ( is_tax() || is_category() || is_tag() ) {
                                $queried_object = get_queried_object();
                                
                                if ( $queried_object && $queried_object->taxonomy ) {
                                    $taxonomy = $queried_object->taxonomy;
                                    $term = $queried_object->term_id;
                                    
                                    if ( $term ) {
                                        $metas_t = WSKO_Class_Onpage::get_meta_object( $taxonomy . ':' . $term, 'post_term' );
                                        if ( $metas_t ) {
                                            
                                            if ( $metas ) {
                                                $metas = $metas + $metas_t;
                                            } else {
                                                $metas = $metas_t;
                                            }
                                        
                                        }
                                    }
                                    
                                    $metas_t = WSKO_Class_Onpage::get_meta_object( $taxonomy, 'post_tax' );
                                    if ( $metas_t ) {
                                        
                                        if ( $metas ) {
                                            $metas = $metas + $metas_t;
                                        } else {
                                            $metas = $metas_t;
                                        }
                                    
                                    }
                                }
                                
                                $is_special = true;
                            }
                        
                        }
                    
                    }
                
                }
            
            }
        
        }
        
        
        if ( !$is_special ) {
            global  $post ;
            $post_r = false;
            
            if ( is_front_page() && is_home() ) {
                if ( $t_post = get_option( 'page_on_front' ) ) {
                    $post_r = get_post( $t_post );
                }
            } else {
                
                if ( is_front_page() ) {
                    if ( $t_post = get_option( 'page_on_front' ) ) {
                        $post_r = get_post( $t_post );
                    }
                } else {
                    
                    if ( is_home() ) {
                        if ( $t_post = get_option( 'page_for_posts' ) ) {
                            $post_r = get_post( $t_post );
                        }
                    } else {
                        if ( $preview || is_singular() ) {
                            $post_r = $post;
                        }
                    }
                
                }
            
            }
            
            
            if ( $post_r ) {
                $metas_t = WSKO_Class_Onpage::get_meta_object( $post_r->ID, 'post_id' );
                if ( $metas_t ) {
                    
                    if ( $metas ) {
                        $metas = $metas + $metas_t;
                    } else {
                        $metas = $metas_t;
                    }
                
                }
                $metas_t = WSKO_Class_Onpage::get_meta_object( $post_r->post_type, 'post_type' );
                if ( $metas_t ) {
                    
                    if ( $metas ) {
                        $metas = $metas + $metas_t;
                    } else {
                        $metas = $metas_t;
                    }
                
                }
            }
        
        }
        
        if ( !$preview ) {
            //if ($metas)
            static::$post_meta_cache = $metas;
        }
        return $metas;
    }
    
    public static function calculate_meta( $text, $overrides = array() )
    {
        $override_post = ( isset( $overrides['post'] ) ? $overrides['post'] : false );
        $override_tax = ( isset( $overrides['tax'] ) ? $overrides['tax'] : false );
        $override_term = ( isset( $overrides['term'] ) ? $overrides['term'] : false );
        $override_post_title = ( isset( $overrides['post_title'] ) ? $overrides['post_title'] : false );
        $override_post_content = ( isset( $overrides['post_content'] ) ? $overrides['post_content'] : false );
        
        if ( $override_post ) {
            $post = get_post( $override_post );
        } else {
            global  $post ;
        }
        
        
        if ( $post ) {
            preg_match_all( '/%post:(.*?)%/s', $text, $matches );
            foreach ( $matches[1] as $k => $match ) {
                $temp = WSKO_Class_Helper::safe_explode( ':', $match );
                if ( !empty($temp) ) {
                    switch ( $temp[0] ) {
                        case 'post_title':
                            $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( ( $override_post_title ? $override_post_title : $post->post_title ), false ), $text );
                            break;
                        case 'post_content':
                            $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( ( $override_post_content ? $override_post_content : $post->post_content ), false ), $text );
                            break;
                        case 'post_excerpt':
                            $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( $post->post_excerpt, false ), $text );
                            break;
                        case 'post_author':
                            $text = str_replace( $matches[0][$k], get_the_author_meta( 'display_name', $post->post_author ), $text );
                            break;
                    }
                }
            }
            preg_match_all( '/%meta:(.*?)%/s', $text, $matches );
            foreach ( $matches[1] as $k => $match ) {
                $temp = WSKO_Class_Helper::safe_explode( ':', $match );
                if ( !empty($temp) ) {
                    $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( get_post_meta( $post->ID, $temp[0], true ) ), $text );
                }
            }
            preg_match_all( '/%tax:(.*?)%/s', $text, $matches );
            foreach ( $matches[1] as $k => $match ) {
                $temp = WSKO_Class_Helper::safe_explode( ':', $match );
                
                if ( !empty($temp) ) {
                    $terms = wp_get_post_terms( $post->ID, $temp[0], array(
                        'fields' => 'names',
                    ) );
                    
                    if ( $terms ) {
                        $terms = implode( ', ', $terms );
                        $text = str_replace( $matches[0][$k], $terms, $text );
                    } else {
                        $text = str_replace( $matches[0][$k], '', $text );
                    }
                
                }
            
            }
        }
        
        
        if ( $override_tax || $override_term || is_tax() || is_category() || is_tag() ) {
            $queried_object = get_queried_object();
            $taxonomy = ( $override_tax ? $override_tax : (( $queried_object && $queried_object->taxonomy ? $queried_object->taxonomy : false )) );
            $term = ( $override_term ? $override_term : (( $queried_object && $queried_object->term_id ? $queried_object->term_id : false )) );
            
            if ( $taxonomy && $term ) {
                $term = get_term_by( 'id', $term, $taxonomy );
                
                if ( $term ) {
                    preg_match_all( '/%term:(.*?)%/s', $text, $matches );
                    foreach ( $matches[1] as $k => $match ) {
                        $temp = WSKO_Class_Helper::safe_explode( ':', $match );
                        if ( !empty($temp) ) {
                            switch ( $temp[0] ) {
                                case 'term_title':
                                    $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( $term->name, 'the_title' ), $text );
                                    break;
                                case 'term_desc':
                                    $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( $term->description, 'the_content' ), $text );
                                    break;
                            }
                        }
                    }
                    preg_match_all( '/%term_meta:(.*?)%/s', $text, $matches );
                    foreach ( $matches[1] as $k => $match ) {
                        $temp = WSKO_Class_Helper::safe_explode( ':', $match );
                        if ( !empty($temp) ) {
                            $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( get_term_meta( $term->term_id, $temp[0], true ) ), $text );
                        }
                    }
                }
            
            }
        
        }
        
        preg_match_all( '/%site:(.*?)%/s', $text, $matches );
        foreach ( $matches[1] as $k => $match ) {
            $temp = WSKO_Class_Helper::safe_explode( ':', $match );
            if ( !empty($temp) ) {
                switch ( $temp[0] ) {
                    case 'blog_name':
                        $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( get_bloginfo(), false ), $text );
                        break;
                    case 'blog_tagline':
                        $text = str_replace( $matches[0][$k], WSKO_Class_Helper::sanitize_meta( get_bloginfo( 'description' ), false ), $text );
                        break;
                }
            }
        }
        return $text;
    }
    
    public static function get_sitemap_params()
    {
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        $sitemap_params = array(
            'types' => array(),
            'stati' => array(),
            'posts' => array(),
        );
        if ( isset( $onpage_data['sitemap_params'] ) && $onpage_data['sitemap_params'] ) {
            $sitemap_params = $onpage_data['sitemap_params'];
        }
        return $sitemap_params;
    }
    
    public static function set_sitemap_params( $data )
    {
        $onpage_data = WSKO_Class_Onpage::get_onpage_data();
        $onpage_data['sitemap_params'] = $data;
        WSKO_Class_Onpage::set_onpage_data( $onpage_data );
    }
    
    public static function generate_sitemap()
    {
        $sitemap_path = ABSPATH;
        
        if ( is_writable( $sitemap_path ) ) {
            try {
                $gen_params = WSKO_Class_Onpage::get_sitemap_params();
                
                if ( $gen_params && $gen_params['types'] ) {
                    // && $gen_params['stati'])
                    $post_types = WSKO_Class_Helper::get_public_post_types( 'names' );
                    foreach ( $post_types as $k => $pt ) {
                        if ( !isset( $gen_params['types'][$pt] ) ) {
                            unset( $post_types[$k] );
                        }
                    }
                    $taxonomies = WSKO_Class_Helper::get_public_taxonomies( 'names' );
                    foreach ( $taxonomies as $k => $tax ) {
                        if ( !isset( $gen_params['tax'][$tax] ) ) {
                            unset( $taxonomies[$k] );
                        }
                    }
                    $sitemap_index = 0;
                    
                    if ( $post_types ) {
                        $xml_main = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><sitemapindex/>' );
                        $xml_main->addAttribute( 'xmlns:xmlns:xsi', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                        $xml_main->addAttribute( 'xsi:xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                        $xml_main->addAttribute( 'xmlns:xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                        $count = 0;
                        $offset = 0;
                        $step = 500;
                        //batch queries
                        $xml_saved = true;
                        $xml = null;
                        do {
                            $args = array(
                                'posts_per_page' => $step,
                                'offset'         => $offset * $step,
                                'post_type'      => $post_types,
                                'post_status'    => 'publish',
                                'fields'         => 'ids',
                            );
                            if ( isset( $gen_params['stati'] ) && $gen_params['stati'] ) {
                                $args['post_status'] = $gen_params['stati'];
                            }
                            if ( isset( $gen_params['excluded_posts'] ) && is_array( $gen_params['excluded_posts'] ) && $gen_params['excluded_posts'] ) {
                                $args['post__not_in'] = $gen_params['excluded_posts'];
                            }
                            $query = new WP_Query( $args );
                            $results = $query->posts;
                            foreach ( $results as $res ) {
                                
                                if ( $count === 0 ) {
                                    
                                    if ( !$xml_saved ) {
                                        $path = '/sitemap_bst' . (( $sitemap_index ? $sitemap_index : '' )) . '.xml';
                                        $sitemap = $xml_main->addChild( 'sitemap' );
                                        $sitemap->addChild( 'loc', home_url( $path ) );
                                        $sitemap->addChild( 'lastmod', date( 'c' ) );
                                        $xml->asXML( $sitemap_path . $path );
                                        $sitemap_index++;
                                    }
                                    
                                    $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><urlset/>' );
                                    $xml->addAttribute( 'xmlns:xmlns:xsi', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                    $xml->addAttribute( 'xsi:xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                    $xml->addAttribute( 'xmlns:xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                    $xml_saved = false;
                                }
                                
                                $post_type = get_post_type( $res );
                                $freq = false;
                                $prio = false;
                                
                                if ( isset( $gen_params['types'][$post_type] ) ) {
                                    $freq = $gen_params['types'][$post_type]['freq'];
                                    $prio = $gen_params['types'][$post_type]['prio'];
                                }
                                
                                /*if (isset($gen_params['posts'][$res]))
                                		{
                                			if ($gen_params['posts'][$res]['freq'])
                                				$freq = $gen_params['posts'][$res]['freq'];
                                			if ($gen_params['posts'][$res]['prio'])
                                				$prio = $gen_params['posts'][$res]['prio'];
                                		}*/
                                switch ( $freq ) {
                                    case 'always':
                                        break;
                                    case 'hourly':
                                        break;
                                    case 'daily':
                                        break;
                                    case 'weekly':
                                        break;
                                    case 'monthly':
                                        break;
                                    case 'yearly':
                                        break;
                                    case 'never':
                                        break;
                                    default:
                                        $freq = 'never';
                                        break;
                                }
                                
                                if ( $prio > 1 ) {
                                    $prio = 1;
                                } else {
                                    if ( $prio < 0 ) {
                                        $prio = 0;
                                    }
                                }
                                
                                $url = $xml->addChild( 'url' );
                                $url->addChild( 'loc', get_permalink( $res ) );
                                $url->addChild( 'lastmod', date( 'c', get_post_modified_time( 'U', false, $res ) ) );
                                if ( $freq ) {
                                    $url->addChild( 'changefreq', $freq );
                                }
                                if ( $prio || $prio === 0 ) {
                                    $url->addChild( 'priority', $prio );
                                }
                                $count++;
                                if ( $count >= WSKO_SITEMAP_LIMIT ) {
                                    $count = 0;
                                }
                            }
                            $offset++;
                        } while ($query->post_count >= $step);
                        foreach ( $taxonomies as $tax ) {
                            $terms = get_terms( array(
                                'taxonomy' => $tax,
                            ) );
                            foreach ( $terms as $term ) {
                                
                                if ( !isset( $gen_params['excluded_terms'] ) || !is_array( $gen_params['excluded_terms'] ) || ($key = array_search( $tax . ':' . $term->term_id, $gen_params['excluded_terms'] )) === false ) {
                                    
                                    if ( $count === 0 ) {
                                        
                                        if ( !$xml_saved ) {
                                            $path = '/sitemap_bst' . (( $sitemap_index ? $sitemap_index : '' )) . '.xml';
                                            $sitemap = $xml_main->addChild( 'sitemap' );
                                            $sitemap->addChild( 'loc', home_url( $path ) );
                                            $sitemap->addChild( 'lastmod', date( 'c' ) );
                                            $xml->asXML( $sitemap_path . $path );
                                            $sitemap_index++;
                                        }
                                        
                                        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><urlset/>' );
                                        $xml->addAttribute( 'xmlns:xmlns:xsi', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                        $xml->addAttribute( 'xsi:xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                        $xml->addAttribute( 'xmlns:xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
                                        $xml_saved = false;
                                    }
                                    
                                    $freq = false;
                                    $prio = false;
                                    
                                    if ( isset( $gen_params['tax'][$tax] ) ) {
                                        $freq = $gen_params['tax'][$tax]['freq'];
                                        $prio = $gen_params['tax'][$tax]['prio'];
                                    }
                                    
                                    switch ( $freq ) {
                                        case 'always':
                                            break;
                                        case 'hourly':
                                            break;
                                        case 'daily':
                                            break;
                                        case 'weekly':
                                            break;
                                        case 'monthly':
                                            break;
                                        case 'yearly':
                                            break;
                                        case 'never':
                                            break;
                                        default:
                                            $freq = 'never';
                                            break;
                                    }
                                    
                                    if ( $prio > 1 ) {
                                        $prio = 1;
                                    } else {
                                        if ( $prio < 0 ) {
                                            $prio = 0;
                                        }
                                    }
                                    
                                    $url = $xml->addChild( 'url' );
                                    $url->addChild( 'loc', get_term_link( $term ) );
                                    $url->addChild( 'lastmod', date( 'c', time() ) );
                                    if ( $freq ) {
                                        $url->addChild( 'changefreq', $freq );
                                    }
                                    if ( $prio || $prio === 0 ) {
                                        $url->addChild( 'priority', $prio );
                                    }
                                    $count++;
                                    if ( $count >= WSKO_SITEMAP_LIMIT ) {
                                        $count = 0;
                                    }
                                }
                            
                            }
                        }
                        
                        if ( $xml_main ) {
                            if ( $xml ) {
                                
                                if ( !$xml_saved ) {
                                    $path = '/sitemap_bst' . (( $sitemap_index ? $sitemap_index : '' )) . '.xml';
                                    $sitemap = $xml_main->addChild( 'sitemap' );
                                    $sitemap->addChild( 'loc', home_url( $path ) );
                                    $sitemap->addChild( 'lastmod', date( 'c' ) );
                                    $xml->asXML( $sitemap_path . $path );
                                }
                            
                            }
                            $xml_main->asXML( $sitemap_path . '/sitemap.xml' );
                            $onpage_data = WSKO_Class_Onpage::get_onpage_data();
                            $onpage_data['last_sitemap_generation'] = WSKO_Class_Helper::get_current_time();
                            WSKO_Class_Onpage::set_onpage_data( $onpage_data );
                            return true;
                        }
                    
                    }
                
                }
            
            } catch ( \Exception $ex ) {
                WSKO_Class_Helper::report_error( 'exception', 'E7: ' . __( 'Onpage - Sitemap Generation Error', 'wsko' ), $ex );
            }
        } else {
            WSKO_Class_Helper::report_error( 'error', 'E8: ' . __( 'Onpage - Sitemap Generation Error', 'wsko' ), 'Sitemap path (' . ABSPATH . ') is not writable!' );
        }
        
        return false;
    }
    
    public static function upload_sitemap()
    {
        try {
            
            if ( WSKO_Class_Core::get_setting( 'automatic_sitemap' ) && WSKO_Class_Core::get_setting( 'automatic_sitemap_ping' ) && file_exists( ABSPATH . '/sitemap.xml' ) ) {
                $response = WSKO_Class_Helper::get_from_url( WSKO_Class_Search::get_external_link( 'ping_google', home_url( 'sitemap.xml' ) ) );
                if ( !$response ) {
                    return false;
                }
                $response = WSKO_Class_Helper::get_from_url( WSKO_Class_Search::get_external_link( 'ping_bing', home_url( 'sitemap.xml' ) ) );
                if ( !$response ) {
                    return false;
                }
                $onpage_data = WSKO_Class_Onpage::get_onpage_data();
                $onpage_data['last_sitemap_upload'] = time();
                WSKO_Class_Onpage::set_onpage_data( $onpage_data );
                return true;
            }
        
        } catch ( \Exception $ex ) {
            WSKO_Class_Helper::report_error( 'exception', 'E9: ' . __( 'Onpage - Sitemap Upload Error', 'wsko' ), $ex );
        }
        return false;
    }
    
    public static function get_priority_keywords( $post_id )
    {
        
        if ( WSKO_Class_Core::is_demo() && function_exists( 'wsko_get_priority_keywords' ) ) {
            return wsko_get_priority_keywords( $post_id );
        } else {
            $data = WSKO_Class_Core::get_post_data( $post_id );
            
            if ( isset( $data['priority_keywords'] ) ) {
                $pks = $data['priority_keywords'];
                foreach ( $pks as $k => $pk ) {
                    if ( !WSKO_Class_Core::is_premium() && $pk['prio'] != 1 ) {
                        unset( $pks[$k] );
                    }
                }
                return $pks;
            }
        
        }
        
        return false;
    }
    
    public static function set_priority_keywords( $post_id, $keywords )
    {
        
        if ( WSKO_Class_Core::is_demo() && function_exists( 'wsko_set_priority_keywords' ) ) {
            return wsko_set_priority_keywords( $post_id, $keywords );
        } else {
            $data = WSKO_Class_Core::get_post_data( $post_id );
            $data['priority_keywords'] = $keywords;
            WSKO_Class_Core::set_post_data( $post_id, $data );
        }
    
    }
    
    public static function add_priority_keyword( $post_id, $keyword, $prio )
    {
        $prio = intval( $prio );
        $keyword = trim( strtolower( $keyword ) );
        
        if ( $post_id && $keyword && ($prio == 1 || WSKO_Class_Core::is_premium() && $prio == 2) ) {
            $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
            
            if ( !$priority_keywords ) {
                $priority_keywords = array(
                    $keyword => array(
                    'prio'    => $prio,
                    'similar' => array(),
                ),
                );
                WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
                WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'p_kw' );
            } else {
                
                if ( isset( $priority_keywords[$keyword] ) || count( $priority_keywords ) < (( WSKO_Class_Core::is_premium() ? 25 : 2 )) ) {
                    $priority_keywords[$keyword] = array(
                        'prio'    => $prio,
                        'similar' => array(),
                    );
                    WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
                    WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'p_kw' );
                }
            
            }
        
        }
    
    }
    
    public static function sort_priority_keywords( $post_id, $keywords, $is_similar = false )
    {
        $prio = intval( $prio );
        
        if ( $post_id && $keywords ) {
            $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
            
            if ( isset( $priority_keywords ) ) {
                
                if ( $is_similar ) {
                    $res = array();
                    foreach ( $keywords as $kw ) {
                        if ( isset( $priority_keywords[$is_similar]['similar'][$kw] ) ) {
                            $res[$kw] = $priority_keywords[$is_similar]['similar'][$kw];
                        }
                    }
                    $priority_keywords[$is_similar]['similar'] = $res;
                } else {
                    $res = array();
                    foreach ( $keywords as $kw ) {
                        if ( isset( $priority_keywords[$kw] ) ) {
                            $res[$kw] = $priority_keywords[$kw];
                        }
                    }
                    $priority_keywords = $res;
                }
                
                WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
            }
        
        }
    
    }
    
    public static function remove_priority_keyword( $post_id, $keyword )
    {
        $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
        
        if ( isset( $priority_keywords[$keyword] ) ) {
            unset( $priority_keywords[$keyword] );
            WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
            WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'p_kw' );
        }
    
    }
    
    public static function add_similar_priority_keyword( $post_id, $keyword_key, $keyword )
    {
        $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
        
        if ( isset( $priority_keywords[$keyword_key]['similar'] ) ) {
            $priority_keywords[$keyword_key]['similar'][$keyword] = true;
            WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
            WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'p_kw' );
        }
    
    }
    
    public static function remove_similar_priority_keyword( $post_id, $keyword_key, $keyword )
    {
        $priority_keywords = WSKO_Class_Onpage::get_priority_keywords( $post_id );
        
        if ( isset( $priority_keywords[$keyword_key]['similar'] ) ) {
            unset( $priority_keywords[$keyword_key]['similar'][$keyword] );
            WSKO_Class_Onpage::set_priority_keywords( $post_id, $priority_keywords );
            WSKO_Class_Onpage::set_op_post_dirty( $post_id, 'p_kw' );
        }
    
    }
    
    public static function set_op_post_dirty( $post_id, $source )
    {
        $posts = WSKO_Class_Core::get_option( 'onpage_changed_posts' );
        if ( !is_array( $posts ) ) {
            $posts = array();
        }
        
        if ( !isset( $posts[$post_id] ) ) {
            $posts[$post_id] = array( $source );
        } else {
            $posts[$post_id][] = $source;
        }
        
        WSKO_Class_Core::save_option( 'onpage_changed_posts', $posts );
    }
    
    public static function get_op_post_dirty( $post_id )
    {
        $posts = WSKO_Class_Core::get_option( 'onpage_changed_posts' );
        if ( $posts && isset( $posts[$post_id] ) ) {
            return $posts[$post_id];
        }
        return false;
    }
    
    public static function reset_op_dirty_posts()
    {
        WSKO_Class_Core::save_option( 'onpage_changed_posts', array() );
    }
    
    public static function get_unused_internal_linking( $post_id )
    {
        $post_url = get_permalink( $post_id );
        $wsko_data = WSKO_Class_Core::get_data();
        $res = array(
            'data_outgoing' => array(),
            'kw_register'   => array(),
            'has_kw_data'   => false,
        );
        return $res;
    }
    
    //Singleton
    static  $instance ;
    public static function get_instance()
    {
        if ( !isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}