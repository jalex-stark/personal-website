<?php
if (!defined('ABSPATH')) exit;

$term_key = isset($template_args['term_key']) ? $template_args['term_key'] : false;
if ($term_key)
{
    $sitemap_excluded = false;
    $gen_params = WSKO_Class_Onpage::get_sitemap_params();
    if (isset($gen_params['excluded_terms']) && is_array($gen_params['excluded_terms']) && ($key=array_search($term_key, $gen_params['excluded_terms'])) !== false)
    {
        $sitemap_excluded = true;
    }
    ?><tr class="form-field">
        <td>
            <div class="wsko-term-meta-widget wsko_wrapper">
                <ul class="wsko-nav bsu-tabs bsu-tabs-sm">
                    <li><a class="wsko-nav-link wsko-nav-link-active" href="#wsko_tax_metas_general"><?=__('Metas', 'wsko')?></a></li>
                    <li><a class="wsko-nav-link" href="#wsko_tax_metas_social"><?=__('Social', 'wsko')?></a></li>
                    <li><a class="wsko-nav-link" href="#wsko_tax_metas_advanced"><?=__('Advanced<', 'wsko')?>/a></li>
                </ul>
                <div class="wsko-tab-content">
                    <div id="wsko_tax_metas_general" class="wsko-tab wsko-tab-active">
                        <?php WSKO_Class_Template::render_template('tools/template-metas-view.php', array('post_term' => $term_key, 'type' => 'post_term', 'meta_view' => 'metas')); ?>
                    </div>
                    <div id="wsko_tax_metas_social" class="wsko-tab">
                        <?php WSKO_Class_Template::render_template('tools/template-metas-view.php', array('post_term' => $term_key, 'type' => 'post_term', 'meta_view' => 'social')); ?>
                    </div>
                    <div id="wsko_tax_metas_advanced" class="wsko-tab">
                        <div class="panel panel-default bsu-panel">
                            <div class="panel-heading"><?=__('Canonical Settings', 'wsko')?></div>
                            <div class="panel-body">   
                                <tr class="form-field">
                                    <td>
                                        <?php WSKO_Class_Template::render_template('tools/template-metas-view.php', array('post_term' => $term_key, 'type' => 'post_term', 'meta_view' => 'canonical')); ?>
                                    </td>
                                </tr>
                            </div>    
                        </div>    
                        <div class="panel panel-default bsu-panel">
                            <div class="panel-heading"><?=__('Sitemap Options', 'wsko')?></div>
                            <div class="panel-body">
                                <tr class="form-field">
                                    <td>
                                        <label><input class="form-control wsko-ajax-input" type="checkbox" <?=$sitemap_excluded ? 'checked="checked"' : ''?> data-wsko-target="onpage_sitemap_term_exclude" data-wsko-setting="<?=$term_key?>"> <?=__('Exclude from Sitemap', 'wsko')?></label>
                                    </td>
                                </tr>
                            </div>    
                        <div>    
                    </div>
                </div>
            </div>
        </td>
    </tr>
<?php
}