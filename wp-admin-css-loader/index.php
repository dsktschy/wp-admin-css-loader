<?php
/*
Plugin Name: WP Admin CSS Loader
Plugin URI: https://github.com/dsktschy/wp-admin-css-loader
Description: WP Admin CSS Loader loads the CSS files for admin pages.
Version: 1.0.0
Author: dsktschy
Author URI: https://github.com/dsktschy
License: GPL2
*/

// Add fields to the setting page
add_filter('admin_init', function() {
  add_settings_field(
    WpAdminCssLoader::$fieldId,
    preg_match('/^ja/', get_option('WPLANG')) ?
      '管理画面で読み込むCSSファイルのURL' :
      'URLs of CSS files to link on admin pages',
    ['WpAdminCssLoader', 'echoField'],
    WpAdminCssLoader::$fieldPage,
    'default',
    ['id' => WpAdminCssLoader::$fieldId]
  );
  register_setting(WpAdminCssLoader::$fieldPage, WpAdminCssLoader::$fieldId);
});

// Load the CSS files for admin pages if specified
add_action('admin_enqueue_scripts', function() {
  $option = get_option(WpAdminCssLoader::$fieldId);
  if ($option === '') return;
  foreach (array_map(
    ['WpAdminCssLoader', 'encodeSpace'],
    array_map('trim', explode("\n", $option))
  ) as $i => $url) {
    if ($url === '') continue;
    wp_enqueue_style("wpacl-admin-custom-{$i}", $url);
  }
});

// Class as a namespace
class WpAdminCssLoader
{
  static public $fieldId = 'wp_admin_css_loader';
  static public $fieldPage = 'general';
  // Outputs an input element with initial value
  static public function echoField(array $args)
  {
    $id = $args['id'];
    $value = esc_html(get_option($id));
    echo "<textarea name=\"$id\" id=\"$id\" rows=\"2\" class=\"large-text code\">$value</textarea>";
  }
  // Encode spaces
  static public function encodeSpace($url)
  {
    return str_replace(' ', '%20', $url);
  }
}
