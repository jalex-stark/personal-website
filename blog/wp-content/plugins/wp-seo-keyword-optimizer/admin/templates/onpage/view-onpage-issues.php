<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
$analysis = ( isset( $template_args['analysis']['current_report'] ) ? $template_args['analysis']['current_report'] : false );
$last_issues = ( isset( $template_args['analysis']['last_issues'] ) ? $template_args['analysis']['last_issues'] : false );
$pdf = ( isset( $template_args['pdf'] ) ? $template_args['pdf'] : false );

if ( $analysis ) {
    $kw_issues = array();
    $kw_issues_count = $analysis['issues']['keywords'][0]['sum'] + $analysis['issues']['keyword_density'][2]['sum'] + $analysis['issues']['keyword_density'][3]['sum'] + $analysis['issues']['keyword_density'][0]['sum'];
    if ( $last_issues ) {
        $kw_issues_count_last = $last_issues['keywords'][0] + $last_issues['keyword_density'][2] + $last_issues['keyword_density'][3] + $last_issues['keyword_density'][0];
    }
    if ( $analysis['issues']['keywords'][0]['sum'] ) {
        $kw_issues[] = array(
            'class' => 'error',
            'title' => __( 'Keyword is not set', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|keywords|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['keywords'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Keyword is not set', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['keywords'][0]['sum'],
        );
    }
    if ( $analysis['issues']['keyword_density'][0]['sum'] ) {
        $kw_issues[] = array(
            'class' => 'error',
            'title' => __( 'Keyword Density is zero', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|keyword_density|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['keyword_density'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Keyword Density is zero', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['keyword_density'][0]['sum'],
        );
    }
    if ( $analysis['issues']['keyword_density'][2]['sum'] ) {
        $kw_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Keyword Density is too low', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|keyword_density|2',
            WSKO_Class_Helper::format_number( $analysis['issues']['keyword_density'][2]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Keyword Density is too low', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['keyword_density'][2]['sum'],
        );
    }
    if ( $analysis['issues']['keyword_density'][3]['sum'] ) {
        $kw_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Keyword Density is too high', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|keyword_density|3',
            WSKO_Class_Helper::format_number( $analysis['issues']['keyword_density'][3]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Keyword Density is too high', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['keyword_density'][3]['sum'],
        );
    }
    $title_issues = array();
    $title_issues_count = $analysis['issues']['title_length'][2]['sum'] + $analysis['issues']['title_length'][3]['sum'] + $analysis['issues']['title_length'][0]['sum'] + $analysis['issues']['title_prio1'][0]['sum'];
    if ( $last_issues ) {
        $title_issues_count_last = $last_issues['title_length'][2] + $last_issues['title_length'][3] + $last_issues['title_length'][0] + $last_issues['title_prio1'][0];
    }
    if ( $analysis['issues']['title_length'][2]['sum'] ) {
        $title_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Title Length is too short', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|title_length|2',
            WSKO_Class_Helper::format_number( $analysis['issues']['title_length'][2]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Title Length is too short', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['title_length'][2]['sum'],
        );
    }
    if ( $analysis['issues']['title_length'][3]['sum'] ) {
        $title_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Title Length is too long', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|title_length|3',
            WSKO_Class_Helper::format_number( $analysis['issues']['title_length'][3]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Title Length is too long', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['title_length'][3]['sum'],
        );
    }
    if ( $analysis['issues']['title_length'][0]['sum'] ) {
        $title_issues[] = array(
            'class' => 'error',
            'title' => __( 'Title is not set', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|title_length|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['title_length'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Title is not set', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['title_length'][0]['sum'],
        );
    }
    if ( $analysis['issues']['title_prio1'][0]['sum'] ) {
        $title_issues[] = array(
            'class' => 'error',
            'title' => __( 'Title has no Prio 1 Keyword', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|title_prio1|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['title_prio1'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Title has no Prio 1 Keyword', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['title_prio1'][0]['sum'],
        );
    }
    $desc_issues = array();
    $desc_issues_count = $analysis['issues']['desc_length'][2]['sum'] + $analysis['issues']['desc_length'][3]['sum'] + $analysis['issues']['desc_length'][0]['sum'] + $analysis['issues']['desc_prio1'][0]['sum'];
    if ( $last_issues ) {
        $desc_issues_count_last = $last_issues['desc_length'][2] + $last_issues['desc_length'][3] + $last_issues['desc_length'][0] + $last_issues['desc_prio1'][0];
    }
    if ( $analysis['issues']['desc_length'][2]['sum'] ) {
        $desc_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Description Length is too short', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|desc_length|2',
            WSKO_Class_Helper::format_number( $analysis['issues']['desc_length'][2]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Description Length is too short', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['desc_length'][2]['sum'],
        );
    }
    if ( $analysis['issues']['desc_length'][3]['sum'] ) {
        $desc_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Description Length is too long', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|desc_length|3',
            WSKO_Class_Helper::format_number( $analysis['issues']['desc_length'][3]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Description Length is too short', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['desc_length'][3]['sum'],
        );
    }
    if ( $analysis['issues']['desc_length'][0]['sum'] ) {
        $desc_issues[] = array(
            'class' => 'error',
            'title' => __( 'Description is not set', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|desc_length|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['desc_length'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Description is not set', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['desc_length'][0]['sum'],
        );
    }
    if ( $analysis['issues']['desc_prio1'][0]['sum'] ) {
        $desc_issues[] = array(
            'class' => 'error',
            'title' => __( 'Description has no Prio 1 Keyword', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|desc_prio1|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['desc_prio1'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Description has no Prio 1 Keyword', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['desc_prio1'][0]['sum'],
        );
    }
    $content_issues = array();
    $content_issues_count = $analysis['issues']['word_count'][2]['sum'] + $analysis['issues']['word_count'][3]['sum'] + $analysis['issues']['word_count'][4]['sum'] + $analysis['issues']['word_count'][0]['sum'];
    if ( $last_issues ) {
        $content_issues_count_last = $last_issues['word_count'][2] + $last_issues['word_count'][3] + $last_issues['word_count'][4] + $last_issues['word_count'][0];
    }
    if ( $analysis['issues']['word_count'][2]['sum'] ) {
        $content_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Content Length is less than 50 Words', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|word_count|2',
            WSKO_Class_Helper::format_number( $analysis['issues']['word_count'][2]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Content Length is less than 50 Words', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['word_count'][2]['sum'],
        );
    }
    if ( $analysis['issues']['word_count'][3]['sum'] ) {
        $content_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Content Length is less than 100 Words', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|word_count|3',
            WSKO_Class_Helper::format_number( $analysis['issues']['word_count'][3]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Content Length is less than 100 Words', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['word_count'][3]['sum'],
        );
    }
    if ( $analysis['issues']['word_count'][4]['sum'] ) {
        $content_issues[] = array(
            'class' => 'warning',
            'title' => __( 'Content Length is less than 200 Words', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|word_count|4',
            WSKO_Class_Helper::format_number( $analysis['issues']['word_count'][4]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Content Length is less than 200 Words', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['word_count'][4]['sum'],
        );
    }
    if ( $analysis['issues']['word_count'][0]['sum'] ) {
        $content_issues[] = array(
            'class' => 'error',
            'title' => __( 'There is no Content!', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|word_count|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['word_count'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'There is no Content!', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['word_count'][0]['sum'],
        );
    }
    $heading_issues = array();
    $heading_issues_count = $analysis['issues']['heading_h1_count'][0]['sum'] + $analysis['issues']['heading_h1_count'][2]['sum'] + $analysis['issues']['heading_h1_prio1'][0]['sum'] + $analysis['issues']['heading_h1_prio1_count'][0]['sum'] + $analysis['issues']['heading_h2_250'][0]['sum'] + $analysis['issues']['heading_h2_500'][0]['sum'] + $analysis['issues']['heading_h2h3_count'][0]['sum'];
    if ( $last_issues ) {
        $heading_issues_count_last = $last_issues['heading_h1_count'][0] + $last_issues['heading_h1_count'][2] + $last_issues['heading_h1_prio1'][0] + $last_issues['heading_h1_prio1_count'][0] + $last_issues['heading_h2_250'][0] + $last_issues['heading_h2_500'][0] + $last_issues['heading_h2h3_count'][0];
    }
    if ( $analysis['issues']['heading_h1_count'][0]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'error',
            'title' => __( 'H1 not set', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_h1_count|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h1_count'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Has no H1', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h1_count'][0]['sum'],
        );
    }
    if ( $analysis['issues']['heading_h1_count'][2]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'error',
            'title' => __( 'There is more than one H1 used!', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_h1_count|2',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h1_count'][2]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Has too many H1', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h1_count'][2]['sum'],
        );
    }
    if ( $analysis['issues']['heading_h1_prio1'][0]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'error',
            'title' => __( 'H1 has no Prio 1 Keyword', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_h1_prio1|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h1_prio1'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'H1 has no Prio1', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h1_prio1'][0]['sum'],
        );
    }
    if ( $analysis['issues']['heading_h1_prio1_count'][0]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'warning',
            'title' => __( 'H1 has not all Prio 1 Keywords', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_h1_prio1_count|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h1_prio1_count'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'H1 has not all Prio1', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h1_prio1_count'][0]['sum'],
        );
    }
    if ( $analysis['issues']['heading_h2_250'][0]['sum'] || $analysis['issues']['heading_h2_500'][0]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'warning',
            'title' => __( 'H2 Count too low', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_count|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h2_250'][0]['sum'] + $analysis['issues']['heading_h2_500'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'H2 Count too low', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h2_250'][0]['sum'] + $analysis['issues']['heading_h2_500'][0]['sum'],
        );
    }
    if ( $analysis['issues']['heading_h2h3_count'][0]['sum'] ) {
        $heading_issues[] = array(
            'class' => 'error',
            'title' => __( 'No H2/H3 found', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|heading_h2h3_count|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['heading_h2h3_count'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'No H2/H3 found', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['heading_h2h3_count'][0]['sum'],
        );
    }
    $url_issues = array();
    $url_issues_count = $analysis['issues']['url_length'][0]['sum'] + $analysis['issues']['url_prio1'][0]['sum'];
    if ( $last_issues ) {
        $url_issues_count_last = $last_issues['url_length'][0] + $last_issues['url_prio1'][0];
    }
    if ( $analysis['issues']['url_length'][0]['sum'] ) {
        $url_issues[] = array(
            'class' => 'warning',
            'title' => __( 'URL length too long', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|url_length|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['url_length'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'URL length too long', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['url_length'][0]['sum'],
        );
    }
    if ( $analysis['issues']['url_prio1'][0]['sum'] ) {
        $url_issues[] = array(
            'class' => 'warning',
            'title' => __( 'URL has no Prio 1 Keyword', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|url_prio1|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['url_prio1'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'URL has no Prio1', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['url_prio1'][0]['sum'],
        );
    }
    $media_issues = array();
    $media_issues_count = $analysis['issues']['media'][0]['sum'] + $analysis['issues']['media_missing_alt'][0]['sum'];
    if ( $last_issues ) {
        $media_issues_count_last = $last_issues['media'][0] + $last_issues['media_missing_alt'][0];
    }
    if ( $analysis['issues']['media'][0]['sum'] ) {
        $media_issues[] = array(
            'class' => 'warning',
            'title' => __( 'No Media found!', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|media|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['media'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'No Media found!', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['media'][0]['sum'],
        );
    }
    if ( $analysis['issues']['media_missing_alt'][0]['sum'] ) {
        $media_issues[] = array(
            'class' => 'error',
            'title' => __( 'Missing alt on media elements', 'wsko' ),
            'issue' => WSKO_Class_Template::render_content_optimizer_multi_link_ajax(
            'onpage_multi_co',
            'issues|media_missing_alt|0',
            WSKO_Class_Helper::format_number( $analysis['issues']['media_missing_alt'][0]['sum'] ) . ' issues',
            array(
            'msg' => __( 'Missing alt on media elements', 'wsko' ),
        ),
            true
        ),
            'count' => $analysis['issues']['media_missing_alt'][0]['sum'],
        );
    }
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($kw_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Keyword Density:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $kw_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $kw_issues_count,
        'wsko'
    ), number_format_i18n( $kw_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $kw_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $kw_issues_count, $kw_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $kw_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($title_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Meta Titles:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $title_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $title_issues_count,
        'wsko'
    ), number_format_i18n( $title_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $title_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $title_issues_count, $title_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $title_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($desc_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Meta Descriptions:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $desc_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $desc_issues_count,
        'wsko'
    ), number_format_i18n( $desc_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $desc_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $desc_issues_count, $desc_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $desc_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($content_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Content Length:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $content_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $content_issues_count,
        'wsko'
    ), number_format_i18n( $content_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $content_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $content_issues_count, $content_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $content_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($heading_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Headings:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $heading_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $heading_issues_count,
        'wsko'
    ), number_format_i18n( $heading_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $heading_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $heading_issues_count, $heading_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $heading_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($url_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Permalinks:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $url_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $url_issues_count,
        'wsko'
    ), number_format_i18n( $url_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $url_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $url_issues_count, $url_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $url_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
    //if ($media_issues)
    WSKO_Class_Template::render_issueGroup( array(
        'title'  => '<span>' . __( 'Media:', 'wsko' ) . '</span> <span class="text-off count-issues">' . (( $media_issues_count ? sprintf( _n(
        '%s issue',
        '%s issues',
        $media_issues_count,
        'wsko'
    ), number_format_i18n( $media_issues_count ) ) . (( $last_issues ? WSKO_Class_Template::render_progress_icon(
        $media_issues_count_last,
        -WSKO_Class_Helper::get_ref_value( $media_issues_count, $media_issues_count_last ),
        array(
        'flip'     => true,
        'decimals' => 2,
    ),
        true
    ) : '' )) : __( '0 issues', 'wsko' ) )) . '</span>',
        'col'    => 'col-md-12 col-sm-12 col-xs-12',
        'parent' => 'wsko_onpage_issues',
        'pdf'    => $pdf,
        'items'  => $media_issues,
    ) );
    if ( $pdf ) {
        echo  '<div class="clearfix"></div>' ;
    }
} else {
    _e( 'No Analysis found.', 'wsko' );
}
