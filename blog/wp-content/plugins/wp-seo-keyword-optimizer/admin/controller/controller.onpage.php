<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Controller_Onpage extends WSKO_Controller
{
    //Options
    public  $icon = "code" ;
    public  $link = "onpage" ;
    public  $styles = array( 'onpage' ) ;
    public  $template_folder = "onpage" ;
    public  $ajax_actions = array( 'table_onpage', 'refresh_analysis', 'onpage_multi_co' ) ;
    public  $subpages = array(
        'overview'     => array(
        'template' => 'onpage/page-overview.php',
    ),
        'titles'       => array(
        'template' => 'onpage/page-titles.php',
    ),
        'descriptions' => array(
        'template' => 'onpage/page-descriptions.php',
    ),
        'links'        => array(
        'template' => 'onpage/page-links.php',
    ),
        'headings'     => array(
        'template' => 'onpage/page-headings.php',
    ),
        'content'      => array(
        'template' => 'onpage/page-content.php',
    ),
        'keywords'     => array(
        'template' => 'onpage/page-keywords.php',
    ),
        'index'        => array(
        'template'   => 'onpage/page-index.php',
        'is_premium' => true,
    ),
        'canon'        => array(
        'template'   => 'onpage/page-canon.php',
        'is_premium' => true,
    ),
        'social'       => array(
        'template' => 'onpage/page-social.php',
    ),
    ) ;
    public function get_title()
    {
        return __( 'Onpage', 'wsko' );
    }
    
    public function get_subpage_title( $subpage )
    {
        switch ( $subpage ) {
            case 'overview':
                return __( 'Overview', 'wsko' );
            case 'titles':
                return __( 'Meta Titles', 'wsko' );
            case 'descriptions':
                return __( 'Meta Descriptions', 'wsko' );
            case 'links':
                return __( 'Permalinks', 'wsko' );
            case 'headings':
                return __( 'Headings', 'wsko' );
            case 'content':
                return __( 'Content Length', 'wsko' );
            case 'keywords':
                return __( 'SEO Keywords', 'wsko' );
            case 'index':
                return __( 'Indexability', 'wsko' );
            case 'canon':
                return __( 'Canonicals', 'wsko' );
            case 'social':
                return __( 'Social Snippets', 'wsko' );
        }
        return '';
    }
    
    public function get_knowledge_base_tags( $subpage )
    {
        $res = array( 'onpage' );
        switch ( $subpage ) {
            case 'overview':
                $res[] = "onpage_overview";
                break;
            case 'titles':
                $res[] = "onpage_meta_titles";
                break;
            case 'descriptions':
                $res[] = "onpage_meta_desc";
                break;
            case 'links':
                $res[] = "onpage_permalinks";
                break;
            case 'headings':
                $res[] = "onpage_headings";
                break;
            case 'content':
                $res[] = "onpage_content_length";
                break;
            case 'keywords':
                $res[] = "onpage_prio_keywords";
                break;
            case 'index':
                $res[] = "onpage_indexability";
                break;
            case 'canon':
                $res[] = "onpage_canonicals";
                break;
            case 'social':
                $res[] = "onpage_social_snippets";
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
        $dynamic_elements = false;
        $page = $this->get_current_subpage();
        
        if ( $page ) {
            $accessible = $this->is_accessible( $page );
            switch ( $page ) {
                case 'overview':
                    
                    if ( $accessible ) {
                        $chart_data = $this->get_cached_var( 'chart_data' );
                        $onpage_score = '-';
                        $onpage_issues = WSKO_Class_Helper::format_number( $chart_data['total_issues'] );
                        if ( $chart_data['total_issues_last'] !== false ) {
                            $onpage_issues .= WSKO_Class_Template::render_progress_icon(
                                $chart_data['total_issues_last'],
                                -WSKO_Class_Helper::get_ref_value( $chart_data['total_issues'], $chart_data['total_issues_last'] ),
                                array(
                                'flip' => true,
                            ),
                                true
                            );
                        }
                        $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
                        
                        if ( isset( $global_analysis['current_report']['onpage_score_avg'] ) ) {
                            $onpage_score = WSKO_Class_Helper::format_number( $global_analysis['current_report']['onpage_score_avg'] );
                            if ( isset( $global_analysis['last_onpage_score'] ) ) {
                                $onpage_score .= WSKO_Class_Template::render_progress_icon(
                                    $global_analysis['last_onpage_score'],
                                    WSKO_Class_Helper::get_ref_value( $global_analysis['current_report']['onpage_score_avg'], $global_analysis['last_onpage_score'] ),
                                    array(
                                    'decimals' => 2,
                                ),
                                    true
                                );
                            }
                        }
                        
                        $data['table_worst_pages'] = WSKO_Class_Template::render_table(
                            array( __( 'CS', 'wsko' ) . WSKO_Class_Template::render_infoTooltip( __( 'Content Score', 'wsko' ), 'info', true ), 'URL', '' ),
                            $chart_data['worst_optimized'],
                            array(
                            'no_pages' => true,
                            'order'    => array(
                            'col' => 0,
                            'dir' => 'asc',
                        ),
                        ),
                            true
                        );
                        $dynamic_elements = array(
                            '#wsko_db_onpage_score'  => $onpage_score,
                            '#wsko_db_onpage_issues' => $onpage_issues,
                        );
                    } else {
                        $dynamic_elements = array(
                            '#wsko_db_onpage_score'  => '-',
                            '#wsko_db_onpage_issues' => '-',
                        );
                    }
                    
                    break;
            }
            return array(
                'success'          => true,
                'data'             => $data,
                'notif'            => $notif,
                'dynamic_elements' => $dynamic_elements,
            );
        }
    
    }
    
    public function action_table_onpage()
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
            case 'titles':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'title_length',
                    array( 'title_duplicates', 'post_id' ),
                    'title',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $args = array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                );
                $is_prem = false;
                if ( !$is_prem ) {
                    $data = WSKO_Class_Onpage::get_onpage_crawl_data( $args, $stats );
                }
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(
                        'with_co' => false,
                    ), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        WSKO_ONPAGE_TITLE_MIN,
                        WSKO_ONPAGE_TITLE_MAX,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg[0] ? WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
                        'onpage_multi_co',
                        'title_dupl|' . $arg[1],
                        WSKO_Class_Helper::format_number( $arg[0] ) . ' <i class="fa fa-eye fa-fw text-off"></i>',
                        array(
                        'msg' => __( 'Title Duplicates', 'wsko' ),
                    ),
                        true
                    ) : '-' );
                },
                    '4' => function ( $arg ) {
                    return '<span class="wsko-text-off">' . substr( $arg, 0, 100 ) . (( strlen( $arg ) > 100 ? '...' : '' )) . '</span>';
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'titles_dupl':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'title_length',
                    array( 'title_duplicates', 'post_id' ),
                    'title',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $args = array(
                    'having'  => $custom_filter,
                    'where'   => array(
                    'title_duplicates' => '>0',
                ),
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                );
                $is_prem = false;
                if ( !$is_prem ) {
                    $data = WSKO_Class_Onpage::get_onpage_crawl_data( $args, $stats );
                }
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        WSKO_ONPAGE_TITLE_MIN,
                        WSKO_ONPAGE_TITLE_MAX,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg[0] ? WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
                        'onpage_multi_co',
                        'title_dupl|' . $arg[1],
                        WSKO_Class_Helper::format_number( $arg[0] ) . ' <i class="fa fa-eye fa-fw text-off"></i>',
                        array(
                        'msg' => __( 'Title Duplicates', 'wsko' ),
                    ),
                        true
                    ) : '-' );
                },
                    '4' => function ( $arg ) {
                    return '<span class="wsko-text-off">' . substr( $arg, 0, 100 ) . (( strlen( $arg ) > 100 ? '...' : '' )) . '</span>';
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'desc':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'desc_length',
                    array( 'desc_duplicates', 'post_id' ),
                    'desc_s',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $args = array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                );
                $is_prem = false;
                if ( !$is_prem ) {
                    $data = WSKO_Class_Onpage::get_onpage_crawl_data( $args, $stats );
                }
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        WSKO_ONPAGE_DESC_MIN,
                        WSKO_ONPAGE_DESC_MAX,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg[0] ? WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
                        'onpage_multi_co',
                        'desc_dupl|' . $arg[1],
                        WSKO_Class_Helper::format_number( $arg[0] ) . ' <i class="fa fa-eye fa-fw text-off"></i>',
                        array(
                        'msg' => __( 'Description Duplicates', 'wsko' ),
                    ),
                        true
                    ) : '-' );
                },
                    '4' => function ( $arg ) {
                    return '<span class="wsko-text-off">' . substr( $arg, 0, 200 ) . (( strlen( $arg ) > 200 ? '...' : '' )) . '</span>';
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'desc_dupl':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'desc_length',
                    array( 'desc_duplicates', 'post_id' ),
                    'desc_s',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $args = array(
                    'having'  => $custom_filter,
                    'where'   => array(
                    'desc_duplicates' => '>0',
                ),
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                );
                $is_prem = false;
                if ( !$is_prem ) {
                    $data = WSKO_Class_Onpage::get_onpage_crawl_data( $args, $stats );
                }
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        WSKO_ONPAGE_DESC_MIN,
                        WSKO_ONPAGE_DESC_MAX,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg[0] ? WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
                        'onpage_multi_co',
                        'desc_dupl|' . $arg[1],
                        WSKO_Class_Helper::format_number( $arg[0] ) . ' <i class="fa fa-eye fa-fw text-off"></i>',
                        array(
                        'msg' => __( 'Description Duplicates', 'wsko' ),
                    ),
                        true
                    ) : '-' );
                },
                    '4' => function ( $arg ) {
                    return '<span class="wsko-text-off">' . substr( $arg, 0, 200 ) . (( strlen( $arg ) > 200 ? '...' : '' )) . '</span>';
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'headings':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'count_h1',
                    'count_h2',
                    'count_h3',
                    'count_h4',
                    'count_h5',
                    'count_h6',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ), $stats );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( 1, 1, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( 0, 99999, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '4' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        0,
                        99999,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '5' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        0,
                        99999,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '6' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        0,
                        99999,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '7' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        0,
                        99999,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '8' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'content',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'content':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    /*'content_length', */
                    'word_count',
                    'post_id',
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ), $stats );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field_s( $arg[0], array(), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step(
                        250,
                        9999999,
                        $arg,
                        true
                    ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'content',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'keywords':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'prio1_kw_den',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ), $stats );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field( $arg[1], array(), true );
                },
                    '2' => function ( $arg ) {
                    $res = "";
                    foreach ( $arg as $k => $a ) {
                        $res .= '<p class="wsko-priokw-item">' . $k . ' - <a href="#" data-toggle="tooltip" data-title="' . __( 'Keyword Density', 'wsko' ) . '" style="color:' . WSKO_Class_Template::get_color_step_5(
                            0.5,
                            1.5,
                            2.5,
                            3.5,
                            $a
                        ) . '">' . WSKO_Class_Helper::format_number( $a, 2 ) . ' %</a></p>';
                    }
                    return $res;
                },
                    '3' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'keywords',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'keywords_uniq':
                $data_p = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'where' => array(
                    'prio1_kw_den' => '<>""',
                ),
                ) );
                foreach ( $data_p as $p ) {
                    foreach ( $p->prio1_kw_den as $kw => $den ) {
                        $data[] = array(
                            'keyword' => $kw,
                            'page'    => $p->url,
                            'prio'    => 1,
                            'density' => $den,
                            'post_id' => $p->post_id,
                        );
                    }
                }
                $data_p = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'where' => array(
                    'prio2_kw_den' => '<>""',
                ),
                ) );
                foreach ( $p->prio2_kw_den as $kw => $den ) {
                    $data[] = array(
                        'keyword' => $kw,
                        'page'    => $p->url,
                        'prio'    => 2,
                        'density' => $den,
                        'post_id' => $p->post_id,
                    );
                }
                $params = array(
                    'specific_keys' => array(
                    'keyword',
                    array( 'page', 'post_id' ),
                    'prio',
                    'density',
                    'post_id'
                ),
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_keyword_field( $arg, true );
                },
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_url_post_field( $arg[1], array(
                        'open_tab' => 'keywords',
                    ), true );
                },
                    '3' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step_5(
                        0.5,
                        1.5,
                        2.5,
                        3.5,
                        $arg
                    ) . '">' . WSKO_Class_Helper::format_number( $arg, 2 ) . ' %</span>';
                },
                    '4' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'keywords',
                    ), true ) : '' );
                },
                ),
                );
                break;
            case 'keywords_dupl':
                $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
                $data = ( isset( $global_analysis['current_report']['priority_kw_duplicates'] ) ? $global_analysis['current_report']['priority_kw_duplicates'] : array() );
                if ( $data ) {
                    $data = array_filter( $data, function ( $a ) {
                        return count( $a['posts'] ) > 1;
                    } );
                }
                $params = array(
                    'specific_keys' => array( 'keyword', array( 'post_count', 'keyword' ) ),
                    'format'        => array(
                    '0' => function ( $arg ) {
                    return WSKO_Class_Template::render_keyword_field( $arg, true );
                },
                    '1' => function ( $arg ) {
                    return ( $arg[0] ? WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
                        'onpage_multi_co',
                        'keyword_dupl|' . $arg[1],
                        WSKO_Class_Helper::format_number( $arg[0] ) . ' <i class="fa fa-eye fa-fw text-off"></i>',
                        array(
                        'msg' => __( 'Keyword Duplicates', 'wsko' ),
                    ),
                        true
                    ) : '-' );
                },
                ),
                );
                break;
            case 'links':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    array( 'url', 'post_id' ),
                    'url_length',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $args_d = array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                );
                $is_prem = false;
                if ( !$is_prem ) {
                    $data = WSKO_Class_Onpage::get_onpage_crawl_data( $args_d, $stats );
                }
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => function ( $arg ) {
                    return WSKO_Class_Template::render_post_dirty_icon( $arg[1], array(), true ) . WSKO_Class_Template::render_url_post_field( $arg[1], array(
                        'open_tab' => 'technical',
                    ), true );
                },
                    '2' => function ( $arg ) {
                    return '<span style="color:' . (( $arg > WSKO_ONPAGE_URL_MAX ? 'orange' : 'green' )) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'content',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'index':
                break;
            case 'canon':
                break;
            case 'social_fb':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    'url',
                    'og_title_length',
                    'og_desc_length',
                    'og_img_provided',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ), $stats );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => 'url',
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( WSKO_ONPAGE_FB_TITLE_MIN, WSKO_ONPAGE_FB_TITLE_MAX, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( WSKO_ONPAGE_FB_DESC_MIN, WSKO_ONPAGE_FB_DESC_MAX, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '4' => function ( $arg ) {
                    return ( $arg == 1 ? '<span style="color:green">' . __( 'set', 'wsko' ) . '</span>' : '<span style="color:orange">' . __( 'not set', 'wsko' ) . '</span>' );
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
                break;
            case 'social_tw':
                $orderby = "";
                $keys = array(
                    'onpage_score',
                    'url',
                    'tw_title_length',
                    'tw_desc_length',
                    'tw_img_provided',
                    'post_id'
                );
                if ( isset( $keys[$order] ) ) {
                    $orderby = (( is_array( $keys[$order] ) ? $keys[$order][0] : $keys[$order] )) . ' ' . (( $orderdir ? 'ASC' : 'DESC' ));
                }
                $custom_filter = WSKO_Class_Helper::sanitize_table_filter( $custom_filter, true );
                
                if ( $search ) {
                    $custom_filter['url,post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    if ( WSKO_Class_Core::is_demo() ) {
                        $custom_filter['post_title,title'] = " LIKE '%" . esc_sql( $search ) . "%'";
                    }
                }
                
                $stats = true;
                $data = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                    'having'  => $custom_filter,
                    'limit'   => $count,
                    'offset'  => $offset,
                    'orderby' => $orderby,
                ), $stats );
                $params = array(
                    'specific_keys' => $keys,
                    'format'        => array(
                    '0' => 'prog_rad',
                    '1' => 'url',
                    '2' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( WSKO_ONPAGE_TW_TITLE_MIN, WSKO_ONPAGE_TW_TITLE_MAX, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '3' => function ( $arg ) {
                    return '<span style="color:' . WSKO_Class_Template::get_color_step( WSKO_ONPAGE_TW_DESC_MIN, WSKO_ONPAGE_TW_DESC_MAX, $arg ) . '">' . WSKO_Class_Helper::format_number( $arg ) . '</span>';
                },
                    '4' => function ( $arg ) {
                    return ( $arg == 1 ? '<span style="color:green">' . __( 'set', 'wsko' ) . '</span>' : '<span style="color:orange">' . __( 'not set', 'wsko' ) . '</span>' );
                },
                    '5' => function ( $arg ) {
                    return ( $arg ? WSKO_Class_Template::render_content_optimizer_link( $arg, array(
                        'open_tab' => 'metas',
                    ), true ) : '' );
                },
                ),
                    'from_cache'    => true,
                );
                
                if ( $stats !== true ) {
                    $filtered_data = $stats['filtered'];
                    $total_data = $stats['total'];
                }
                
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
    
    public function action_onpage_multi_co()
    {
        if ( !$this->can_execute_action( false ) ) {
            return false;
        }
        $arg = ( isset( $_POST['arg'] ) ? sanitize_text_field( $_POST['arg'] ) : false );
        
        if ( $arg ) {
            $args = WSKO_Class_Helper::safe_explode( '|', $arg );
            if ( $args && count( $args ) >= 2 ) {
                switch ( $args[0] ) {
                    case 'issues':
                        $issues = array();
                        
                        if ( $args[1] == 'indexability' ) {
                            $analysis = WSKO_Class_Premium::get_onpage_analysis();
                            if ( $analysis ) {
                                $issues = $analysis['current_report']['issues']['indexability'][$args[2]]['posts'];
                            }
                        } else {
                            $analysis = WSKO_Class_Onpage::get_onpage_analysis();
                            if ( $analysis ) {
                                
                                if ( $args[1] == 'heading_count' ) {
                                    $issues = ( !$analysis['issues']['heading_h2_250'][0]['posts'] ? $analysis['issues']['heading_h2_500'][0]['posts'] : (( !$analysis['issues']['heading_h2_500'][0]['posts'] ? $analysis['issues']['heading_h2_250'][0]['posts'] : array_merge( $analysis['issues']['heading_h2_250'][0]['posts'], $analysis['issues']['heading_h2_500'][0]['posts'] ) )) );
                                } else {
                                    if ( isset( $analysis['current_report']['issues'][$args[1]][$args[2]]['posts'] ) ) {
                                        $issues = $analysis['current_report']['issues'][$args[1]][$args[2]]['posts'];
                                    }
                                }
                            
                            }
                        }
                        
                        $table_dupl = array();
                        $title = $args[1];
                        
                        if ( $issues ) {
                            foreach ( $issues as $dupl ) {
                                $table_dupl[] = array( array(
                                    'value' => WSKO_Class_Template::render_url_post_field( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ), array(
                                    'value' => WSKO_Class_Template::render_content_optimizer_link( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ) );
                            }
                            $view = WSKO_Class_Template::render_table(
                                array( __( 'Page', 'wsko' ), array(
                                'name'  => __( 'CO', 'wsko' ),
                                'width' => '10%',
                            ) ),
                                $table_dupl,
                                array(),
                                true
                            );
                            return array(
                                'success' => true,
                                'title'   => sprintf( __( 'Issues for "%s"', 'wsko' ), $title ),
                                'view'    => $view,
                            );
                        }
                        
                        break;
                    case 'title_dupl':
                        $table_dupl = array();
                        $cache_row = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                            'for_post' => $args[1],
                        ) );
                        $title = "";
                        
                        if ( is_array( $cache_row ) && isset( $cache_row[0] ) ) {
                            $title = $cache_row[0]->title;
                            $duplicates = $cache_row[0]->title_duplicate_posts;
                            foreach ( $duplicates as $dupl ) {
                                $table_dupl[] = array( array(
                                    'value' => WSKO_Class_Template::render_url_post_field( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ), array(
                                    'value' => WSKO_Class_Template::render_content_optimizer_link( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ) );
                            }
                            $view = WSKO_Class_Template::render_table(
                                array( __( 'Page', 'wsko' ), array(
                                'name'  => 'CO',
                                'width' => '10%',
                            ) ),
                                $table_dupl,
                                array(),
                                true
                            );
                            return array(
                                'success' => true,
                                'title'   => sprintf( __( 'Duplicates of "%s"', 'wsko' ), $title ),
                                'view'    => $view,
                            );
                        }
                        
                        break;
                    case 'desc_dupl':
                        $table_dupl = array();
                        $cache_row = WSKO_Class_Onpage::get_onpage_crawl_data( array(
                            'for_post' => $args[1],
                        ) );
                        $title = "";
                        
                        if ( is_array( $cache_row ) && isset( $cache_row[0] ) ) {
                            $title = $cache_row[0]->title;
                            $duplicates = $cache_row[0]->desc_duplicate_posts;
                            foreach ( $duplicates as $dupl ) {
                                $table_dupl[] = array( array(
                                    'value' => WSKO_Class_Template::render_url_post_field( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ), array(
                                    'value' => WSKO_Class_Template::render_content_optimizer_link( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'metas',
                                ), true ),
                                ) );
                            }
                            $view = WSKO_Class_Template::render_table(
                                array( __( 'Page', 'wsko' ), array(
                                'name'  => __( 'CO', 'wsko' ),
                                'width' => '10%',
                            ) ),
                                $table_dupl,
                                array(),
                                true
                            );
                            return array(
                                'success' => true,
                                'title'   => sprintf( __( 'Duplicates of "%s"', 'wsko' ), $title ),
                                'view'    => $view,
                            );
                        }
                        
                        break;
                    case 'keyword_dupl':
                        $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
                        $data = ( isset( $global_analysis['current_report']['priority_kw_duplicates'] ) ? $global_analysis['current_report']['priority_kw_duplicates'] : array() );
                        $table_dupl = array();
                        $title = "";
                        
                        if ( $data && is_array( $data[$args[1]] ) ) {
                            $title = $data[$args[1]]['keyword'];
                            $duplicates = $data[$args[1]]['posts'];
                            foreach ( $duplicates as $dupl ) {
                                $table_dupl[] = array( array(
                                    'value' => WSKO_Class_Template::render_url_post_field( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'keywords',
                                ), true ),
                                ), array(
                                    'value' => WSKO_Class_Template::render_content_optimizer_link( $dupl, array(
                                    'multi_link' => true,
                                    'open_tab'   => 'keywords',
                                ), true ),
                                ) );
                            }
                            $view = WSKO_Class_Template::render_table(
                                array( __( 'Page', 'wsko' ), array(
                                'name'  => __( 'CO', 'wsko' ),
                                'width' => '10%',
                            ) ),
                                $table_dupl,
                                array(),
                                true
                            );
                            return array(
                                'success' => true,
                                'title'   => sprintf( __( 'Duplicates of "%s"', 'wsko' ), $title ),
                                'view'    => $view,
                            );
                        }
                        
                        break;
                }
            }
        }
    
    }
    
    public function action_refresh_analysis()
    {
        if ( !$this->can_execute_action() ) {
            return false;
        }
        if ( !WSKO_Class_Core::get_setting( 'onpage_include_post_types' ) ) {
            return array(
                'success' => false,
                'msg'     => __( 'Please select at least one post type', 'wsko' ),
            );
        }
        
        if ( WSKO_Class_Core::is_configured() ) {
            WSKO_Class_Crons::bind_onpage_analysis();
        } else {
            WSKO_Class_Core::save_option( 'start_with_onpage_crawl', true );
        }
        
        return true;
    }
    
    public function get_cached_chart_data( $args = false )
    {
        $global_analysis = WSKO_Class_Onpage::get_onpage_analysis();
        $analysis = ( isset( $global_analysis['current_report'] ) ? $global_analysis['current_report'] : false );
        $last_issues = ( isset( $global_analysis['last_issues'] ) ? $global_analysis['last_issues'] : false );
        $total_issues = 0;
        $total_issues_last = 0;
        
        if ( $analysis ) {
            $total_issues += $analysis['issues']['keyword_density'][2]['sum'] + $analysis['issues']['keyword_density'][3]['sum'] + $analysis['issues']['keyword_density'][0]['sum'];
            $total_issues += $analysis['issues']['title_length'][2]['sum'] + $analysis['issues']['title_length'][3]['sum'] + $analysis['issues']['title_length'][0]['sum'] + $analysis['issues']['title_prio1'][0]['sum'];
            $total_issues += $analysis['issues']['desc_length'][2]['sum'] + $analysis['issues']['desc_length'][3]['sum'] + $analysis['issues']['desc_length'][0]['sum'] + $analysis['issues']['desc_prio1'][0]['sum'];
            $total_issues += $analysis['issues']['word_count'][2]['sum'] + $analysis['issues']['word_count'][3]['sum'] + $analysis['issues']['word_count'][4]['sum'] + $analysis['issues']['word_count'][0]['sum'];
            $total_issues += $analysis['issues']['heading_h1_count'][0]['sum'] + $analysis['issues']['heading_h1_count'][2]['sum'] + $analysis['issues']['heading_h1_prio1'][0]['sum'] + $analysis['issues']['heading_h1_prio1_count'][0]['sum'] + $analysis['issues']['heading_h2_250'][0]['sum'] + $analysis['issues']['heading_h2_500'][0]['sum'] + $analysis['issues']['heading_h2h3_count'][0]['sum'];
            $total_issues += $analysis['issues']['url_length'][0]['sum'] + $analysis['issues']['url_prio1'][0]['sum'];
            $total_issues += $analysis['issues']['media'][0]['sum'] + $analysis['issues']['media_missing_alt'][0]['sum'];
        }
        
        
        if ( $last_issues ) {
            $total_issues_last += $last_issues['keyword_density'][2] + $last_issues['keyword_density'][3] + $last_issues['keyword_density'][0];
            $total_issues_last += $last_issues['title_length'][2] + $last_issues['title_length'][3] + $last_issues['title_length'][0] + $last_issues['title_prio1'][0];
            $total_issues_last += $last_issues['desc_length'][2] + $last_issues['desc_length'][3] + $last_issues['desc_length'][0] + $last_issues['desc_prio1'][0];
            $total_issues_last += $last_issues['word_count'][2] + $last_issues['word_count'][3] + $last_issues['word_count'][4] + $last_issues['word_count'][0];
            $total_issues_last += $last_issues['heading_h1_count'][0] + $last_issues['heading_h1_count'][2] + $last_issues['heading_h1_prio1'][0] + $last_issues['heading_h1_prio1_count'][0] + $last_issues['heading_h2_250'][0] + $last_issues['heading_h2_500'][0] + $last_issues['heading_h2h3_count'][0];
            $total_issues_last += $last_issues['url_length'][0] + $last_issues['url_prio1'][0];
            $total_issues_last += $last_issues['media'][0] + $last_issues['media_missing_alt'][0];
        }
        
        $res = array(
            'worst_optimized'   => array(),
            'total_issues'      => 0,
            'total_issues_last' => false,
        );
        $res['total_issues'] = $total_issues;
        if ( $last_issues || $last_issues_prem ) {
            $res['total_issues_last'] = $total_issues_last;
        }
        $worst = WSKO_Class_Onpage::get_onpage_crawl_data( array(
            'orderby' => 'onpage_score',
            'limit'   => 5,
        ) );
        foreach ( $worst as $w ) {
            $res['worst_optimized'][] = array( array(
                'order' => $w->onpage_score,
                'value' => WSKO_Class_Template::render_radial_progress(
                'success',
                false,
                array(
                'val' => $w->onpage_score,
            ),
                true
            ),
            ), array(
                'order' => $w->url,
                'value' => WSKO_Class_Template::render_post_dirty_icon( $w->post_id, array(), true ) . WSKO_Class_Template::render_url_post_field( $w->post_id, array(), true ),
            ), array(
                'order' => $w->post_id,
                'value' => WSKO_Class_Template::render_content_optimizer_link( $w->post_id, array(), true ),
            ) );
        }
        return $res;
    }
    
    public function _is_accessible( $subpage = false )
    {
        return WSKO_Class_Onpage::has_current_report() && !WSKO_Class_Core::get_option( 'onpage_analysis_running' );
    }
    
    //Singleton
    static  $instance ;
}
WSKO_Controller_Onpage::init_controller();