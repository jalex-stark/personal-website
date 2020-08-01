<?php
if (!defined('ABSPATH')) exit;

if (!WSKO_Class_Onpage::seo_plugins_disabled())
{
	WSKO_Class_Template::render_template('misc/template-seo-plugins-disable.php', array());
}
?>