<?php

/**
 * Simulate enough of WordPress to test plugins and themes using PHPUnit, SimpleTest, or any other PHP unit testing framework.
 * @author John Bintz
 */

$wp_test_expectations = array();

/**
 * Reset the WordPress test expectations.
 */
function _reset_wp() {
  global $wp_test_expectations;
  $wp_test_expectations = array(
    'options' => array(),
    'categories' => array(),
    'post_categories' => array(),
    'get_posts' => array(),
    'pages' => array(),
    'actions' => array(),
    'filters' => array(),
    'posts' => array(),
    'post_meta' => array(),
    'themes' => array(),
    'plugin_domains' => array(),
    'enqueued' => array(),
    'all_tags' => array(),
    'sidebar_widgets' => array(),
    'widget_controls' => array(),
    'nonce' => array(),
    'wp_widgets' => array(),
    'current' => array(
      'is_feed' => false
    ),
    'plugin_data' => array(),
    'theme' => array(
      'posts' => array()    
    ),
    'bloginfo' => array(),
    'user_capabilities' => array(),
    'children' => array()
  );
}

/*** WordPress Test Doubles ***/

/** Options **/

/**
 * Get an option from the WP Options table.
 * @param string $key The option to retrieve.
 * @return string|boolean The value of the option, or false if the key doesn't exist.
 */
function get_option($key) {
  global $wp_test_expectations;
  if (is_string($key)) {
    if (isset($wp_test_expectations['options'][$key])) {
      return maybe_unserialize($wp_test_expectations['options'][$key]);
    } else {
      return false;
    }
  } else {
    return false; 
  }
}

/**
 * Store an option in the WP Options table.
 * @param string $key The option to store.
 * @param string $value The value to store.
 * @return boolean True if the option was updated, false otherwise.
 */
function update_option($key, $value) {
  global $wp_test_expectations;
  $value = maybe_serialize($value);
  if (is_string($key)) {
    if (!isset($wp_test_expectations['options'][$key])) {
      $wp_test_expectations['options'][$key] = $value;
      return true;
    } else {
      if ($wp_test_expectations['options'][$key] == $value) {
        return false;
      } else {
        $wp_test_expectations['options'][$key] = $value;
        return true;
      }
    }
  } else {
    return false; 
  }
}
                                    
/**
 * Delete an option from the WP Options table.
 * @param string $key The option to delete.
 * @return boolean True if the option was deleted.
 */
function delete_option($key) {
  global $wp_test_expectations;
  if (is_string($key)) {
    if (isset($wp_test_expectations['options'][$key])) {
      unset($wp_test_expectations['options'][$key]);
      return true;
    } else {
      return false;
    }
  } else {
    return false; 
  }
}
 
/** String Utility Functions **/

/**
 * Remove a trailing slash from a string if it exists.
 * @param string $string The string to check for trailing slashes.
 * @return string The string with a trailing slash removed, if necessary.
 */
function untrailingslashit($string) {
  return preg_replace('#/$#', '', $string);
}

/**
 * Get GMT string from date string.
 * Currently does nothing.
 * @param string $date_string The date string to convert.
 * @return string The converted date string in GMT.
 */
function get_gmt_from_date($date_string) {
  return $date_string;
}

/**
 * Return a string that's been internationalized.
 * @param string $string The string to check for i18n.
 * @param string $namespace The namespace to check.
 * @return string The i18n string.
 */
function __($string, $namespace = 'default') {
  return $string;
}

/**
 * Echo an internationalized string.
 * @param string $string The string to check for i18n.
 * @param string $namespace The namespace to check.
 */ 
function _e($string, $namespace = 'default') {
  echo __($string, $namespace);
}

/**
 * Return a different string if the number of items is 1.
 * @deprecated Is now _n in WordPress 2.8.
 * @param string $single The string to return if only one item.
 * @param string $plural The string to return if not one item.
 * @param string $number The number of items.
 * @param string $domain The text domain.
 * @return string The correct string.
 */
function __ngettext($single, $plural, $number, $domain) {
  return _n($single, $plural, $number, $domain);
}

/**
 * Return a different string if the number of items is 1.
 * @param $single The string to return if only one item.
 * @param $plural The string to return if not one item.
 * @param $number The number of items.
 * @param $domain The text domain.
 * @return string The correct string.
 */
function _n($single, $plural, $number, $domain) {
  return ($number == 1) ? $single : $plural;  
}

/**
 * True if the data provided was created by serialize()
 * @param mixed $data The data to check.
 * @return boolean True if the data was created by serialize.
 */
function is_serialized($data) {
  return (@unserialize($data) !== false);
}

/**
 * Try to serialize the data and return the serialized string.
 * @param mixed $data The data to try to serialize.
 * @return mixed The data, possibly serialized.
 */
function maybe_serialize($data) {
  if (is_array($data) || is_object($data) || is_serialized($data)) {
    return serialize($data);
  } else {
    return $data;
  }
}

/**
 * Try to unserialize the data and return the serialized data.
 * @param mixed $data The data to try to unserialize.
 * @return mixed The data, possibly unserialized.
 */
function maybe_unserialize($data) {
  if (is_serialized($data)) {
    if (($gm = unserialize($data)) !== false) { return $gm; }
  }
  return $data; 
}

/** Categories **/

/**
 * Add a category.
 * @param int $id The category ID.
 * @param object $object The category object.
 * @throws Error if $id is not numeric or $category is not an object.
 */
function add_category($id, $object) {
  global $wp_test_expectations;
  if (is_object($object)) {
    if (is_numeric($id)) {
      $object->cat_ID = $object->term_id = (int)$id;
      $wp_test_expectations['categories'][$id] = $object;
    } else {
      trigger_error("ID must be numeric");
    }
  } else {
    trigger_error("Category provided must be an object"); 
  }
}

/**
 * Get a category.
 * @param int $id The category ID to retrieve.
 * @return object|WP_Error The category object, or a WP_Error object on failure.
 */
function get_category($id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['categories'])) {
    return new WP_Error();
  } else {
    return $wp_test_expectations['categories'][$id];
  }
}

/**
 * Get all category IDs.
 * @return array All valid category IDs.
 */
function get_all_category_ids() {
  global $wp_test_expectations;
  return array_keys($wp_test_expectations['categories']);
}

/**
 * Get a category's name.
 * @param int $id The id of the category.
 * @return string|null The name, or null if the category is not found.
 */
function get_cat_name($id) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['categories'][$id])) {  
    return $wp_test_expectations['categories'][$id]->name;
  } else {
    return null; 
  }
}
   
/**
 * Set a post's categories.
 * @param int $post_id The post to modify.
 * @param array $categories The categories to set for this post.
 */
function wp_set_post_categories($post_id, $categories) {
  global $wp_test_expectations;
  if (!is_array($categories)) { $categories = array($categories); }
  $wp_test_expectations['post_categories'][$post_id] = $categories;
}

/**
 * Get a post's categories.
 * @param int $post_id The post to query.
 * @return array The categories for this post.
 */
function wp_get_post_categories($post_id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_categories'][$post_id])) {
    return array();
  } else {
    return $wp_test_expectations['post_categories'][$post_id];
  }
}

/**
 * Get the permalink to a category.
 * For MockPress's purposes, the link will look like "/category/${category_id}"
 * @param int $category_id The category ID.
 * @return string|WP_Error The URI or a WP_Error object upon failure.
 */
function get_category_link($category_id) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['categories'][$category_id])) {
    return "/category/${category_id}";
  } else {
    return new WP_Error();
  }
}

/** Tags **/

/**
 * Get a post's tags.
 * @param int $post_id The post to query.
 * @return array The tags for the post.
 */
function wp_get_post_tags($post_id) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_tags'][$post_id])) {
    return array();
  } else {
    return $wp_test_expectations['post_tags'][$post_id];
  }  
}

/**
 * Set a post's tags.
 * @param int $post_id The post to modify.
 * @param array $tags The tags to set for this post.
 */
function wp_set_post_tags($post_id, $tags) {
  global $wp_test_expectations;
  if (!is_array($tags)) { $categories = array($tags); }
  $wp_test_expectations['post_tags'][$post_id] = $tags;
}

/**
 * Set the output for get_tags()
 * @param array $tags The output for get_tags()
 */
function _set_all_tags($tags) {
  global $wp_test_expectations;
  $wp_test_expectations['all_tags'] = $tags;
}

/**
 * Get all tags within WordPress.
 * @return array All the tags within WordPress.
 */
function get_tags() {
  global $wp_test_expectations;
  return $wp_test_expectations['all_tags'];
}

/** Posts **/

/**
 * Set the respone for get_posts()
 * @param $query The query to expect.
 * @param $result The posts to return for this query.
 */
function _set_up_get_posts_response($query, $result) {
  global $wp_test_expectations;
  $wp_test_expectations['get_posts'][$query] = $result;
}

/**
 * Retrieve posts from the WordPress database.
 * @param string $query The query to use against the database.
 * @return array The posts that match the query.
 */
function get_posts($query) {
  global $wp_test_expectations;
  
  if (isset($wp_test_expectations['get_posts'][$query])) {
    return $wp_test_expectations['get_posts'][$query];
  } else {
    return array();
  }
}

/**
 * Insert a post into the database.
 * @param array $post The post information.
 * @return int The post ID.
 */
function wp_insert_post($array) {
  global $wp_test_expectations;

  $array = (array)$array;
  if (isset($array['ID'])) {
    $id = $array['ID'];
  } else {
    if (count($wp_test_expectations['posts']) == 0) {
      $id = 1;
    } else {
      $id = max(array_keys($wp_test_expectations['posts'])) + 1;
    }
    $array['ID'] = $id;
  }
  $wp_test_expectations['posts'][$id] = (object)$array;    
  return $id;
}

/**
 * Get a post from the database.
 * @param int $id The post to retrieve.
 * @param string $output
 * @return object|null The post or null if not found.
 */
function get_post($id, $output = "") {
  global $wp_test_expectations;
  
  if (isset($wp_test_expectations['posts'][$id])) {
    return $wp_test_expectations['posts'][$id];
  } else {
    return null; 
  }
}

function update_post_meta($post_id, $field, $value) {
  global $wp_test_expectations;
  if (!isset($wp_test_expectations['post_meta'][$post_id])) {
    $wp_test_expectations['post_meta'][$post_id] = array();
  }
  $wp_test_expectations['post_meta'][$post_id][$field] = $value;
}

function get_post_meta($post_id, $field, $single = false) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['post_meta'][$post_id])) { return ""; }
  if (!isset($wp_test_expectations['post_meta'][$post_id][$field])) { return ""; }
  
  return ($single) ? $wp_test_expectations['post_meta'][$post_id][$field] : array($wp_test_expectations['post_meta'][$post_id][$field]);
}

function post_exists($title, $content, $date) {
  global $wp_test_expectations;
  foreach ($wp_test_expectations['posts'] as $post_id => $post) {
    if (
      ($post->post_title == $title) &&
      ($post->post_date == $date)
    ) {
      return $post_id;
    }
  }
  return 0;
}

function get_permalink($post) {
  return $post->guid;
}

function _set_get_children($options, $children) {
  global $wp_test_expectations;
  $wp_test_expectations['children'][md5(serialize($options))] = $children;
}

function get_children($options) {
  global $wp_test_expectations;
  var_dump(md5(serialize($options)));
  return $wp_test_expectations['children'][md5(serialize($options))];
}

/** Core **/

function add_action($name, $callback) {
  global $wp_test_expectations;
  $wp_test_expectations['actions'][$name] = $callback;
}

function add_filter($name, $callback) {
  global $wp_test_expectations;
  $wp_test_expectations['filters'][$name] = $callback;
}

/** Admin **/

function add_options_page($page_title, $menu_title, $access_level, $file, $function = "") {
  add_submenu_page('options-general.php', $page_title, $menu_title, $access_level, $file, $function);
}

function add_menu_page($page_title, $menu_title, $access_level, $file, $function, $icon) {
  global $wp_test_expectations;
  $parent = "";
  
  $wp_test_expectations['pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function', 'icon');
  
  return "hook name";
}

function add_submenu_page($parent, $page_title, $menu_title, $access_level, $file, $function = "") {
  global $wp_test_expectations;
  
  $wp_test_expectations['pages'][] = compact('parent', 'page_title', 'menu_title', 'access_level', 'file', 'function');
  
  return "hook name";
}

function _set_user_can_richedit($can) {
  global $wp_test_expectations;
  $wp_test_expectations['user_can_richedit'] = $can;
}

function user_can_richedit() {
  global $wp_test_expectations;
  return $wp_test_expectations['user_can_richedit'];
}

function the_editor($content) {
  echo $content;
}

/** Plugin **/

function plugin_basename($file) { return $file; }

function load_plugin_textdomain($domain, $path) {
  global $wp_test_expectations;
  $wp_test_expectations['plugin_domains'][] = "${domain}-${path}";
}

function wp_enqueue_script($script) {
  global $wp_test_expectations;
  $wp_test_expectations['enqueued'][$script] = true;
}

function _did_wp_enqueue_script($script) {
  global $wp_test_expectations;
  return isset($wp_test_expectations['enqueued'][$script]);
}

/** Nonce **/

function _set_valid_nonce($name, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['nonce'][$name] = $value;
}

function _get_nonce($name) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['nonce'][$name])) {
    return $wp_test_expectations['nonce'][$name];
  } else {
    return false;
  }
}

function wp_create_nonce($name) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['nonce'][$name])) {
    $wp_test_expectations['nonce'][$name] = md5(rand());
  }
  return $wp_test_expectations['nonce'][$name];
}

function wp_verify_nonce($value, $name) {
  global $wp_test_expectations;

  if (isset($wp_test_expectations['nonce'][$name])) {
    return $wp_test_expectations['nonce'][$name] == $value;
  }
  return false;
}

function wp_nonce_field($name) {
  global $wp_test_expectations;

  if (!isset($wp_test_expectations['nonce'][$name])) {
    $wp_test_expectations['nonce'][$name] = md5(rand());
  }
  echo "<input type=\"hidden\" name=\"${name}\" value=\"" . $wp_test_expectations['nonce'][$name] . "\" />";
}

/** Theme **/

function get_theme($name) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['themes'][$name])) {
    return $wp_test_expectations['themes'][$name];
  } else {
    return null;
  }
}

function get_current_theme() {
  global $wp_test_expectations;
  return $wp_test_expectations['current_theme'];
}

function _set_current_theme($theme) {
  global $wp_test_expectations;
  $wp_test_expectations['current_theme'] = $theme;
}

/** Query **/

function _setup_query($string) {
  $_SERVER['QUERY_STRING'] = $string;
}

function add_query_arg($parameter, $value) {
  $separator = (strpos($_SERVER['QUERY_STRING'], "?") === false) ? "?" : "&";
  return $_SERVER['QUERY_STRING'] . $separator . $parameter . "=" . urlencode($value);
}

function get_search_query() {
  $parts = explode("&", preg_replace("#^.*\?#", "", $_SERVER['QUERY_STRING']));
  foreach ($parts as $part) {
    list($param, $value) = explode("=", $part);
    if ($param == "s") {
      return $value; 
    }
  }
  
  return "";
}

function the_search_query() {
  echo get_search_query(); 
}

/** Pre-2.8 Widgets **/

function wp_register_sidebar_widget($id, $name, $output_callback, $options = array()) {
  register_sidebar_widget($id, $name, $output_callback, $options);
}

function register_sidebar_widget($id, $name, $output_callback, $options = array()) {
  global $wp_test_expectations; 

  $wp_test_expectations['sidebar_widgets'][] = compact('id', 'name', 'output_callback', 'options');
}

function register_widget_control($name, $control_callback, $width = '', $height = '') {
  global $wp_test_expectations; 
  $params = array_slice(func_get_args(), 4);

  $wp_test_expectations['widget_controls'][] = compact('id', 'name', 'output_callback', 'options', 'params');
}

/** Template Tags and Theme Testing **/

function _set_theme_expectation($which, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['theme'][$which] = $value; 
}

function _set_template_directory($dir) {
  global $wp_test_expectations;
  $wp_test_expectations['theme']['template_directory'] = $dir; 
}

function is_feed() {
  global $wp_test_expectations;
  return $wp_test_expectations['current']['is_feed'];
}

function is_admin() {
  global $wp_test_expectations;
  return $wp_test_expectations['current']['is_admin'];
}

function _set_current_option($field, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['current'][$field] = $value;
}

function get_plugin_data($filepath) {
  global $wp_test_expectations;
  return $wp_test_expectations['plugin_data'][$filepath];
}

function _add_theme_post($post) {
  global $wp_test_expectations;
  $wp_test_expectations['theme']['posts'][] = $post;
}

function get_header() {
  global $wp_test_expectations;
  return $wp_test_expectations['theme']['header']; 
}

function get_sidebar() {
  global $wp_test_expectations;
  return $wp_test_expectations['theme']['sidebar']; 
}

function get_footer() {
  global $wp_test_expectations;
  return $wp_test_expectations['theme']['footer']; 
}

function have_posts() {
  global $wp_test_expectations;
  return is_array($wp_test_expectations['theme']['posts']) && !empty($wp_test_expectations['theme']['posts']);
}

function the_post() {
  global $wp_test_expectations, $post;
  if (is_array($wp_test_expectations['theme']['posts']) && !empty($wp_test_expectations['theme']['posts'])) {
    $post = array_shift($wp_test_expectations['theme']['posts']);
  }
}

function the_ID() {
  global $post;
  echo $post->ID; 
}

function the_permalink() {
  global $post;
  echo $post->guid; 
}

function the_title() {
  global $post;
  echo $post->post_title; 
}

function the_title_attribute() {
  global $post;
  echo htmlentities($post->post_title);
}

function the_time($format) {
  global $post;
  echo date($format, $post->post_date);
}

function the_author() {
  global $post;
  echo $post->post_author;
}

/**
 * Print the content of the post.
 * @param string $more_link_text If the content is multi-page, the text for the next page link.
 */
function the_content($more_link_text = "") {
  global $post;
  echo $post->post_content;
  
  if (strpos($post->post_content, "<!--more") !== false) {
    echo $more_link_text;
  }
}

/**
 * Print the tags for the post.
 * @param string $start The prefix to the tag listing.
 * @param string $separator The string between each tag.
 * @param string $finish The suffix to the tag listing.
 */
function the_tags($start, $separator, $finish) {
  global $post;
  
  $tag_output = array();
  foreach (wp_get_post_tags($post->ID) as $tag) {
    $tag_output = '<a href="' . $tag->slug . '">' . $tag->name . '</a>';
  }
  
  echo $start . implode($separator, $tag_output) . $finish;
}


/**
 * Print the categories for the post.
 * @param string $separator The string between each category.
 */
function the_category($separator) {
  global $post;
  
  $category_output = array();
  foreach (wp_get_post_tags($post->ID) as $category) {
    $category_output = '<a href="' . $category->slug . '">' . $category->name . '</a>';
  }
  
  echo implode($separator, $category_output);
}

/**
 * If there are more posts, print a link that links to the subsequent posts.
 * @param string $link_test The text for the link.
 */
function next_posts_link($link_text) {
  global $wp_test_expectations;
  if ($wp_test_expectations['theme']['has_next_posts']) {
    echo '<a href="#mockpress:next">' . $link_text . '</a>';
  }
}

/**
 * Get the theme's root directory.
 * @return string The template directory.
 */
function get_template_directory() {
  global $wp_test_expectations;
  return $wp_test_expectations['theme']['template_directory'];  
}

/**
 * Set a bloginfo() field.
 * @param string $field The field to set.
 * @param string $value The value that the bloginfo() call should return.
 */
function _set_bloginfo($field, $value) {
  global $wp_test_expectations;
  $wp_test_expectations['bloginfo'][$field] = $value;  
}

/**
 * Echo a bloginfo value.
 * @param string $field The field to return.
 */
function bloginfo($field) {
  echo get_bloginfo($field, 'display');
}

/**
 * Get a bloginfo value.
 * @param string $field The field to return.
 * @param string $display The display method.
 * @return string The bloginfo field value.
 */
function get_bloginfo($field, $display) {
  global $wp_test_expectations;
  return $wp_test_expectations['bloginfo'][$field];
}

/** Media **/

/**
 * Get an &lt;img /> tag for the requested attachment.
 * @param int $id The attachment ID.
 * @param string $size The size of the image to display.
 * @param boolean $icon
 * @return The &lt;img /> tag for the attachment.
 */
function wp_get_attachment_image($id, $size = 'thumbnail', $icon = false) {
  global $wp_test_expectations;
  if (isset($wp_test_expectations['posts'][$id])) {
    return '<img src="' . $wp_test_expectations['posts'][$id]->guid . '" />';
  }
}

/** User roles **/

/**
 * Set a user capability.
 * @param string,... $capabilities The capabilities to give the current user.
 */
function _set_user_capabilities() {
  global $wp_test_expectations;
  
  $capabilities = func_get_args(); 
  foreach ($capabilities as $capability) {
    $wp_test_expectations['user_capabilities'][$capability] = true;
  }
}

/**
 * See if the current user can perform all of the requested actions.
 * @param string,... $capabilities The actions the user should be able to perform.
 * @return boolean True if the current user can perform all of the actions.
 */
function current_user_can() {
  global $wp_test_expectations;
  
  $capabilities = func_get_args(); 
  $all_valid = true;
  foreach ($capabilities as $capability) {
    if (!$wp_test_expectations['user_capabilities'][$capability]) { $all_valid = false; break; }
  }
  return $all_valid;
}

/**
 * Show the link to edit the current post.
 */
function edit_post_link() {}

/** WP_Error class **/

class WP_Error {}

/** WP_Widget class **/

class WP_Widget {
  function WP_Widget($id, $name, $widget_options, $control_options) {
    global $wp_test_expectations;
    $wp_test_expectations['wp_widgets'][$id] = compact('id', 'name', 'widget_options', 'widget_controls');
    $this->id = $id;
  }
  function widget($args, $instance) {}
  function update($new_instance, $old_instance) {}
  function form($instance) {}
  
  function get_field_id($field_name) { return "$id-$field_name"; }
  function get_field_name($field_name) { return "$id[$field_name]"; }
}

function is_wp_error($object) {
  return (is_a($object, "WP_Error"));
}

// For use with SimpleXML

$_xml_cache = array();

/**
 * Convert a string to XML.
 * Additional conversion of HTML entities will be performed to make the string valid XML.
 * @param string $string The string to convert.
 * @param boolean $show_exception If true, show any parsing errors.
 * @return SimpleXMLElement|boolean The SimpleXMLElement of the string, or false if not valid XML.
 */
function _to_xml($string, $show_exception = false) {
  global $_xml_cache;
  
  $key = md5($string);
  if (!isset($_xml_cache[$key])) {
    try {
      $_xml_cache[$key] = new SimpleXMLElement("<x>" . str_replace(
                                                         array("&mdash;", "&nbsp;"),
                                                         array("--", " "),
                                                         $string
                                                       ) . "</x>");
    } catch (Exception $e) {
      if ($show_exception) {
        echo $e->getMessage() . "\n\n";
        
        $lines = explode("\n", $string);
        for ($i = 0, $il = count($lines); $i < $il; ++$i) {
          echo str_pad(($i + 1), strlen($il), " ", STR_PAD_LEFT) . "# " . $lines[$i] . "\n";
        }
        echo "\n";
      }
      $_xml_cache[$key] = false;
    }    
  }
  return $_xml_cache[$key];
}

/**
 * Test a SimpleXMLElement node for the provided XPath.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @param mixed $value Either a string that the XPath's value should match, true if the node simply needs to exist, or false if the node shouldn't exist.
 * @return boolen True if the XPath matches.
 */
function _xpath_test($xml, $xpath, $value) {
  if ($value === true) { $value = "~*exists*~"; }
  if ($value === false) { $value = "~*not exists*~"; }
  switch ($value) {
    case "~*exists*~":
      return _node_exists($xml, $xpath);
      break; 
    case "~*not exists*~":
      return !(_node_exists($xml, $xpath));
      break; 
    default:
      return _get_node_value($xml, $xpath) == $value;
  }
  return false;
}

/**
 * Return true if the node referred to by the provided XPath.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @return boolean True if the node exists.
 */
function _node_exists($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return count($xml->xpath($xpath)) > 0;
  } else {
    return false;
  }
}

/**
 * Get the value of a node.
 * @param SimpleXMLElement $xml The node to check.
 * @param string $xpath The XPath to search for.
 * @return string|boolean The value of the node, or false if the node does not exist.
 */
function _get_node_value($xml, $xpath) {
  $result = $xml->xpath($xpath);
  if (is_array($result)) {
    return (count($result) > 0) ? trim((string)reset($result)) : null;
  } else {
    return false;
  }
}

/**
 * Wrap an XML string in an additional node.
 * @param string $string The XML string.
 * @return SimpleXMLElement An XML node.
 */
function _wrap_xml($string) {
  return new SimpleXMLElement("<x>" . $string . "</x>");
}

?>
