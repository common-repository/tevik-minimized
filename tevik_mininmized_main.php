<?php
/*
Plugin Name: Speed Site Optimization by dTevik
Description: Plugin Google Speed Optimization is minimizes the HTML output of the site, combines JS and CS files into one, and also minimizes JS and CSS. It compresses the HTML output, removing unnecessary spaces, reduces the size of the output of your site, which significantly increases the speed of your site, since the output will be less content. Also, the plugin can cut out single JS codes from content, minimizes them and adds them before the closing BODY tag after the optimized main JS file (so as not to break anything) This is the best way to compress the HTML output of your site and improve performance in Google Speed ​​Test (PageSpeed ​​Insights)!
Author: dTevik
Version: 1.0.0
Author URI: https://anira-web.ru/
*/
global $do_tevik_minify;
$do_tevik_minify = true;

add_action( 'init', 'tevik_minimized_process_post' );
function tevik_minimized_process_post() {
 ob_start();
}
add_action('shutdown', function() {
  $final = '';
  $levels = ob_get_level();
  for ($i = 0; $i < $levels; $i++) {
    $final .= ob_get_clean();
  }
  echo apply_filters('tevik_minimized_final_output', $final);
}, 0);


add_filter('tevik_minimized_final_output', function($output) {
  global $do_tevik_minify;
  if(is_admin() && isset($_GET['tevik_clear_minimized']) && $_GET['tevik_clear_minimized'] == 'true'){
   
   //clear minimized CSS and JS update 
   delete_option('tevik_minimization_css');
   delete_option('tevik_minimization_js');
   delete_option('tevik_minimization_js_code');
 }
 
 if(!is_admin() && 
  !isset($_GET['tevik_clear_minimized']) && 
  !isset($_GET['tevik_no_minimized']) && 
  $do_tevik_minify && 
  !tevik_is_exclude_pages()){
  
  if (!file_exists(WP_CONTENT_DIR . '/cache/minified/')) {
    mkdir(WP_CONTENT_DIR . '/cache/minified/', 0777, true);
  }
  $main_css_minify_abs = WP_CONTENT_DIR . '/cache/minified/css-minified.css';
  $main_js_minify_abs = WP_CONTENT_DIR . '/cache/minified/js-minified.js';
  
  $main_css_minify_url = str_replace(ABSPATH,home_url('/'),$main_css_minify_abs);
  $main_js_minify_url = str_replace(ABSPATH,home_url('/'),$main_js_minify_abs);

  if(!file_exists($main_css_minify_abs)) {
    $fh = fopen($main_css_minify_abs, 'w') or die("Can't create file");
    fclose($fh);
    chmod($main_css_minify_abs, 0666);
  }

  if(!file_exists($main_js_minify_abs)) {
    $fh = fopen($main_js_minify_abs, 'w') or die("Can't create file");
    fclose($fh);
    chmod($main_js_minify_abs, 0666);
  }

  if($_SERVER['REQUEST_METHOD'] == 'GET'){
    
   //CSS and JS minimization
   $tevik_output = $output;
   $res_css_array = array();
   $res_js_array = array();
   
   preg_match_all('/<link.*?href=["\']((?!\/\/)(?!.*google)(?!.*yandex).*?\.css.*?)["\'].*?>/', $tevik_output, $css);
   preg_match_all('/<script.*?src=["\'](.*?\.js.*?)["\'].*?><\/script>/', $tevik_output, $js);
   preg_match_all('/(<script.*?>)(.*?)<\/script>[^<]/is', $tevik_output, $js_code);
   
   //minimized CSS files
   $saved_minimization_data_css = get_option('tevik_minimization_css');
   if(empty($saved_minimization_data_css)) $saved_minimization_data_css = array();

   $is_new_css_in_page = false;
   if($css['1']) foreach($css['1'] as $k => $l){
     $l = preg_replace("/css\?.*?$/", 'css', $l);
     $l = preg_replace("/^\//", '', $l);
     $l = str_replace(home_url('/'),'',$l);
     $l = ABSPATH . $l;
     if(file_exists($l) && !array_key_exists(hash('md5',$l),$saved_minimization_data_css)){
       $the_stat = stat($l);
       $mtime = $the_stat['mtime'];
       $res_css_array[hash('md5',$l)]['src'] = $l;
       $res_css_array[hash('md5',$l)]['mtime'] = $mtime;

       if(!array_key_exists(hash('md5',$l),$saved_minimization_data_css) && !empty($saved_minimization_data_css)){
         $is_new_css_in_page = true;
         $res_css_array_new[hash('md5',$l)]['src'] = $l;
         $res_css_array_new[hash('md5',$l)]['mtime'] = $mtime;
       }
     }
   }
   if(empty($saved_minimization_data_css)){ 
     require_once(plugin_dir_path( __FILE__ ) . '/minify/vendor/autoload.php');

     $minifier = new MatthiasMullie\Minify\CSS;

     if($res_css_array) foreach($res_css_array as $css_item){
       $sourcePath = $css_item['src'];
       $minifier->add($sourcePath);
     }

     $minifiedPath = $main_css_minify_abs;
     $minifier->minify($minifiedPath);
     
     update_option('tevik_minimization_css',$res_css_array);
   }
   elseif($is_new_css_in_page) {
     $saved_minimization_data_css = array_merge($saved_minimization_data_css, $res_css_array_new);

     require_once(plugin_dir_path( __FILE__ ) . '/minify/vendor/autoload.php');

     $minifier = new MatthiasMullie\Minify\CSS;

     if($saved_minimization_data_css) foreach($saved_minimization_data_css as $css_item){
       $sourcePath = $css_item['src'];
       $minifier->add($sourcePath);
     }

     $minifiedPath = $main_css_minify_abs;
     $minifier->minify($minifiedPath);

     update_option('tevik_minimization_css',$saved_minimization_data_css);
   }

     //minimized JS files
   $saved_minimization_data_js = get_option('tevik_minimization_js');
   if(empty($saved_minimization_data_js)) $saved_minimization_data_js = array();

   $is_new_js_in_page = false;
   
   $js_row = '';
   
   foreach($js['1'] as $key => $jsct){
    if(
      preg_match("/(google)|(yandex)|^\/\//",$jsct)
    ){
      $js_row .='<script src="'.$jsct.'" type="text/javascript"></script>';
      unset($js['1'][$key]);
    }
  }
  
  if($js['1']) foreach($js['1'] as $k => $l){
    $l = preg_replace("/js\?.*?$/", 'js', $l);
    $l = preg_replace("/^\//", '', $l);
    $l = str_replace(home_url('/'),'',$l);
    $l = ABSPATH . $l;
    if(file_exists($l) && !array_key_exists(hash('md5',$l),$saved_minimization_data_js)){
     $the_stat = stat($l);
     $mtime = $the_stat['mtime'];
     $res_js_array[hash('md5',$l)]['src'] = $l;
     $res_js_array[hash('md5',$l)]['mtime'] = $mtime;

     if(!array_key_exists(hash('md5',$l),$saved_minimization_data_js) && !empty($saved_minimization_data_js)){
       $is_new_js_in_page = true;
       $res_js_array_new[hash('md5',$l)]['src'] = $l;
       $res_js_array_new[hash('md5',$l)]['mtime'] = $mtime;
     }
   }
 }

 if(empty($saved_minimization_data_js)){

   require_once(plugin_dir_path( __FILE__ ) . '/minify/vendor/autoload.php');

   $minifier = new MatthiasMullie\Minify\JS;

   if($res_js_array) foreach($res_js_array as $js_item){
     $sourcePath = $js_item['src'];
     $minifier->add($sourcePath);
   }

   $minifiedPath = $main_js_minify_abs;
   $minifier->minify($minifiedPath);
   
   update_option('tevik_minimization_js',$res_js_array);

 }
 elseif($is_new_js_in_page) {
   $saved_minimization_data_js = array_merge($saved_minimization_data_js, $res_js_array_new);

   require_once(plugin_dir_path( __FILE__ ) . '/minify/vendor/autoload.php');

   $minifier = new MatthiasMullie\Minify\JS;

   if($saved_minimization_data_js) foreach($saved_minimization_data_js as $js_item){
     $sourcePath = $js_item['src'];
     $minifier->add($sourcePath);
   }

   $minifiedPath = $main_js_minify_abs;
   $minifier->minify($minifiedPath);

   update_option('tevik_minimization_js',$saved_minimization_data_js);
 }

     //add time label in JS and CSS for browser cesh update
 $the_stat_css = stat($main_css_minify_abs);
 $the_stat_js = stat($main_js_minify_abs);
 $mtime_css = $the_stat_css['mtime'];
 $mtime_js = $the_stat_js['mtime'];

 $css_row = '<link rel="stylesheet" type="text/css" href="'.$main_css_minify_url.'?ver='.$mtime_css.'"/>';

 $js_row .='<script src="'.$main_js_minify_url.'?ver='.$mtime_js.'" type="text/javascript"></script>';
 
 $js_code_row = '';
 if(!empty($js_code['2'])){
  
  //find elementor config
  $the_elmentor_config = '';
  foreach($js_code['2'] as $key => $jsct){
    if(preg_match("/elementorFrontendConfig/",$jsct)){
      $the_elmentor_config = '<script>'.$js_code['2'][$key].'</script>';
      unset($js_code['2'][$key]);
    }  
  }

  require_once(plugin_dir_path( __FILE__ ) . '/minify/vendor/autoload.php');
  $minifier_code = new MatthiasMullie\Minify\JS;
  $js_code_row = implode("\n",$js_code['2']);
  $js_code_row = str_replace(array('<!--','-->'),'',$js_code_row);
  $minifier_code->add($js_code_row);
  $js_code_row = $minifier_code->minify();
  $js_code_row = '<script type="text/javascript">'.$js_code_row.'</script>';
}

  //remove all native JS and CSS
$tevik_output = preg_replace('/<link.*?href=["\']((?!\/\/)(?!.*google)(?!.*yandex).*?\.css.*?)["\'].*?>\n?/', '', $tevik_output);
$tevik_output = preg_replace('/<script.*?\/script>/s', '', $tevik_output);

  //make HTML in one line
$tevik_output = preg_replace("/(\n)+/u", "\n", $tevik_output);
$tevik_output = preg_replace("/\r\n+/u", "\n", $tevik_output);
$tevik_output = preg_replace("/\n(\t)+/u", "\n", $tevik_output);
$tevik_output = preg_replace("/\n(\ )+/u", "\n", $tevik_output);
$tevik_output = preg_replace("/\>(\n)+</u", '><', $tevik_output);
$tevik_output = preg_replace("/\>\r\n</u", '><', $tevik_output);
$tevik_output = preg_replace("/\s+/u",' ', $tevik_output);
$tevik_output = preg_replace("/\n/u", '', $tevik_output);

 //add CSS to HEADER
$tevik_output = preg_replace(
 "/(<head>)/", 
 "$1$css_row", 
 $tevik_output
);

 //add JS before </body>
$tevik_output = preg_replace("/(<\/body>)/", "$the_elmentor_config$js_row$js_code_row$1", $tevik_output);
$output = $tevik_output;
}
}
return $output;
});

function tevik_clear_minimized_button(){
  
  if ( ! is_user_logged_in() || ! is_admin_bar_showing() ) {
    return false;
  }

  // User verification
  if ( ! is_admin() ) {
    return false;
  }
// Button parameters
  $tevik_clear_minimized_url = add_query_arg( array( 'tevik_clear_minimized' => 'true' ) );
  $tevik_clear_minimized_url_nonced = wp_nonce_url( $tevik_clear_minimized_url, 'tevik_clear_minimized' );

  // Admin button only on main site in MS edition or admin bar if normal edition
  if ( ( is_multisite() && is_super_admin() && is_main_site() ) || ! is_multisite() ) {
    global $wp_admin_bar;
    $wp_admin_bar->add_menu( array(
      'parent' => '',
      'id' => 'tevik_clear_minimized_button',
      'title' => 'Clear Minify CSS & JS',
      'meta' => array( 'title' => 'Clear Minify CSS & JS' ),
      'href' => $tevik_clear_minimized_url_nonced
    )
  );
  }
}
if($do_tevik_minify)
  add_action( 'admin_bar_menu', 'tevik_clear_minimized_button', 101 );


function tevik_is_exclude_pages() {
  $exclude_array = array('wp\-login\.php', 'wp\-register\.php');
  foreach($exclude_array as $exc){
    if(preg_match("/$exc/",$_SERVER['REQUEST_URI']))
      return true;
    break;
  }
}
?>