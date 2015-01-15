<?php

/*
Template Name: Export Page
*/

/*

$post= get_post();
//print_r($post);
$fields = explode("|", preg_replace('/\r\n|\r|\n/m','|',$post->post_content));
//print_r($fields);

*/

  $csv = array();
  array_push($csv, 'post_title,address,product_range'."\n"); // <- Header Row

  $args = array( 'post_type' => 'locations', 'numberposts' => 5 );
  $locs = get_posts($args);
  //print_r($locs); // <- for testing

  foreach ($locs as $loc) {
    $title = $loc->post_title;
    $meta = get_post_meta_all($loc->ID);
    //print_r($meta);
    
    $address = $meta[address];
    $products = unserialize($meta[product_range]);

    unset($p_string);
    foreach ($products as $product) {
      $p_string .= $product.';';
    }
    $p_string = substr($p_string, 0,strlen($p_string)-1);
    
    array_push($csv, $title.','.$address.','.$p_string."\n");
  
  }

  $fp = fopen('php://output', 'w+'); 
  header('Content-type: application/octet-stream');  
  header('Content-disposition: attachment; filename="data.csv"'); 
  foreach($csv as $line){
    $val = explode(",",$line);
    fputcsv($fp, $val);
  }

fclose($output);

function get_post_meta_all($post_id){
    global $wpdb;
    $data   =   array();
    $wpdb->query("
        SELECT `meta_key`, `meta_value`
        FROM $wpdb->postmeta
        WHERE `post_id` = $post_id
    ");
    foreach($wpdb->last_result as $k => $v){
        $data[$v->meta_key] =   $v->meta_value;
    };
    return $data;
}

?>
