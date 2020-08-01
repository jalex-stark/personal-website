<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$post_id = ( isset( $template_args['post_id'] ) ? $template_args['post_id'] : false );
$post_type = ( isset( $template_args['post_type'] ) ? $template_args['post_type'] : false );
$post_tax = ( isset( $template_args['post_tax'] ) ? $template_args['post_tax'] : false );
$post_term = ( isset( $template_args['post_term'] ) ? $template_args['post_term'] : false );
$meta_view = ( isset( $template_args['meta_view'] ) ? $template_args['meta_view'] : 'metas' );
$type = ( isset( $template_args['type'] ) ? $template_args['type'] : false );
$collapse = ( isset( $template_args['collapse'] ) && $template_args['collapse'] ? true : false );
$is_collapse = ( isset( $template_args['is_collapse_open'] ) && $template_args['is_collapse_open'] ? true : false );
$dataParent = ( isset( $template_args['data-parent'] ) ? $template_args['data-parent'] : false );
$preview = ( isset( $template_args['preview'] ) ? $template_args['preview'] : false );
$term_id = false;
$arg = false;
$metas = array();
$tax_m = array();
switch ( $type ) {
    case 'post_id':
        $arg = $post_id;
        break;
    case 'post_archive':
        $arg = $post_type;
        break;
    case 'post_type':
        $url = get_post_type_archive_link( $arg );
        
        if ( strpos( $url, '?' ) !== false ) {
            $url .= '&' . $arg . '=' . 'a-post';
        } else {
            $url .= '/a-post/';
        }
        
        $first_post = WSKO_Class_Helper::get_first_post( $post_type );
        if ( $first_post ) {
            $post_id = $first_post->ID;
        }
        $arg = $post_type;
        $overrides = array(
            'tax'  => false,
            'term' => false,
            'post' => $post_id,
        );
        break;
    case 'post_tax':
        $arg = $post_tax;
        $tax = $post_tax;
        $term_f = false;
        $terms = get_terms( array(
            'taxonomy'   => $post_tax,
            'hide_empty' => false,
        ) );
        foreach ( $terms as $term ) {
            
            if ( !$term_f ) {
                $term_f = $term;
                break;
            }
        
        }
        $term_id = ( $term_f ? $term_f->term_id : false );
        $term = $term_f;
        break;
    case 'post_term':
        $arg = $post_term;
        $args = WSKO_Class_Helper::safe_explode( ':', $arg, 2 );
        $term_id = false;
        $tax = false;
        
        if ( count( $args ) == 2 ) {
            $tax = $args[0];
            $term_id = $args[1];
        }
        
        break;
    case 'other':
        $arg = ( isset( $template_args['arg'] ) ? $template_args['arg'] : false );
        break;
}

if ( $preview ) {
    $meta_obj = WSKO_Class_Compatibility::get_meta_object_ext( $arg, $type, $preview );
} else {
    $meta_obj = WSKO_Class_Onpage::get_meta_object( $arg, $type );
}


if ( $post_id ) {
    //global $post;
    $post = get_post( $post_id );
    //setup_postdata($post);
    $title_o = WSKO_Class_Helper::sanitize_meta( get_the_title( $post->ID ) );
    $title_d = WSKO_Class_Helper::get_default_page_title( $title_o );
}


if ( $term_id ) {
    $term = get_term_by( 'id', $term_id, $tax );
    $title_d = WSKO_Class_Helper::get_default_page_title( WSKO_Class_Helper::sanitize_meta( $term->name ) );
}

$unique_id = WSKO_Class_Helper::get_unique_id();
?><div class="wsko-set-metas-wrapper <?php 
echo  ( $collapse ? 'wsko-collapsed' : '' ) ;
?>" <?php 
echo  ( $preview ? 'data-saving-disabled="true"' : '' ) ;
?> data-type="<?php 
echo  $type ;
?>" data-arg="<?php 
echo  $arg ;
?>" data-nonce="<?php 
echo  wp_create_nonce( 'wsko_set_metas' ) ;
?>" <?php 
echo  ( $meta_view === 'noindex' || $meta_view === 'metas' || $meta_view === 'co' ? 'data-robots="true"' : '' ) ;
?> <?php 
echo  ( $meta_view === 'canonical' ? 'data-canonical="true"' : '' ) ;
?> style="position:relative;">
	<div class="<?php 
echo  ( $type === 'post_id' || $type === 'post_term' ? 'wsko-row' : 'wsko-row' ) ;
?>">
		<div class="<?php 
echo  ( $type === 'post_id' || $type === 'post_term' || $meta_view === 'links' ? 'wsko-col-sm-12' : 'wsko-col-sm-8' ) ;
?> wsko-col-xs-12">
			<a href="#" class="button wsko-metas-save-button"><?php 
echo  __( 'Save', 'wp-seo-keyword-optimizer' ) ;
?></a>
			<?php 

if ( $meta_view === 'social' || $meta_view === 'co' ) {
    ?>
			<ul class="wsko-nav bsu-tabs bsu-tabs-sm wsko-tabs-social-snippets">
				<?php 
    
    if ( $meta_view === 'co' ) {
        ?>
					<li><a class="wsko-nav-link wsko-nav-link-active" href="#wsko_metas_<?php 
        echo  $unique_id ;
        ?>_google"><i class="fa fa-google fa-fw"></i> Google</a></li>		
				<?php 
    }
    
    ?>
				<li><a class="wsko-nav-link <?php 
    echo  ( $meta_view != 'co' ? 'wsko-nav-link-active' : '' ) ;
    ?>" href="#wsko_metas_<?php 
    echo  $unique_id ;
    ?>_facebook"><i class="fa fa-facebook fa-fw"></i> Facebook</a></li>
				<li><a class="wsko-nav-link" href="#wsko_metas_<?php 
    echo  $unique_id ;
    ?>_twitter"><i class="fa fa-twitter fa-fw"></i> Twitter</a></li>
				<?php 
    /* <li><a href="#wsko_metas_<?=$unique_id?>_tab" data-toggle="tab"><i class="fa fa-internet-explorer fa-fw"></i> Tab</a></li> */
    ?>
				<?php 
    if ( $meta_view === 'co' ) {
    }
    ?>
			</ul>
			<?php 
}

?>
			<div class="wsko-tab-content">
				<?php 

if ( $meta_view === 'metas' || $meta_view === 'co' ) {
    ?>
					<div id="wsko_metas_<?php 
    echo  $unique_id ;
    ?>_google" class="wsko-tab wsko-meta-tab <?php 
    echo  ( $meta_view === 'co' || $meta_view === 'metas' ? 'wsko-tab-active' : '' ) ;
    ?>">
						<?php 
    
    if ( $collapse ) {
        ?>
							<a class="wsko-metas-bulk-collapse btn btn-flat btn-sm pull-right m10" data-toggle="collapse" data-toggle-heading="Collapse" href="#snippet_input_wrapper_<?php 
        echo  $post_id ;
        ?>_<?php 
        echo  $unique_id ;
        ?>" aria-expanded="false">
							<?php 
        echo  __( 'Edit', 'wsko' ) ;
        ?>
							</a>
						<?php 
    }
    
    ?>
						<?php 
    $title_r = false;
    $desc_r = false;
    WSKO_Class_Template::render_meta_snippet_container(
        'google',
        $type,
        $arg,
        array(
        'post_id' => $post_id,
        'term_id' => $term_id,
        'preview' => $preview,
    ),
        false,
        $tile_r,
        $desc_r
    );
    ?>
						<div class="<?php 
    echo  ( $collapse ? 'collapse' . (( $is_collapse ? ' in' : '' )) : '' ) ;
    ?>" <?php 
    echo  ( $collapse ? 'id="snippet_input_wrapper_' . $post_id . '_' . $unique_id . '"' : '' ) ;
    ?>>	
							<input name="meta_view" type="hidden" value="<?php 
    echo  $meta_view ;
    ?>">
							<input name="collapse" type="hidden" value="<?php 
    echo  ( $collapse ? 'true' : 'false' ) ;
    ?>">
							<?php 
    echo  WSKO_Class_Template::render_form( array(
        'type'         => 'input',
        'title'        => __( 'Title', 'wsko' ),
        'value'        => htmlentities( ( $meta_obj && isset( $meta_obj['title'] ) ? $meta_obj['title'] : '' ) ),
        'class'        => 'wsko-metas-field-title',
        'name'         => 'title',
        'placeholder'  => __( 'Title - ', 'wsko' ) . (( $title_r ? __( "Default by ", 'wsko' ) . (( $type == "post_term" ? 'taxonomy' : 'post type' )) . ": '" . $title_r . "'" : __( "Default title", 'wsko' ) )),
        'id'           => 'wsko_meta_title_input',
        'progressBar'  => ( $type == 'post_id' && $post_id ? true : false ),
        'progressID'   => 'meta_title-' . $post_id,
        'progressType' => 'google_title',
    ), true ) ;
    echo  WSKO_Class_Template::render_form( array(
        'type'         => 'textarea',
        'title'        => __( 'Description', 'wsko' ),
        'value'        => htmlentities( ( $meta_obj && isset( $meta_obj['desc'] ) ? $meta_obj['desc'] : '' ) ),
        'class'        => 'wsko-metas-field-desc',
        'name'         => 'desc',
        'placeholder'  => __( 'Description - ', 'wsko' ) . (( $desc_r ? __( "Default by ", 'wsko' ) . (( $type == "post_term" ? 'taxonomy' : 'post type' )) . ": '" . $desc_r . "'" : __( 'not set', 'wsko' ) )),
        'id'           => 'wsko_meta_description_input',
        'progressBar'  => ( $type == 'post_id' && $post_id ? true : false ),
        'progressID'   => 'meta_description-' . $post_id,
        'progressType' => 'google_desc',
    ), true ) ;
    ?>								
								<div class="wsko-row form-group">
									<div class="wsko-col-sm-3 wsko-col-xs-12">
										<?php 
    echo  __( 'Robots', 'wsko' ) ;
    ?>
									</div>
									<div class="wsko-col-sm-9 wsko-col-xs-12">
										<label><input class="form-control wsko-metas-field-robotsnf" name="robots_nf" type="checkbox" <?php 
    echo  ( $meta_obj && isset( $meta_obj['robots'] ) && ($meta_obj['robots'] == 1 || $meta_obj['robots'] == 3) ? 'checked' : '' ) ;
    ?>> NoFollow</label>
										<label><input class="form-control wsko-metas-field-robotsni" name="robots_ni" type="checkbox" <?php 
    echo  ( $meta_obj && isset( $meta_obj['robots'] ) && ($meta_obj['robots'] == 2 || $meta_obj['robots'] == 3) ? 'checked' : '' ) ;
    ?>> NoIndex</label>
									</div>
								</div>	
								<?php 
    //echo WSKO_Class_Template::render_form(array('type' => 'submit', 'class' => 'btn btn primary'), true);
    ?> 
						</div>
					</div>		
				<?php 
}


if ( $meta_view === 'social' || $meta_view === 'co' ) {
    ?>
				<div id="wsko_metas_<?php 
    echo  $unique_id ;
    ?>_facebook" class="wsko-tab wsko-meta-tab <?php 
    echo  ( $meta_view != 'co' ? 'wsko-tab-active' : '' ) ;
    ?>">
					<?php 
    WSKO_Class_Template::render_meta_snippet_container(
        'facebook',
        $type,
        $arg,
        array(
        'post_id' => $post_id,
        'term_id' => $term_id,
        'preview' => $preview,
    )
    );
    ?>

						<input name="meta_view" type="hidden" value="<?php 
    echo  $meta_view ;
    ?>">
						<input name="collapse" type="hidden" value="<?php 
    echo  ( $collapse ? 'true' : 'false' ) ;
    ?>">
						<?php 
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'input',
        'title'       => __( 'Facebook Title', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['og_title'] ) ? $meta_obj['og_title'] : '' ) ),
        'class'       => 'wsko-metas-field-title',
        'name'        => 'og_title',
        'placeholder' => __( 'Facebook Title', 'wsko' ),
    ), true ) ;
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'textarea',
        'title'       => __( 'Facebook Description', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['og_desc'] ) ? $meta_obj['og_desc'] : '' ) ),
        'class'       => 'wsko-metas-field-desc',
        'name'        => 'og_desc',
        'placeholder' => __( 'Facebook Description', 'wsko' ),
    ), true ) ;
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'url_media',
        'title'       => __( 'Facebook Preview Image', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['og_img'] ) ? $meta_obj['og_img'] : '' ) ),
        'class'       => 'wsko-metas-field-img',
        'name'        => 'og_img',
        'placeholder' => __( 'Image URL', 'wsko' ),
    ), true ) ;
    //echo WSKO_Class_Template::render_form(array('type' => 'submit', 'class' => 'btn btn primary'), true);
    ?>	
		
				</div>
				<div id="wsko_metas_<?php 
    echo  $unique_id ;
    ?>_twitter" class="wsko-tab wsko-meta-tab">
					<?php 
    WSKO_Class_Template::render_meta_snippet_container(
        'twitter',
        $type,
        $arg,
        array(
        'post_id' => $post_id,
        'term_id' => $term_id,
        'preview' => $preview,
    )
    );
    ?>
						<input name="meta_view" type="hidden" value="<?php 
    echo  $meta_view ;
    ?>">
						<input name="collapse" type="hidden" value="<?php 
    echo  ( $collapse ? 'true' : 'false' ) ;
    ?>">
						
						<?php 
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'input',
        'title'       => __( 'Twitter Title', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['tw_title'] ) ? $meta_obj['tw_title'] : '' ) ),
        'class'       => 'wsko-metas-field-title',
        'name'        => 'tw_title',
        'placeholder' => __( 'Twitter Title', 'wsko' ),
    ), true ) ;
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'textarea',
        'title'       => __( 'Twitter Description', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['tw_desc'] ) ? $meta_obj['tw_desc'] : '' ) ),
        'class'       => 'wsko-metas-field-desc',
        'name'        => 'tw_desc',
        'placeholder' => __( 'Twitter Description', 'wsko' ),
    ), true ) ;
    echo  WSKO_Class_Template::render_form( array(
        'type'        => 'url_media',
        'title'       => __( 'Twitter Preview Image', 'wsko' ),
        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['tw_img'] ) ? $meta_obj['tw_img'] : '' ) ),
        'class'       => 'wsko-metas-field-img',
        'name'        => 'tw_img',
        'placeholder' => __( 'Image URL', 'wsko' ),
    ), true ) ;
    //echo WSKO_Class_Template::render_form(array('type' => 'submit', 'class' => 'btn btn primary'), true);
    ?>	
				</div>
				<?php 
} else {
    
    if ( $meta_view === 'noindex' ) {
        ?>
					<div class="wsko-meta-tab">
						<input name="meta_view" type="hidden" value="<?php 
        echo  $meta_view ;
        ?>">					
						<div class="wsko-row form-group">
							<div class="wsko-col-sm-3 wsko-col-xs-12">
								<?php 
        echo  __( 'Robots', 'wsko' ) ;
        ?>
							</div>
							<div class="wsko-col-sm-9 wsko-col-xs-12">
								<label><input class="form-control wsko-metas-field-robotsnf" name="robots_nf" type="checkbox" <?php 
        echo  ( $meta_obj && isset( $meta_obj['robots'] ) && ($meta_obj['robots'] == 1 || $meta_obj['robots'] == 3) ? 'checked' : '' ) ;
        ?>> <?php 
        echo  __( 'NoFollow', 'wsko' ) ;
        ?></label>
								<label><input class="form-control wsko-metas-field-robotsni" name="robots_ni" type="checkbox" <?php 
        echo  ( $meta_obj && isset( $meta_obj['robots'] ) && ($meta_obj['robots'] == 2 || $meta_obj['robots'] == 3) ? 'checked' : '' ) ;
        ?>> <?php 
        echo  __( 'NoIndex', 'wsko' ) ;
        ?></label>
							</div>
						</div>	
						<?php 
        echo  WSKO_Class_Template::render_form( array(
            'type'  => 'submit',
            'class' => 'btn btn primary',
        ), true ) ;
        ?> 

					</div>
				<?php 
    } else {
        
        if ( $meta_view === 'links' ) {
            ?><div class="wsko-meta-tab">
							<input name="meta_view" type="hidden" value="<?php 
            echo  $meta_view ;
            ?>"><?php 
            $auto_redirects = WSKO_Class_Onpage::get_auto_redirects( false );
            
            if ( $type === "post_id" && $post ) {
                $slug = $post->post_name;
                echo  WSKO_Class_Template::render_form( array(
                    'type'        => 'input',
                    'title'       => __( 'URL Slug', 'wsko' ),
                    'value'       => htmlentities( $slug ),
                    'class'       => 'wsko-metas-field-url',
                    'name'        => 'url',
                    'placeholder' => __( 'some-slug', 'wsko' ),
                    'id'          => 'wsko_meta_url_input',
                ), true ) ;
            } else {
                
                if ( $type === "post_type" ) {
                    $data = WSKO_Class_Helper::get_obj_rewrite_base( $type, $arg );
                    
                    if ( $data ) {
                        $first_slug = $data['original'];
                        $curr_slug = $data['current'];
                    } else {
                        $first_slug = '<i>' . __( 'not provided', 'wsko' ) . '</i>';
                        $curr_slug = '<i>' . __( 'not provided', 'wsko' ) . '</i>';
                    }
                    
                    /*?><div class="wsko-row form-group">
                    			<div class="wsko-col-sm-3 wsko-col-xs-12">
                    				<?=__('Hide Slug', 'wsko')?>
                    			</div>
                    			<div class="wsko-col-sm-9 wsko-col-xs-12">
                    				<label><input class="form-control wsko-metas-field-hide-slug" name="hide_slug" type="checkbox" <?=($meta_obj && isset($meta_obj['hide_slug']) && $meta_obj['hide_slug']) ? 'checked' : ''?>> Hide Slug</label>
                    			</div>
                    		</div><?php*/
                    echo  WSKO_Class_Template::render_form( array(
                        'type'        => 'input',
                        'title'       => sprintf( __( 'Post Type Slug<br/><small class="text-off">Original: %s • Current: %s</small>', 'wsko' ), $first_slug, $curr_slug ),
                        'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['slug'] ) ? $meta_obj['slug'] : '' ) ),
                        'class'       => 'wsko-metas-field-url',
                        'name'        => 'url',
                        'placeholder' => __( 'The URL part between your domain and the post name', 'wsko' ),
                        'id'          => 'wsko_meta_url_input',
                    ), true ) ;
                } else {
                    
                    if ( $type === "post_tax" ) {
                        $data = WSKO_Class_Helper::get_obj_rewrite_base( $type, $arg );
                        
                        if ( $data ) {
                            $first_slug = $data['original'];
                            $curr_slug = $data['current'];
                        } else {
                            $first_slug = '<i>' . __( 'not provided', 'wsko' ) . '</i>';
                            $curr_slug = '<i>' . __( 'not provided', 'wsko' ) . '</i>';
                        }
                        
                        echo  WSKO_Class_Template::render_form( array(
                            'type'        => 'input',
                            'title'       => sprintf( __( 'Taxonomy Slug<br/><small class="text-off">Original: %s • Current: %s</small>', 'wsko' ), $first_slug, $curr_slug ),
                            'value'       => htmlentities( ( $meta_obj && isset( $meta_obj['slug'] ) ? $meta_obj['slug'] : '' ) ),
                            'class'       => 'wsko-metas-field-url',
                            'name'        => 'url',
                            'placeholder' => __( 'The URL part between your domain and the post name', 'wsko' ),
                            'id'          => 'wsko_meta_url_input',
                        ), true ) ;
                    }
                
                }
            
            }
            
            
            if ( $type === 'post_type' || $type === 'post_tax' ) {
                ?>
								<div class="wsko-border wsko-mb10 wsko-mt10 hidden-xs"></div>
								
								<div class="wsko-row form-group hidden-xs">
									<div class="wsko-col-sm-3 wsko-col-xs-12">
										<p><?php 
                echo  __( 'Automatic redirects', 'wsko' ) ;
                ?></p>
									</div>
									<div class="wsko-col-sm-9 wsko-col-xs-12">
										<div class="wsko-row"> <div class="col-sm-5"><label class="wsko-label small"><?php 
                echo  __( 'Redirect from', 'wsko' ) ;
                ?></label></div> <div class="col-sm-5"><label class="wsko-label small"><?php 
                echo  __( 'Redirect to', 'wsko' ) ;
                ?></label></div> <div class="col-sm-2"><label class="wsko-label small"><?php 
                echo  __( 'Count', 'wsko' ) ;
                ?></label></div> </div>
										<ul class="wsko-auto-redirect-wrapper"><?php 
                
                if ( isset( $auto_redirects[$type][$arg] ) && $auto_redirects[$type][$arg] && $auto_redirects[$type][$arg]['source'] ) {
                    $auto_redirects = $auto_redirects[$type][$arg];
                    foreach ( $auto_redirects['source'] as $re => $link_snapshot ) {
                        ?>
													<li>
														<div class="row">	
															<div class="col-sm-5">
																<?php 
                        echo  WSKO_Class_Helper::home_url( $re ) . '/*' ;
                        ?>
															</div>
															<div class="col-sm-5">
																<?php 
                        echo  WSKO_Class_Helper::home_url( $auto_redirects['target'] . '/*' ) ;
                        ?>
															</div>
															<div class="col-sm-1">
																<?php 
                        echo  count( $link_snapshot ) ;
                        ?>
															</div>	
															<div class="col-sm-1 align-right wsko-a-normalize">
																<?php 
                        WSKO_Class_Template::render_ajax_button(
                            '<i class="fa fa-times dark"></i>',
                            'remove_auto_redirect',
                            array(
                            'type' => $type,
                            'arg'  => $arg,
                            'key'  => $re,
                        ),
                            array()
                        );
                        ?>
															</div>															
														</div>
													</li><?php 
                    }
                } else {
                    ?><li><?php 
                    echo  __( 'No redirect found.', 'wsko' ) ;
                    ?></li><?php 
                }
                
                ?></ul>
									</div>
								</div>	
						<?php 
            }
            
            ?>
						</div>
					<?php 
        } else {
            
            if ( $meta_view === 'canonical' ) {
                ?>
					<div class="wsko-meta-tab">
						<input name="meta_view" type="hidden" value="<?php 
                echo  $meta_view ;
                ?>">
						<fieldset>
						<label><input class="wsko-form-control wsko-metas-field-canonical-auto" type="radio" name="canon_t" value="1" <?php 
                echo  ( isset( $meta_obj['canon']['type'] ) && $meta_obj['canon']['type'] === 1 || !isset( $meta_obj['canon']['type'] ) ? 'checked' : '' ) ;
                ?>> <?php 
                echo  __( 'Auto', 'wsko' ) ;
                ?></label>
						<label><input class="wsko-form-control wsko-metas-field-canonical-off" type="radio" name="canon_t" value="0" <?php 
                echo  ( isset( $meta_obj['canon']['type'] ) && $meta_obj['canon']['type'] === 0 ? 'checked' : '' ) ;
                ?>> <?php 
                echo  __( 'No Tag', 'wsko' ) ;
                ?></label>
						<label><input class="wsko-form-control wsko-metas-field-canonical-custom" type="radio" name="canon_t" value="2" <?php 
                echo  ( isset( $meta_obj['canon']['type'] ) && ($meta_obj['canon']['type'] === 2 || $meta_obj['canon']['type'] === 3) ? 'checked' : '' ) ;
                ?>> <?php 
                echo  __( 'Custom', 'wsko' ) ;
                ?></label>
						</fieldset>
						<input type="text" class="wsko-form-control wsko-metas-field-canonical-spec" placeholder="<?php 
                echo  __( 'Set a specific canonical by URL or Post-ID', 'wsko' ) ;
                ?>" value="<?php 
                echo  ( isset( $meta_obj['canon']['arg'] ) ? $meta_obj['canon']['arg'] : '' ) ;
                ?>" <?php 
                echo  ( !isset( $meta_obj['canon']['type'] ) || ($meta_obj['canon']['type'] === 0 || $meta_obj['canon']['type'] === 1) ? 'style="display:none;"' : '' ) ;
                ?>>
						<?php 
                
                if ( !isset( $meta_obj['canon']['type'] ) || $meta_obj['canon']['type'] === 1 ) {
                    ?><small class="text-off"><?php 
                    echo  __( 'Autolink to the same page if Automatic Canonicals are activated.', 'wsko' ) ;
                    ?> <?php 
                    echo  WSKO_Class_Template::render_page_link(
                        WSKO_Controller_Tools::get_instance(),
                        'links',
                        __( 'Activate Automatic Canonicals', 'wsko' ),
                        array(
                        'button' => false,
                    ),
                        false
                    ) ;
                    ?></small><?php 
                } else {
                    
                    if ( $meta_obj['canon']['type'] === 0 ) {
                        ?><small class="text-off"><?php 
                        echo  __( 'No tag will be set', 'wsko' ) ;
                        ?></small><?php 
                    } else {
                        
                        if ( $meta_obj['canon']['type'] === 2 ) {
                            ?><small class="text-off"><?php 
                            echo  __( 'By Post:', 'wsko' ) ;
                            ?> <?php 
                            echo  get_permalink( $meta_obj['canon']['arg'] ) ;
                            ?></small><?php 
                        } else {
                            
                            if ( $meta_obj['canon']['type'] === 3 ) {
                                ?><small class="text-off"><?php 
                                echo  __( 'By URL:', 'wsko' ) ;
                                ?> <?php 
                                echo  WSKO_Class_Helper::format_url( $meta_obj['canon']['arg'] ) ;
                                ?></small><?php 
                            }
                        
                        }
                    
                    }
                
                }
                
                ?>
					</div>
					<?php 
            }
        
        }
    
    }

}

?>
			</div>
		</div>

		<?php 

if ( $type !== 'post_id' && $type !== 'post_term' && $meta_view !== 'links' ) {
    ?><div class="wsko-col-sm-4 wsko-col-xs-12"><?php 
    WSKO_Class_Template::render_dragndrop_metas( $type, $arg, array() );
    ?></div><?php 
}

?>	
	</div>
	<div class="wsko-metas-load-overlay" style="position:absolute;top:0px;width:100%;height:100%;opacity:0.7;background-color:white;display:none;">
		<?php 
echo  WSKO_Class_Template::render_wsko_preloader( array(
    'size' => 'big',
) ) ;
?>
	</div>
</div>