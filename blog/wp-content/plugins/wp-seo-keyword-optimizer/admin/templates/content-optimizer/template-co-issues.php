<?php
if (!defined('ABSPATH'))
    exit;

$op_report = isset($template_args['op_report']) ? $template_args['op_report'] : false;
?>
<div class="wsko-co-onpage-issues-wrapper">
    <?php
    $issues = array('error' => array(), 'warning' => array(), 'success' => array());
    if ($op_report)
    {
        /*$html_error_str = "";
        if ($op_report['issues']['html_error_infos'])
        {
            foreach ($op_report['issues']['html_error_infos'] as $err)
            {
                $html_error_str .= ($err->message .' (Line: '.$err->line.', Column '.$err->column.'), ');
            }
        }
        $html_error_str = substr($html_error_str, 0, 300);
        switch ($op_report['issues']['html_errors'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => 'Your Content has HTML errors! '/*.($html_error_str?WSKO_Class_Template::render_infoTooltip($html_error_str, 'info', true):'')* /, 'prio' => '1', 'group' => 'Content Length'); break;
            //case 1: $issues['success'][] = array('type' => 'success', 'msg' => 'No HTML errors!', 'prio' => '1', 'group' => 'Content Length'); break;
        }*/
        
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Meta Title Issues', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        if (!$op_report['meta_title_custom'])
        {
            $issues['warning'][] = array('type' => 'warning', 'msg' => __('No custom Meta Title set', 'wsko'), 'prio' => '3', 'group' => 'Title');
        }
        switch ($op_report['issues']['title_length'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('Meta Title is not set', 'wsko'), 'prio' => '1', 'group' => 'Title'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => sprintf(__('Meta Title Length (%d) is good!', 'wsko'), $op_report['title_length']), 'prio' => '1', 'group' => 'Title'); break;
            case 2: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Meta Title Length (%d) is too low', 'wsko'), $op_report['title_length']), 'prio' => '1', 'group' => 'Title'); break;
            case 3: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Meta Title Length (%d) is too high', 'wsko'), $op_report['title_length']), 'prio' => '1', 'group' => 'Title'); break;
        }
        
        if ($op_report['issues']['title_length'] != 0) {
            switch ($op_report['issues']['title_prio1'])
            {
                case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('Meta Title has no Prio 1 Keyword', 'wsko'), 'prio' => '1', 'group' => 'Title'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('Meta Title has a Prio 1 Keyword', 'wsko'), 'prio' => '1', 'group' => 'Title'); break;
            }
        }
        
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Meta Description Issues', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        switch ($op_report['issues']['desc_length'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('Meta Description is not set!', 'wsko'), 'prio' => '1', 'group' => 'Description'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => sprintf(__('Meta Description Length (%d) is good!', 'wsko'), $op_report['desc_length']), 'prio' => '1', 'group' => 'Description'); break;
            case 2: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Meta Description Length (%d) is too low', 'wsko'), $op_report['desc_length']), 'prio' => '1', 'group' => 'Description'); break;
            case 3: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Meta Description Length (%d) is too high', 'wsko'), $op_report['desc_length']), 'prio' => '1', 'group' => 'Description'); break;
        }
        
        if ($op_report['issues']['desc_length'] != 0) {
            switch ($op_report['issues']['desc_prio1'])
            {
                case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('Meta Description has no Prio 1 Keyword!', 'wsko'), 'prio' => '1', 'group' => 'Description'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('Meta Description has a Prio 1 Keyword!', 'wsko'), 'prio' => '1', 'group' => 'Description'); break;
            }
        }	
        
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Content Issues', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        switch ($op_report['issues']['word_count'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('There is no Content!', 'wsko'), 'prio' => '1', 'group' => 'Content Length'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => sprintf(__('Content length (%d) is good!', 'wsko'), $op_report['word_count']), 'prio' => '1', 'group' => 'Content Length'); break;
            case 2: $issues['error'][] = array('type' => 'error', 'msg' => sprintf(__('Content length (%d) is under 50', 'wsko'), $op_report['word_count']), 'prio' => '1', 'group' => 'Content Length'); break;
            case 3: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Content length (%d) is under 100', 'wsko'), $op_report['word_count']), 'prio' => '1', 'group' => 'Content Length'); break;
            case 4: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Content length (%d) is under 200', 'wsko'), $op_report['word_count']), 'prio' => '1', 'group' => 'Content Length'); break;
            case 5: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('Content length (%d) is under 400', 'wsko'), $op_report['word_count']), 'prio' => '1', 'group' => 'Content Length'); break;
        }
        switch ($op_report['issues']['heading_h1_count'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('H1 not set', 'wsko'), 'prio' => '1', 'group' => 'Headings'); break;
            //case 1: $issues['success'][] = array('type' => 'success', 'msg' => 'H1 found!'); break;
            case 2: $issues['error'][] = array('type' => 'error', 'msg' => sprintf(__('There is more than one H1 used! (%d)!', 'wsko'), $op_report['tags']['h1']), 'prio' => '1', 'group' => 'Headings', 'prio' => '1', 'group' => 'Headings'); break;
        }
        if ($op_report['issues']['heading_h1_count'] > 0) {
            switch ($op_report['issues']['heading_h1_prio1'])
            {
                    case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('H1 has no Prio 1 Keyword!', 'wsko'), 'prio' => '1', 'group' => 'Headings'); break;
                    //case 1: $issues['success'][] = array('type' => 'success', 'msg' => 'H1 has a Prio 1 Keyword!'); break;
            }
            if ($op_report['issues']['heading_h1_prio1'] != 0) {
                switch ($op_report['issues']['heading_h1_prio1_count'])
                {
                    case 0: $issues['warning'][] = array('type' => 'warning', 'msg' => __('H1 does not have all Prio 1 Keywords!', 'wsko'), 'prio' => '1', 'group' => 'Headings'); break;
                    case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('H1 has all Prio 1 Keywords!', 'wsko'), 'prio' => '1', 'group' => 'Headings'); break;
                }
            }	
        }
        if ($op_report['word_count'] > 250 && $op_report['word_count'] < 500)
        {
            switch ($op_report['issues']['heading_h2_250'])
            {
                case 0: $issues['warning'][] = array('type' => 'warning', 'msg' => __('H2 Count too low', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('H2 for over 250 words supplied', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
            }
        }
        if ($op_report['word_count'] > 500)
        {
            switch ($op_report['issues']['heading_h2_500'])
            {
                case 0: $issues['warning'][] = array('type' => 'warning', 'msg' => __('H2 Count too low', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('H2 for over 500 words supplied', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
            }
        }
        switch ($op_report['issues']['heading_h2h3_count'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('No H2/H3 found', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('H2/H3 found', 'wsko'), 'prio' => '2', 'group' => 'Headings'); break;
        }
        
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Permalinks', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        switch ($op_report['issues']['url_length'])
        {
            case 0: $issues['warning'][] = array('type' => 'warning', 'msg' => sprintf(__('URL Length (%d) is too high', 'wsko'), $op_report['url_length']), 'prio' => '3', 'group' => 'URL'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => sprintf(__('URL Length (%d) is good', 'wsko'), $op_report['url_length']), 'prio' => '3', 'group' => 'URL'); break;
        }
        switch ($op_report['issues']['url_prio1'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('URL has no Prio 1 Keyword', 'wsko'), 'prio' => '3', 'group' => 'URL'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('URL has a Prio 1 Keyword', 'wsko'), 'prio' => '3', 'group' => 'URL'); break;
        }
        
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Images', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        switch ($op_report['issues']['media'])
        {
            case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('No Media Elements found', 'wsko'), 'prio' => '3', 'group' => 'Media'); break;
            case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('Media Elements found', 'wsko'), 'prio' => '3', 'group' => 'Media'); break;
        }
        
        if ($op_report['issues']['media'] != 0) {
            switch ($op_report['issues']['media_missing_alt'])
            {
                case 0: $issues['error'][] = array('type' => 'error', 'msg' => __('Some of your pictures are missing alt-attributes', 'wsko'), 'prio' => '3', 'group' => 'Media'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => __('All pictues have alt-tags', 'wsko'), 'prio' => '3', 'group' => 'Media'); break;
            }
        }
        
        /*
        //WSKO_Class_Template::render_panel(array('type' => 'issue', 'title' => __('Keyword Density Issues', 'wsko'), 'col' => 'col-md-12 col-sm-12 col-xs-12'));
        foreach ($op_report['issues']['keyword_density'] as $pk => $iss)
        {
            switch ($iss['type'])
            {
                case 0: $issues['error'][] = array('type' => 'error', 'msg' => 'Keyword density ('.$iss['density'].'%) of "'.$pk.'" is zero', 'prio' => '1', 'group' => 'Keywords'); break;
                case 1: $issues['success'][] = array('type' => 'success', 'msg' => 'Keyword density ('.$iss['density'].'%) of "'.$pk.'" is good!', 'prio' => '1', 'group' => 'Keywords'); break;
                case 2: $issues['warning'][] = array('type' => 'warning', 'msg' => 'Keyword density ('.$iss['density'].'%) of "'.$pk.'" is too low', 'prio' => '1', 'group' => 'Keywords'); break;
                case 3: $issues['warning'][] = array('type' => 'warning', 'msg' => 'Keyword density ('.$iss['density'].'%) of "'.$pk.'" is too high', 'prio' => '1', 'group' => 'Keywords'); break;
            }
        }
        */

        if ($issues['error'])
        {
            usort($issues['error'], function($a, $b){
                if ($a['prio'] == $b['prio'])
                    return 0;
                return $a['prio'] > $b['prio'] ? 1 : -1;
            });
            foreach($issues['error'] as $iss)
            {
                $iss['show_support'] = false;
                WSKO_Class_Template::render_notification($iss['type'], $iss);
            }
        }
        if ($issues['warning'])
        {
            usort($issues['warning'], function($a, $b){
                if ($a['prio'] == $b['prio'])
                    return 0;
                return $a['prio'] > $b['prio'] ? 1 : -1;
            });
            foreach($issues['warning'] as $iss)
            {
                $iss['show_support'] = false;
                WSKO_Class_Template::render_notification($iss['type'], $iss);
            }
        }
        if ($issues['success'])
        {
            usort($issues['success'], function($a, $b){
                if ($a['prio'] == $b['prio'])
                    return 0;
                return $a['prio'] > $b['prio'] ? 1 : -1;
            });
            foreach($issues['success'] as $iss)
            {
                $iss['show_support'] = false;
                WSKO_Class_Template::render_notification($iss['type'], $iss);
            }
        }
    }
    else
    {
        WSKO_Class_Template::render_notification('error', array('msg' => wsko_loc('notif_co', 'onpage_report_fail')));
    }
?>	
</div>