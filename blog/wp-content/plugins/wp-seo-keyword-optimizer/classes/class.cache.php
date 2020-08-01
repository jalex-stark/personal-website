<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class WSKO_Class_Cache
{
    private  $options_table = 'wsko_options' ;
    private  $cache_rows_table = 'wsko_cache_days' ;
    private  $fieldsets = array(
        'search'       => array(
        'table'  => 'wsko_cache_data_se',
        'prefix' => 'se',
    ),
        'search_delta' => array(
        'table'  => 'wsko_cache_data_se_d',
        'prefix' => 'se_d',
    ),
        'onpage'       => array(
        'table'  => 'wsko_cache_data_onpage',
        'prefix' => 'op',
    ),
    ) ;
    static  $temp_stats ;
    static  $session_cache = array() ;
    static  $large_cache = array() ;
    static  $option_cache = array() ;
    static  $option_cache_t = array() ;
    static  $cache_prepared = false ;
    function __construct()
    {
    }
    
    public static function get_table_prefix( $table )
    {
        $int = new static();
        if ( isset( $int->fieldsets[$table] ) ) {
            return $int->fieldsets[$table]['prefix'];
        }
        return false;
    }
    
    public static function get_table( $table )
    {
        $int = new static();
        if ( isset( $int->fieldsets[$table] ) ) {
            return $int->fieldsets[$table]['table'];
        }
        return false;
    }
    
    public static function check_database()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        global  $wpdb ;
        $int = new static();
        $charset_collate = "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        //$wpdb->get_charset_collate();
        /*if ($wpdb->get_var('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . $wpdb->dbname .'" AND table_name="' . $wpdb->prefix.$int->options_table . '"') == "0")
        		{*/
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . $int->options_table . " (\r\n\t\t\t  id bigint(20) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  option_name text NOT NULL,\r\n\t\t\t  option_value text,\r\n\t\t\t  PRIMARY KEY (id)\r\n\t\t\t) {$charset_collate};";
        dbDelta( $sql );
        /*}
        		if ($wpdb->get_var('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . $wpdb->dbname .'" AND table_name="' . $wpdb->prefix.$int->cache_rows_table . '"') == "0")
        		{*/
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . $int->cache_rows_table . " (\r\n\t\t\t  id bigint(20) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\r\n\t\t\t  PRIMARY KEY (id)\r\n\t\t\t) {$charset_collate};";
        dbDelta( $sql );
        /*}
        		if ($wpdb->get_var('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . $wpdb->dbname .'" AND table_name="' . $wpdb->prefix.$int->fieldsets['search']['table'] . '"') == "0")
        		{*/
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . $int->fieldsets['search']['table'] . " (\r\n\t\t\t  id bigint(20) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  cache_id bigint(20) NOT NULL,\r\n\t\t\t  keyval text NOT NULL,\r\n\t\t\t  clicks int NOT NULL,\r\n\t\t\t  position double NOT NULL,\r\n\t\t\t  impressions int NOT NULL,\r\n\t\t\t  type tinyint NOT NULL,\r\n\t\t\t  PRIMARY KEY (id)\r\n\t\t\t) {$charset_collate};";
        dbDelta( $sql );
        /*}
        		if ($wpdb->get_var('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . $wpdb->dbname .'" AND table_name="' . $wpdb->prefix.$int->fieldsets['search_delta']['table'] . '"') == "0")
        		{*/
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . $int->fieldsets['search_delta']['table'] . " (\r\n\t\t\t  keyval varchar(190) NOT NULL,\r\n\t\t\t  cache_id bigint(20) NOT NULL,\r\n\t\t\t  type tinyint NOT NULL,\r\n\t\t\t  new tinyint NOT NULL,\r\n\t\t\t  lost tinyint NOT NULL,\r\n\t\t\t  PRIMARY KEY (keyval)\r\n\t\t\t) {$charset_collate};";
        dbDelta( $sql );
        /*}
        		if ($wpdb->get_var('SELECT COUNT(1) FROM information_schema.tables WHERE table_schema="' . $wpdb->dbname .'" AND table_name="' . $wpdb->prefix.$int->fieldsets['onpage']['table'] . '"') == "0")
        		{*/
        $sql = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . $int->fieldsets['onpage']['table'] . " (\r\n\t\t\t  id bigint(20) NOT NULL AUTO_INCREMENT,\r\n\t\t\t  url text NOT NULL,\r\n\t\t\t  cache_id bigint(20) NOT NULL,\r\n\t\t\t  onpage_score float NOT NULL,\r\n\t\t\t  post_id bigint(20) NOT NULL,\r\n\t\t\t  post_type text NOT NULL,\r\n\t\t\t  title text NOT NULL,\r\n\t\t\t  title_custom tinyint,\r\n\t\t\t  title_length int NOT NULL,\r\n\t\t\t  title_duplicates int NOT NULL,\r\n\t\t\t  title_duplicate_posts text NOT NULL,\r\n\t\t\t  desc_s text NOT NULL,\r\n\t\t\t  desc_length int NOT NULL,\r\n\t\t\t  desc_duplicates int NOT NULL,\r\n\t\t\t  desc_duplicate_posts text NOT NULL,\r\n\t\t\t  og_title_length int NOT NULL,\r\n\t\t\t  og_desc_length int NOT NULL,\r\n\t\t\t  og_img_provided int NOT NULL,\r\n\t\t\t  tw_title_length int NOT NULL,\r\n\t\t\t  tw_desc_length int NOT NULL,\r\n\t\t\t  tw_img_provided int NOT NULL,\r\n\t\t\t  count_h1 int NOT NULL,\r\n\t\t\t  count_h2 int NOT NULL,\r\n\t\t\t  count_h3 int NOT NULL,\r\n\t\t\t  count_h4 int NOT NULL,\r\n\t\t\t  count_h5 int NOT NULL,\r\n\t\t\t  count_h6 int NOT NULL,\r\n\t\t\t  word_count int NOT NULL,\r\n\t\t\t  content_length int NOT NULL,\r\n\t\t\t  url_length int NOT NULL,\r\n\t\t\t  prio1_kw_den text NOT NULL,\r\n\t\t\t  prio2_kw_den text NOT NULL,\r\n\t\t\t  PRIMARY KEY (id)\r\n\t\t\t) {$charset_collate};";
        dbDelta( $sql );
    }
    
    public static function update_database( $version_pre )
    {
        $int = new static();
        global  $wpdb ;
        if ( !$version_pre ) {
            $version_pre = '0.0.1';
        }
        
        if ( version_compare( $version_pre, '2.1', '<' ) ) {
            $charset_collate = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            //$wpdb->get_charset_collate();
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['onpage']['table'] . " CONVERT TO " . $charset_collate );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['search']['table'] . " CONVERT TO " . $charset_collate );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['search_delta']['table'] . " CONVERT TO " . $charset_collate );
        }
        
        
        if ( version_compare( $version_pre, '2.1.9.9', '<' ) ) {
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->options_table . " MODIFY id bigint(20) AUTO_INCREMENT" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->cache_rows_table . " MODIFY id bigint(20) AUTO_INCREMENT" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['search']['table'] . " MODIFY id bigint(20) AUTO_INCREMENT" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['search']['table'] . " MODIFY cache_id bigint(20)" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['search_delta']['table'] . " MODIFY cache_id bigint(20)" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['onpage']['table'] . " MODIFY id bigint(20) AUTO_INCREMENT" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['onpage']['table'] . " MODIFY cache_id bigint(20)" );
            $wpdb->query( "ALTER TABLE " . $wpdb->prefix . $int->fieldsets['onpage']['table'] . " MODIFY post_id bigint(20)" );
        }
    
    }
    
    public static function get_wsko_option(
        $key,
        $check = false,
        $refetch = false,
        $save_to_cache = true
    )
    {
        if ( isset( static::$option_cache_t[$key] ) && static::$option_cache_t[$key]['t'] < time() ) {
            $refetch = true;
        }
        $data = false;
        
        if ( !isset( static::$option_cache_t[$key] ) || $refetch ) {
            $int = new static();
            global  $wpdb ;
            $row = $wpdb->get_row( 'SELECT option_value FROM ' . $wpdb->prefix . $int->options_table . ' WHERE option_name="' . esc_sql( $key ) . '" LIMIT 1' );
            if ( $row && is_object( $row ) ) {
                $data = unserialize( $row->option_value );
            }
            if ( $check && is_callable( $check ) ) {
                $data = $check( $data );
            }
            if ( $save_to_cache ) {
                static::$option_cache_t[$key] = array(
                    'data' => $data,
                    't'    => time() + 10,
                );
            }
        }
        
        if ( isset( static::$option_cache_t[$key] ) ) {
            $data = static::$option_cache_t[$key]['data'];
        }
        return $data;
    }
    
    public static function save_wsko_option( $key, $data, $save_to_cache = true )
    {
        $int = new static();
        global  $wpdb ;
        $row = $wpdb->get_row( 'SELECT option_value FROM ' . $wpdb->prefix . $int->options_table . ' WHERE option_name="' . esc_sql( $key ) . '" LIMIT 1' );
        
        if ( $row && is_object( $row ) ) {
            $wpdb->update( $wpdb->prefix . $int->options_table, array(
                'option_value' => serialize( $data ),
            ), array(
                'option_name' => esc_sql( $key ),
            ) );
        } else {
            $wpdb->insert( $wpdb->prefix . $int->options_table, array(
                'option_name'  => esc_sql( $key ),
                'option_value' => serialize( $data ),
            ) );
        }
        
        if ( $save_to_cache || isset( static::$option_cache_t[$key] ) ) {
            static::$option_cache_t[$key] = array(
                'data' => $data,
                't'    => time() + 10,
            );
        }
    }
    
    public static function remove_wsko_option( $key )
    {
        $int = new static();
        global  $wpdb ;
        $wpdb->delete( $wpdb->prefix . $int->options_table, array(
            'option_name' => esc_sql( $key ),
        ) );
        if ( isset( static::$option_cache_t[$key] ) ) {
            unset( static::$option_cache_t[$key] );
        }
    }
    
    public static function get_wp_option(
        $key,
        $check = false,
        $refetch = false,
        $save_to_cache = true
    )
    {
        if ( isset( static::$option_cache[$key] ) && static::$option_cache[$key]['t'] < time() ) {
            $refetch = true;
        }
        $data = false;
        
        if ( !isset( static::$option_cache[$key] ) || $refetch ) {
            
            if ( $refetch ) {
                global  $wpdb ;
                $row = $wpdb->get_row( 'SELECT option_value FROM ' . $wpdb->options . ' WHERE option_name="' . esc_sql( $key ) . '" LIMIT 1' );
                if ( $row && is_object( $row ) ) {
                    $data = unserialize( $row->option_value );
                }
            } else {
                $data = get_option( $key );
            }
            
            if ( $check && is_callable( $check ) ) {
                $data = $check( $data );
            }
            if ( $save_to_cache ) {
                static::$option_cache[$key] = array(
                    'data' => $data,
                    't'    => time() + 10,
                );
            }
        }
        
        if ( isset( static::$option_cache[$key] ) ) {
            $data = static::$option_cache[$key]['data'];
        }
        return $data;
    }
    
    public static function save_wp_option( $key, $data, $save_to_cache = true )
    {
        update_option( $key, $data );
        if ( $save_to_cache || isset( static::$option_cache[$key] ) ) {
            static::$option_cache[$key] = array(
                'data' => $data,
                't'    => time() + 10,
            );
        }
    }
    
    public static function remove_wp_option( $key )
    {
        delete_option( $key );
        if ( isset( static::$option_cache[$key] ) ) {
            unset( static::$option_cache[$key] );
        }
    }
    
    public static function store_large_object( $key, $data )
    {
        $key_store = WSKO_Class_Cache::get_cache_path( 'large', $key );
        
        if ( $key_store ) {
            $res = file_put_contents( $key_store, json_encode( $data ) );
            if ( $res !== false ) {
                static::$large_cache[$key] = $data;
            }
        }
    
    }
    
    public static function get_large_object( $key )
    {
        if ( isset( static::$large_cache[$key] ) ) {
            return static::$large_cache[$key];
        }
        $key_store = WSKO_Class_Cache::get_cache_path( 'large', $key );
        
        if ( $key_store && file_exists( $key_store ) ) {
            $cache = file_get_contents( $key_store );
            
            if ( $cache ) {
                $data = WSKO_Class_Helper::safe_json_decode( $cache );
                static::$large_cache[$key] = $data;
                return $data;
            }
        
        }
        
        return false;
    }
    
    public static function get_cache_stats()
    {
        if ( self::$temp_stats ) {
            return self::$temp_stats;
        }
        global  $wpdb ;
        $int = new static();
        $t_stats = array(
            'general'     => array(
            'days' => $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $int->cache_rows_table ),
            'size' => $wpdb->get_var( 'SELECT SUM((data_length + index_length)) AS size
								  FROM information_schema.TABLES
								  WHERE table_schema="' . $wpdb->dbname . '" 
								  AND table_name="' . $wpdb->prefix . $int->cache_rows_table . '"' ),
        ),
            'data_tables' => array(),
        );
        $t_stats['general']['rows'] = $t_stats['general']['days'];
        foreach ( $int->fieldsets as $set => $data ) {
            $t_stats['data_tables'][$set] = array(
                'days' => $wpdb->get_var( 'SELECT COUNT(*) FROM (SELECT rt.id, rt.time, COUNT(dt.cache_id) as dataCount FROM ' . $wpdb->prefix . $int->cache_rows_table . ' rt LEFT JOIN ' . $wpdb->prefix . $int->fieldsets[$set]['table'] . ' dt ON rt.id=dt.cache_id GROUP BY rt.id, rt.time) AS tt WHERE tt.dataCount > 0' ),
                'rows' => $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . $int->fieldsets[$set]['table'] ),
                'size' => $wpdb->get_var( 'SELECT SUM((data_length + index_length)) AS size 
								  FROM information_schema.TABLES 
								  WHERE table_schema="' . $wpdb->dbname . '" 
								  AND table_name="' . $wpdb->prefix . $int->fieldsets[$set]['table'] . '"' ),
            );
            $t_stats['general']['rows'] += $t_stats['data_tables'][$set]['rows'];
            $t_stats['general']['size'] += $t_stats['data_tables'][$set]['size'];
        }
        self::$temp_stats = $t_stats;
        return self::$temp_stats;
    }
    
    public static function get_cache_id( $day, $create = false )
    {
        global  $wpdb ;
        $int = new static();
        if ( !empty($wpdb->charset) && $wpdb->charset == 'utf8mb4' ) {
            $collate = 'COLLATE utf8mb4_bin';
        }
        $sql = "SELECT * FROM (SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table;
        
        if ( $day ) {
            $sql .= " WHERE time BETWEEN '" . date( 'Y-m-d 00:00:00', $day ) . "' AND '" . date( 'Y-m-d 23:59:59', $day ) . "'";
        } else {
            $sql .= " WHERE time = '0000-00-00 00:00:00'";
        }
        
        $sql .= ") AS table_c";
        $res = $wpdb->get_results( $sql );
        
        if ( isset( $res[0] ) ) {
            return $res[0]->id;
        } else {
            
            if ( $create ) {
                $wpdb->insert( $wpdb->prefix . $int->cache_rows_table, array(
                    'time' => ( $day ? date( 'Y-m-d', $day ) : '0000-00-00' ),
                ) );
                return $wpdb->insert_id;
            }
        
        }
        
        return false;
    }
    
    public static function get_cache_time( $id )
    {
        global  $wpdb ;
        $int = new static();
        return $wpdb->get_var( "SELECT time FROM " . $wpdb->prefix . $int->cache_rows_table . " WHERE id=" . $id );
    }
    
    public static function get_cache_rows(
        $start,
        $end,
        $field_sets,
        $options = false,
        $orderby = false,
        $return_sql = false
    )
    {
        $having = ( isset( $options['having'] ) ? $options['having'] : false );
        $where = ( isset( $options['where'] ) ? $options['where'] : false );
        $has_no_ref = ( isset( $options['has_no_ref'] ) ? $options['has_no_ref'] : false );
        $override_join = ( isset( $options['override_join'] ) ? $options['override_join'] : false );
        $from_cache_days = ( isset( $options['from_cache_days'] ) ? $options['from_cache_days'] : false );
        $join_post = ( isset( $options['join_post'] ) ? $options['join_post'] : false );
        global  $wpdb ;
        $int = new static();
        if ( !empty($wpdb->charset) && $wpdb->charset == 'utf8mb4' ) {
            $collate = 'COLLATE utf8mb4_bin';
        }
        $ids = array();
        $sql = "";
        
        if ( $start === 0 && $end === 0 ) {
        } else {
            
            if ( $from_cache_days ) {
                $sql = "SELECT " . (( isset( $options['vals'] ) && $options['vals'] ? $options['vals'] : '*' )) . " FROM (SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table;
            } else {
                $sql = "SELECT id FROM " . $wpdb->prefix . $int->cache_rows_table;
            }
            
            
            if ( $start || $end ) {
                $sql .= " WHERE time BETWEEN '" . date( 'Y-m-d H:i:s', $start ) . "' AND '" . date( 'Y-m-d H:i:s', $end ) . "'";
            } else {
                $sql .= " WHERE time = '0000-00-00 00:00:00'";
            }
            
            
            if ( $from_cache_days ) {
                $sql .= ") AS table_c";
            } else {
                $ids = $wpdb->get_results( $sql );
                $sql = "";
            }
        
        }
        
        
        if ( $start === 0 && $end === 0 || $ids || $from_cache_days ) {
            $first_key = false;
            
            if ( !$from_cache_days ) {
                if ( $ids ) {
                    foreach ( $ids as $k => $id ) {
                        $ids[$k] = $id->id;
                    }
                }
                reset( $field_sets );
                $first_key = key( $field_sets );
                $first_tn = $int->fieldsets[$first_key]['prefix'];
                $sql = "SELECT " . (( isset( $options['vals'] ) && $options['vals'] ? $options['vals'] : '*' )) . " FROM " . $wpdb->prefix . $int->fieldsets[$first_key]['table'] . " AS " . $first_tn;
            }
            
            $first = true;
            foreach ( $field_sets as $set => $filter ) {
                $tn = $int->fieldsets[$set]['prefix'];
                
                if ( $set != $first_key || $from_cache_days ) {
                    $sql .= " " . (( isset( $override_join[$set]['join_left'] ) && $override_join[$set]['join_left'] ? 'LEFT' : 'INNER' )) . " JOIN " . $wpdb->prefix . $int->fieldsets[$set]['table'] . " AS " . $tn;
                    
                    if ( isset( $override_join[$set]['table_r'] ) ) {
                        $sql .= " ON " . $int->fieldsets[$set]['prefix'] . "." . $override_join[$set]['key'] . "=" . $int->fieldsets[$override_join[$set]['table_r']]['prefix'] . "." . $override_join[$set]['key_r'];
                        $first = false;
                    } else {
                        
                        if ( isset( $override_join[$set]['no_time'] ) && $override_join[$set]['no_time'] ) {
                        } else {
                            $sql .= " ON table_c.id=" . $tn . ".cache_id";
                            $first = false;
                        }
                    
                    }
                    
                    if ( !isset( $override_join[$set]['table_r'] ) || isset( $override_join[$set]['full_join'] ) && $override_join[$set]['full_join'] ) {
                        foreach ( $filter as $f => $v ) {
                            if ( isset( $v['join'] ) && $v['join'] ) {
                                $sql .= (( $first ? " ON " : " AND " )) . $tn . "." . $f . $v['eval'];
                            }
                        }
                    }
                }
            
            }
            if ( $join_post ) {
                $sql .= " LEFT JOIN " . $wpdb->posts . " wp_post ON wp_post.ID = " . $int->fieldsets[$first_key]['prefix'] . ".post_id";
            }
            $first = true;
            foreach ( $field_sets as $set => $filter ) {
                $tn = $int->fieldsets[$set]['prefix'];
                foreach ( $filter as $f => $v ) {
                    
                    if ( !isset( $v['join'] ) || !$v['join'] ) {
                        $sql .= (( $first ? ' WHERE ' : ' AND ' )) . $tn . "." . $f . $v['eval'];
                        $first = false;
                    }
                
                }
                //$sql = $sql .= ($f?' WHERE ':' AND ').$tn.".cache_id IS NOT NULL";
                //$f = false;
            }
            if ( $where ) {
                //$f = true;
                foreach ( $where as $key => $eval ) {
                    
                    if ( strpos( '(', $key ) === false && strpos( ',', $key ) !== false ) {
                        $sql .= (( $first ? " WHERE " : " AND " )) . "(";
                        $first = false;
                        $parts = WSKO_Class_Helper::safe_explode( ',', $key );
                        $f = true;
                        foreach ( $parts as $part ) {
                            $sql .= (( $f ? "" : " OR " )) . $part . $eval;
                            $f = false;
                        }
                        $sql .= ")";
                    } else {
                        $sql .= (( $first ? " WHERE " : " AND " )) . $key . $eval;
                        $first = false;
                    }
                
                }
            }
            $sql_save = $sql . (( $first ? " WHERE " : " AND " ));
            
            if ( !$from_cache_days ) {
                
                if ( !($start === 0 && $end === 0) ) {
                    $sql .= (( $first ? " WHERE " : " AND " )) . $first_tn . ".cache_id IN (" . implode( ",", $ids ) . ")";
                    $first = false;
                }
                
                $sql_save2 = "";
                if ( !isset( $override_join[$first_key]['table_r'] ) || isset( $override_join[$first_key]['full_join'] ) && $override_join[$first_key]['full_join'] ) {
                    foreach ( $field_sets[$first_key] as $f => $v ) {
                        
                        if ( isset( $v['join'] ) && $v['join'] ) {
                            $sql .= (( $first ? " WHERE " : " AND " )) . $first_tn . "." . $f . $v['eval'];
                            $sql_save2 .= (( $first ? " WHERE " : " AND " )) . $first_tn . "." . $f . $v['eval'];
                            $first = false;
                        }
                    
                    }
                }
            }
            
            
            if ( isset( $options['group'] ) && $options['group'] ) {
                $sql .= " GROUP BY " . $options['group'];
                if ( !$from_cache_days ) {
                    $sql_save2 .= " GROUP BY " . $options['group'];
                }
            }
            
            
            if ( $has_no_ref ) {
                $sql_ref = "SELECT id FROM (SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " WHERE time BETWEEN '" . date( 'Y-m-d H:i:s', $has_no_ref['start'] ) . "' AND '" . date( 'Y-m-d H:i:s', $has_no_ref['end'] ) . "') AS table_c";
                $ids = $wpdb->get_results( $sql_ref );
                
                if ( $ids ) {
                    foreach ( $ids as $k => $id ) {
                        $ids[$k] = $id->id;
                    }
                } else {
                    $ids = array( -1 );
                }
                
                $sql = "SELECT t1.* FROM (" . $sql . ") t1 LEFT JOIN (" . $sql_save . $first_tn . ".cache_id IN (" . implode( ",", $ids ) . ")" . $sql_save2 . ") t2 ON t1." . $has_no_ref['key'] . "=t2." . $has_no_ref['key'] . " WHERE t2." . $has_no_ref['key'] . " IS NULL";
            }
            
            
            if ( $having ) {
                $first = true;
                foreach ( $having as $key => $eval ) {
                    
                    if ( strpos( $key, '(' ) === false && strpos( $key, ',' ) !== false ) {
                        $sql .= (( $first ? " HAVING " : " AND " )) . "(";
                        $parts = WSKO_Class_Helper::safe_explode( ',', $key );
                        $f = true;
                        foreach ( $parts as $part ) {
                            $sql .= (( $f ? "" : " OR " )) . $part . $eval;
                            $f = false;
                        }
                        $sql .= ")";
                    } else {
                        $sql .= (( $first ? " HAVING " : " AND " )) . $key . $eval;
                    }
                    
                    $first = false;
                }
            }
            
            if ( $orderby ) {
                $sql .= " ORDER BY " . (( $has_no_ref ? 't1.' : '' )) . $orderby;
            }
            $sql = (( isset( $options['prefix'] ) ? $options['prefix'] . ' ' : '' )) . $sql;
            $sql .= ( isset( $options['suffix'] ) ? ' ' . $options['suffix'] : '' );
            return ( $return_sql ? $sql : $wpdb->get_results( $sql ) );
        }
        
        return ( $return_sql ? $sql : array() );
    }
    
    public static function get_cache_rows_stats( $start, $end, $field_sets = array() )
    {
        global  $wpdb ;
        $int = new static();
        if ( !empty($wpdb->charset) && $wpdb->charset == 'utf8mb4' ) {
            $collate = 'COLLATE utf8mb4_bin';
        }
        $sql = "SELECT *";
        $i = 0;
        foreach ( $field_sets as $fs_data ) {
            $set = $fs_data['table_key'];
            $tn = $int->fieldsets[$set]['prefix'];
            $sql .= ",(SELECT COUNT(*) FROM " . $wpdb->prefix . $int->fieldsets[$set]['table'] . " " . $tn . $i . " WHERE " . $tn . $i . ".cache_id=table_c.id";
            foreach ( $fs_data['filter'] as $f => $v ) {
                $sql .= " AND " . $tn . $i . "." . $f . $v['eval'];
            }
            $sql .= ") " . $fs_data['result_key'];
            $i++;
        }
        $sql .= " FROM (SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table;
        
        if ( $start && $end ) {
            $sql .= " WHERE time BETWEEN '" . date( 'Y-m-d H:i:s', $start ) . "' AND '" . date( 'Y-m-d H:i:s', $end ) . "'";
        } else {
            
            if ( $start === 0 || $end === 0 ) {
                $sql .= " WHERE time BETWEEN '" . (( $start ? date( 'Y-m-d H:i:s', $start ) : '0000-00-00 00:00:00' )) . "' AND '" . (( $end ? date( 'Y-m-d H:i:s', $end ) : '0000-00-00 00:00:00' )) . "'";
            } else {
                $sql .= " WHERE time = '0000-00-00 00:00:00'";
            }
        
        }
        
        $sql .= ") AS table_c";
        /*foreach ($field_sets as $set => $filter)
        		{
        			$tn = $int->fieldsets[$set]['prefix'];
        			$sql .= " INNER JOIN ".$wpdb->prefix.$int->fieldsets[$set]['table']." AS ".$tn." ON table_c.id=".$tn.".cache_id";
        			
        			foreach ($filter as $f => $v)
        			{
        				$sql .= " AND ".$tn.".".$f.$v['eval'];
        			}
        		}*/
        return $wpdb->get_results( $sql );
    }
    
    public static function has_cache_row( $day, $field_sets = array() )
    {
        $results = WSKO_Class_Cache::get_cache_row( $day, $field_sets );
        return ( $results ? true : false );
    }
    
    public static function get_cache_row( $day, $field_sets = array() )
    {
        global  $wpdb ;
        $int = new static();
        if ( !empty($wpdb->charset) && $wpdb->charset == 'utf8mb4' ) {
            $collate = 'COLLATE utf8mb4_bin';
        }
        $sql = "";
        
        if ( $day >= 0 ) {
            $sql = "SELECT * FROM (SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table;
            
            if ( $day ) {
                $sql .= " WHERE time BETWEEN '" . date( 'Y-m-d 00:00:00', $day ) . "' AND '" . date( 'Y-m-d 23:59:59', $day ) . "'";
            } else {
                $sql .= " WHERE time = '0000-00-00 00:00:00'";
            }
            
            $sql .= ") AS table_c";
        }
        
        $first = true;
        foreach ( $field_sets as $set => $filter ) {
            $tn = $int->fieldsets[$set]['prefix'];
            
            if ( $day < 0 && $first ) {
                $sql .= "SELECT * FROM " . $wpdb->prefix . $int->fieldsets[$set]['table'] . " AS " . $tn;
            } else {
                $sql .= " INNER JOIN " . $wpdb->prefix . $int->fieldsets[$set]['table'] . " AS " . $tn;
                $fi = true;
                foreach ( $filter as $f => $v ) {
                    //if ($v['join'])
                    $sql .= (( $fi ? " ON " : " AND " )) . $tn . "." . $f . $v;
                    $fi = false;
                }
            }
            
            $first = false;
        }
        
        if ( $day < 0 ) {
            $first = true;
            $fi = true;
            foreach ( $field_sets as $set => $filter ) {
                
                if ( $first ) {
                    $tn = $int->fieldsets[$set]['prefix'];
                    foreach ( $filter as $f => $v ) {
                        //if ($v['join'])
                        $sql .= (( $fi ? " WHERE " : " AND " )) . $tn . "." . $f . $v;
                        $fi = false;
                    }
                    break;
                }
                
                $first = false;
            }
        }
        
        /*$f = true;
        		foreach ($field_sets as $set => $filter)
        		{
        			$sql .= ($f?' WHERE ':' AND ').$tn.".cache_id IS NOT NULL";
        			$f = false;
        		}*/
        $res = $wpdb->get_results( $sql );
        if ( isset( $res[0] ) ) {
            return $res[0];
        }
        return false;
    }
    
    public static function set_cache_row(
        $day,
        $fieldset_data = array(),
        $append = false,
        $chunk = true,
        &$ids = array()
    )
    {
        global  $wpdb ;
        $int = new static();
        $sql = "SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " AS table_c";
        
        if ( $day ) {
            $sql .= " WHERE table_c.time BETWEEN '" . date( 'Y-m-d 00:00:00', $day ) . "' AND '" . date( 'Y-m-d 23:59:59', $day ) . "'";
        } else {
            $sql .= " WHERE table_c.time = '0000-00-00 00:00:00'";
        }
        
        $rows = $wpdb->get_results( $sql );
        $cache_row_id = false;
        if ( $rows && count( $rows ) > 0 ) {
            
            if ( count( $rows ) > 1 ) {
                WSKO_Class_Cache::delete_cache_row( $day, array(), true );
            } else {
                $cache_row_id = $rows[0]->id;
            }
        
        }
        
        if ( !$cache_row_id ) {
            $wpdb->insert( $wpdb->prefix . $int->cache_rows_table, array(
                'time' => ( $day ? date( 'Y-m-d', $day ) : '0000-00-00' ),
            ) );
            $cache_row_id = $wpdb->insert_id;
        }
        
        foreach ( $fieldset_data as $data ) {
            if ( !$append ) {
                $wpdb->delete( $wpdb->prefix . $int->fieldsets[$data['set']]['table'], array_merge( array(
                    'cache_id' => $cache_row_id,
                ), ( isset( $data['where'] ) ? $data['where'] : array() ) ) );
            }
            
            if ( $chunk ) {
                $chunks = array_chunk( $data['rows'], 100 );
                foreach ( $chunks as $chunk ) {
                    $data['rows'] = $chunk;
                    WSKO_Class_Cache::set_cache_row_bulk( $day, array( $data ), true );
                    /*foreach ($chunk as $k => $row)
                    		{
                    			if ($row)
                    			{
                    				$chunk[$k]['cache_id'] = $cache_row_id;
                    				if (isset($data['where']))
                    					$chunk[$k] = array_merge($chunk[$k], $data['where']);
                    				$wpdb->insert($wpdb->prefix.$int->fieldsets[$data['set']]['table'], $row);
                    				$ids[] = $wpdb->insert_id;
                    			}
                    		}*/
                }
            } else {
                foreach ( $data['rows'] as $row ) {
                    
                    if ( $row ) {
                        $row['cache_id'] = $cache_row_id;
                        if ( isset( $data['where'] ) ) {
                            $row = array_merge( $row, $data['where'] );
                        }
                        $wpdb->insert( $wpdb->prefix . $int->fieldsets[$data['set']]['table'], $row );
                        $ids[] = $wpdb->insert_id;
                    }
                
                }
            }
        
        }
    }
    
    public static function set_cache_row_bulk( $day, $fieldset_data = array(), $append = false )
    {
        global  $wpdb ;
        $int = new static();
        $sql = "SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " AS table_c";
        
        if ( $day ) {
            $sql .= " WHERE table_c.time BETWEEN '" . date( 'Y-m-d 00:00:00', $day ) . "' AND '" . date( 'Y-m-d 23:59:59', $day ) . "'";
        } else {
            $sql .= " WHERE table_c.time = '0000-00-00 00:00:00'";
        }
        
        $rows = $wpdb->get_results( $sql );
        $cache_row_id = false;
        if ( $rows && count( $rows ) > 0 ) {
            
            if ( count( $rows ) > 1 ) {
                WSKO_Class_Cache::delete_cache_row( $day, array(), true );
            } else {
                $cache_row_id = $rows[0]->id;
            }
        
        }
        
        if ( !$cache_row_id ) {
            $wpdb->insert( $wpdb->prefix . $int->cache_rows_table, array(
                'time' => ( $day ? date( 'Y-m-d', $day ) : '0000-00-00' ),
            ) );
            $cache_row_id = $wpdb->insert_id;
        }
        
        foreach ( $fieldset_data as $data ) {
            if ( !$append ) {
                $wpdb->delete( $wpdb->prefix . $int->fieldsets[$data['set']]['table'], array_merge( array(
                    'cache_id' => $cache_row_id,
                ), ( isset( $data['where'] ) ? $data['where'] : array() ) ) );
            }
            $sql = '';
            $f = true;
            $header = array();
            foreach ( $data['rows'] as $row ) {
                
                if ( $row ) {
                    $row['cache_id'] = $cache_row_id;
                    if ( isset( $data['where'] ) ) {
                        $row = array_merge( $row, $data['where'] );
                    }
                    $row = (array) $row;
                    if ( !$header ) {
                        foreach ( $row as $k => $v ) {
                            $header[] = $k;
                        }
                    }
                    $sql .= (( $f ? '' : ',' )) . '("' . implode( '","', array_map( 'esc_sql', $row ) ) . '")';
                    $f = false;
                }
            
            }
            if ( $sql ) {
                $wpdb->query( "INSERT INTO " . $wpdb->prefix . $int->fieldsets[$data['set']]['table'] . " (" . implode( ',', $header ) . ") VALUES " . $sql );
            }
        }
    }
    
    public static function update_cache_data_row( $where, $field_set, $data )
    {
        global  $wpdb ;
        $int = new static();
        if ( isset( $int->fieldsets[$field_set] ) ) {
            $wpdb->update( $wpdb->prefix . $int->fieldsets[$field_set]['table'], $data, $where );
        }
    }
    
    public static function delete_cache_fieldset_row( $field_sets = array() )
    {
        global  $wpdb ;
        $int = new static();
        foreach ( $field_sets as $set => $fieldset_where ) {
            if ( isset( $int->fieldsets[$set] ) ) {
                $wpdb->delete( $wpdb->prefix . $int->fieldsets[$set]['table'], $fieldset_where );
            }
        }
    }
    
    public static function delete_cache_row( $day, $field_sets = array(), $complete = false )
    {
        global  $wpdb ;
        $int = new static();
        $sql = "SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " AS table_c";
        if ( $day ) {
            
            if ( $day ) {
                $sql .= " WHERE table_c.time BETWEEN '" . date( 'Y-m-d 00:00:00', $day ) . "' AND '" . date( 'Y-m-d 23:59:59', $day ) . "'";
            } else {
                $sql .= " WHERE table_c.time = '0000-00-00 00:00:00'";
            }
        
        }
        $rows = $wpdb->get_results( $sql );
        if ( $rows ) {
            foreach ( $rows as $row ) {
                foreach ( $int->fieldsets as $set => $fieldset ) {
                    if ( empty($field_sets) || in_array( $set, $field_sets ) || $complete ) {
                        $wpdb->delete( $wpdb->prefix . $fieldset['table'], array(
                            'cache_id' => $row->id,
                        ) );
                    }
                }
                if ( $complete ) {
                    $wpdb->delete( $wpdb->prefix . $int->cache_rows_table, array(
                        'id' => $row->id,
                    ) );
                }
            }
        }
    }
    
    public static function delete_cache_rows_before( $day )
    {
        global  $wpdb ;
        $int = new static();
        $sql = "SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " AS table_c";
        $sql .= " WHERE table_c.time < '" . date( 'Y-m-d 00:00:00', $day ) . "' AND table_c.time != '0000-00-00 00:00:00'";
        $rows = $wpdb->get_results( $sql );
        if ( $rows ) {
            foreach ( $rows as $row ) {
                foreach ( $int->fieldsets as $fieldset ) {
                    $wpdb->delete( $wpdb->prefix . $fieldset['table'], array(
                        'cache_id' => $row->id,
                    ) );
                }
                $wpdb->delete( $wpdb->prefix . $int->cache_rows_table, array(
                    'id' => $row->id,
                ) );
            }
        }
    }
    
    public static function delete_cache_rows( $field_sets, $is_cache_child = true )
    {
        global  $wpdb ;
        $int = new static();
        
        if ( $is_cache_child ) {
            $sql = "SELECT * FROM " . $wpdb->prefix . $int->cache_rows_table . " AS table_c";
            $rows = $wpdb->get_results( $sql );
            if ( $rows ) {
                foreach ( $rows as $row ) {
                    foreach ( $int->fieldsets as $k => $fieldset ) {
                        if ( in_array( $k, $field_sets ) ) {
                            $wpdb->delete( $wpdb->prefix . $fieldset['table'], array(
                                'cache_id' => $row->id,
                            ) );
                        }
                    }
                }
            }
        } else {
            foreach ( $int->fieldsets as $k => $fieldset ) {
                if ( in_array( $k, $field_sets ) ) {
                    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . $fieldset['table'] );
                }
            }
        }
    
    }
    
    public static function delete_cache()
    {
        global  $wpdb ;
        $int = new static();
        if ( $int->cache_rows_table && substr( $int->cache_rows_table, 0, 5 ) == 'wsko_' ) {
            $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . $int->cache_rows_table );
        }
        foreach ( $int->fieldsets as $f ) {
            $t = $f['table'];
            if ( $t && substr( $t, 0, 5 ) == 'wsko_' ) {
                //security
                $wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . $t );
            }
        }
    }
    
    public static function prepare_session_cache()
    {
        if ( static::$cache_prepared ) {
            return true;
        }
        static::$cache_prepared = true;
        
        if ( WSKO_Class_Core::is_configured() ) {
            $cache_state = ( WSKO_Class_Core::is_premium() ? 'prem' : 'free' );
            $old_state = WSKO_Class_Cache::get_session_cache( 'cache_state' );
            if ( $old_state && $cache_state != $old_state ) {
                WSKO_Class_Cache::clear_session_cache( false );
            }
            WSKO_Class_Cache::set_session_cache( 'cache_state', $cache_state );
        }
        
        return true;
    }
    
    public static function set_session_cache( $key, $data, $expire = 7200 )
    {
        if ( !is_scalar( $key ) ) {
            return;
        }
        $key_data = array(
            'expire' => time() + $expire,
            'data'   => $data,
        );
        $key_store = WSKO_Class_Cache::get_cache_path( 'session', $key );
        
        if ( $key_store ) {
            $res = file_put_contents( $key_store, serialize( $key_data ) );
            if ( $res !== false ) {
                static::$session_cache[$key] = $data;
            }
        }
    
    }
    
    public static function get_session_cache( $key )
    {
        if ( !is_scalar( $key ) ) {
            return false;
        }
        if ( isset( static::$session_cache[$key] ) ) {
            return static::$session_cache[$key];
        }
        $key_store = WSKO_Class_Cache::get_cache_path( 'session', $key );
        
        if ( $key_store && file_exists( $key_store ) ) {
            $cache = file_get_contents( $key_store );
            
            if ( $cache ) {
                $data = unserialize( $cache );
                
                if ( $data && isset( $data['expire'] ) && $data['expire'] > time() && isset( $data['data'] ) && $data['data'] ) {
                    static::$session_cache[$key] = $data['data'];
                    return $data['data'];
                }
            
            }
        
        }
        
        return false;
    }
    
    public static function clear_session_cache( $key = false )
    {
        
        if ( $key ) {
            $key_file = WSKO_Class_Cache::get_cache_path( 'session', $key );
            if ( file_exists( $key_file ) && is_file( $key_file ) ) {
                unlink( $key_file );
            }
        } else {
            $key_store = WSKO_Class_Cache::get_cache_path( 'session' );
            
            if ( $key_store ) {
                $key_files = glob( $key_store . '*' );
                foreach ( $key_files as $f ) {
                    if ( is_file( $f ) ) {
                        unlink( $f );
                    }
                }
            }
        
        }
    
    }
    
    public static function get_cache_path( $type, $key = false )
    {
        $key_store = WSKO_Class_Helper::get_temp_dir( $type );
        if ( $key_store && is_writable( $key_store ) && is_readable( $key_store ) ) {
            return $key_store . (( $key ? $key . '.cache' : '' ));
        }
        return false;
    }

}