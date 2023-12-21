<?php
/**
 * Plugin Name:       WPVIP Docs HTMX
 * Description:       Adds HTMX functionality to the WPVIP Docs theme
 * Version:           0.0.1
 * Author:            Stoph
 * Text Domain:       wpvip-docs-htmx
 */

//namespace WPVIPDocsHTMX;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Always flush cache... during testing
wp_cache_flush();

function htmx_activation() {
  wp_cache_flush();
}
register_activation_hook(__FILE__, 'htmx_activation');

/**
 * Enqueue the main HTMX script
 * and optionally the preload extension script
 */
function load_htmx() {
  wp_enqueue_script(
    'htmx', 
    'https://unpkg.com/htmx.org',
    array()
  );

  if (get_option('htmx_preload_type') != '') {
    wp_enqueue_script(
      'htmx-preload', 
      'https://unpkg.com/htmx.org/dist/ext/preload.js',
      array()
    );
  }

  // Add inline script to execute JS after a swap
  $handle = 'htmx-afterSwap';
  $script = 'document.body.addEventListener("htmx:afterSwap", function(event) {
    // Code here
    console.log("htmx:afterSwap");
  });';
  wp_register_script($handle, '', [], '', true);
  wp_add_inline_script($handle, $script);
  wp_enqueue_script($handle);

}
add_action('wp_enqueue_scripts', '\\load_htmx');

/**
 * Add the htmx attributes to the allowed tags list
 */
function allow_hx_attributes($allowed_tags, $context) {
  $elements = ['a'];
  $hx_attributes = ['hx-get', 'hx-post', 'hx-trigger', 'hx-target', 'hx-swap', 'hx-confirm', 'hx-prompt', 'hx-vals', 'hx-headers', 'hx-include', 'hx-params', 'hx-push-url', 'hx-boost', 'hx-indicator', 'hx-history-elt', 'hx-scroll', 'hx-select', 'preload'];

  foreach ($elements as $element) {
    foreach ($hx_attributes as $hx_attribute) {
      $allowed_tags[$element][$hx_attribute] = true;
    }
  }
  
  return $allowed_tags;
}
add_filter('wp_kses_allowed_html', 'allow_hx_attributes', 10, 2);

/**
 * Add the htmx attributes to the nav menu links
 */
function add_hx_attributes($atts, $item, $args) {
  if (get_option('htmx_transition') == 'on') {
    $transition = "transition:true";
  } else {
    $transition = "";
  }

  $hx_target = get_option('htmx_css_selector') ?: ".a8c-docs-layout__main__content__inner";

  $atts['hx-get'] = $atts['href'] . '?partial=true';
  $atts['hx-push-url'] = $atts['href']; // Setting to 'true' includes the partial query string in the URL
  $atts['hx-target'] = $hx_target;
  $atts['hx-swap'] = "innerHTML show:body:top $transition";
  if (get_option('htmx_preload_type') != '') {
    $atts['preload'] = get_option('htmx_preload_type');
  }

  return $atts;
}
add_filter('nav_menu_link_attributes', 'add_hx_attributes', 10, 3);

/**
 * Add the hx-ext="preload" attribute to the <html> tag if the preload option is enabled.
 * Bit of a hack, but it makes me happy.
 */
function my_plugin_add_attribute($output) {
  if (get_option('htmx_preload_type') != '' ) {
    return $output . ' hx-ext="preload"';
  }
}
add_filter('language_attributes', 'my_plugin_add_attribute');

/**
 * Loads the partial template if the 'partial' query parameter is set.
 */
function load_partial_template($template) {
  if (isset($_GET['partial'])) {
    return plugin_dir_path(__FILE__) . 'templates/partial-content.php';
  }
  
  return $template;
}
add_filter('template_include', 'load_partial_template');


// ====================
// Settings section
// ====================
function htmx_settings_page() {
  add_options_page('HTMX options', 'HTMX Options', 'manage_options', 'htmx-options-url', 'htmx_settings_page_html');
}
add_action('admin_menu', 'htmx_settings_page');

function htmx_register_settings() {
  register_setting('htmx_options_group', 'htmx_transition');
  register_setting('htmx_options_group', 'htmx_css_selector');
  register_setting('htmx_options_group', 'htmx_preload_type');
}
add_action('admin_init', 'htmx_register_settings');

function htmx_settings_page_html() {
  $htmx_css_selector = get_option('htmx_css_selector') ?: ".a8c-docs-layout__main__content__inner";
  ?>
  <div class="wrap">
    <h2>HTMX Options</h2>
    <form method="post" action="options.php">
      <?php settings_fields('htmx_options_group'); ?>

      <table class="form-table">
        <tr>
          <th><label for="htmx_transition">Enable Transitions:</label></th>
          <td>
            <input type = 'checkbox' id="htmx_transition" name="htmx_transition" value="on" <?php if(get_option('htmx_transition') == 'on'){echo"checked";}?> >
          </td>
        </tr>
        <tr>
          <th><label for="htmx_css_selector">CSS Selector target:</label></th>
          <td>
            <input type = 'text' class="regular-text" id="htmx_css_selector" name="htmx_css_selector" value="<?php echo $htmx_css_selector; ?>">
          </td>
        </tr>
        <tr>
          <th><label for="htmx_preload_type">Preload Type:</label></th>
          <td>
            <select id="htmx_preload_type" name="htmx_preload_type">
              <option value="" <?php if(get_option('htmx_preload_type') == ''){echo"selected";}?> >Disabled</option>
              <option value="mousedown" <?php if(get_option('htmx_preload_type') == 'mousedown'){echo"selected";}?> >Mouse Down/Touch Start</option>
              <option value="mouseover" <?php if(get_option('htmx_preload_type') == 'mouseover'){echo"selected";}?> >Mouse Over</option>
            </select>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}

