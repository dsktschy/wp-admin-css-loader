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
  foreach (array_map(
    ['WpAdminCssLoader', 'separateSrcAndDeps'],
    explode("\n", get_option(WpAdminCssLoader::$fieldId))
  ) as $i => $line) {
    $src = WpAdminCssLoader::optimizeSpaces($line['src']);
    if ($src === '') continue;
    wp_enqueue_style(
      "wpacl-admin-custom-{$i}",
      $src,
      // Remove empty string
      array_values(array_filter(array_map(
        ['WpAdminCssLoader', 'optimizeSpaces'],
        explode(',', $line['deps'])
      ), 'strlen'))
    );
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
  // Separate to a src url and a deps strings from a line of textarea
  static public function separateSrcAndDeps($line)
  {
    $posOpening = strpos($line, '[');
    $posClosing = strpos($line, ']');
    if (
      $posOpening === false || $posClosing === false ||
      ($posClosing - $posOpening) <= 0
    ) return ['src' => $line, 'deps' => ''];
    $separated = explode('[', str_replace(']', '', $line));
    return [
      'src' => $separated[0],
      'deps' => isset($separated[1]) ? $separated[1] : ''
    ];
  }
  // Trim and Encode spaces
  static public function optimizeSpaces($url)
  {
    return str_replace(' ', '%20', trim($url));
  }
}
