<?php
/*
Plugin Name: BB Import File
Plugin URI: http://brownbox.net.au/
Version: 0.1
Author: BrownBox
Author URI: http://brownbox.net.au/
Description: Import CSV files to and apply contents to the WordPress database. 
License: GPL2
------------------------------------------------------------------------

Copyright 2013. This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FIbbESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

*/ 


class bb_import_files{
  public function __construct(){
    if(is_admin()){
      add_action('admin_menu', array($this, 'add_plugin_page'));
      add_action('admin_init', array($this, 'page_init'));
    }
  }
  
  public function add_plugin_page(){
  // This page will be under "Settings"
  add_menu_page('bb import_files', 'Import/Export', 'administrator', 'bb-import-file', array($this, 'create_admin_page'));
  //add_options_page('Settings Admin', 'Settings', 'manage_options', 'bb-mc-setting-admin', array($this, 'create_admin_page'));
  }

  public function create_admin_page(){ ?>
  <div class="wrap">
    <h2>Select file to be imported</h2>     
      <form enctype="multipart/form-data" action="#" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
        Choose a file to upload: <input name="uploadedfile" type="file" /><br />
        <input type="submit" value="Import CSV File" />
    <?php
    if(isset($_FILES['uploadedfile']['tmp_name'])) {
      $data = csv_to_array($_FILES['uploadedfile']['tmp_name']);
      
      // show imported data
      $plural = (count($data)!=1) ? 's' : '';
      echo '<p>'.count($data).' record'.$plural.' available for processing.</p>'."\n";
      echo '<textarea rows="5" cols="100">'."\n";
      print_r($data); 
      echo '</textarea>'."\n";

      unset($errors);
      $errors = array();

      foreach ($data as $record) {
        $error = create_posts($record);
        if ( strlen($error)>0 ) array_push($errors, $error);
      }
      
      // error reporting
      $plural = (count($errors)!=1) ? 's' : '';
      echo '<p>'.count($errors).' record'.$plural.' could not be imported - Asset with the same name already exisits!</p>'."\n";
      echo '<textarea rows="5" cols="100">'."\n";
      print_r($errors); 
      echo '</textarea>'."\n";

      $done = count($data)-count($errors);
      $plural = ($done!=1) ? 's' : '';
      echo '<p style="font-weight:bold;">'.$done.' record'.$plural.' imported. <a href="/wp-admin/edit.php?post_type=asset">View Assets</a></p>'."\n"; 

    }
    ?>
  </div>
  <?php
  }

  public function page_init(){    
  register_setting('bb_import_files_option_group', 'bb_import_files_settings');
  add_settings_section('setting_section_id', 'Setting', array($this, 'print_section_info'), 'bb-mc-setting-admin');

  }
  
}

$bb_import_files = new bb_import_files();

//////////      STANDARD PROCESS     //////////
////////// CREATE TEMP FILE FROM CSV //////////

function csv_to_array($filename='', $delimiter=',') {
  if(!file_exists($filename) || !is_readable($filename)) return FALSE;
  ini_set('auto_detect_line_endings', true);

  $header = NULL;
  $data = array();
  
  if (($file = fopen($filename, 'r')) !== FALSE) {
    while (($row = fgetcsv($file, 5000, $delimiter)) !== FALSE) {

      if(!$header) {
        $header = $row; // <- create $header array

        // remove any whitespaces from $header array
        $header = implode(',',$header);
        $header = str_replace( ' ', '', $header);
        $header = explode(',',$header);
        
        // var_dump($header);

       } else {
        //$header = array_filter(array_map('trim', $header));
        //$header = str_replace( ' ', '', array_keys($header) );
        $data[] = array_combine($header, $row);
      }
    }
    fclose($file);
  }

  return $data;
}

////////// CUSTOMISATION AFTER FILE LOADED //////////
//////////       FOR SPRINGHILLFARM        //////////

function create_posts($data){

  $primary_key = $data['PhysicalAssetNumber'];
    
  // global $wpdb; 
  // $mypost = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_title = '$pname' AND post_status = 'publish' AND post_type = 'asset'");
  //print_r($mypost[0]->post_title); // <- for testing

  // test for duplicates
  // $args = array('' => , );
  // $posts = get_posts( $args ); 

  if (count($posts) > 0) {
    $error = $primary_key;
  } else {

    // [PhysicalAssetNumber] => 00002000
    // [FinancialAssetNumber] => 000010
    // [Description] => Biohazard Hood
    // [LongDescription] => Biohazard Hood
    // [ShortDescription] => Biohazard Hood
    // [Location-Stage] => Stage 0
    // [Location-Level] => Level 0
    // [Location-Room] => G25
    // [AssetType-Level1] => Equipment
    // [AssetType-Level2] => Facility
    // [AssetType-Level3] => Hood
    // [ManufacturerName] => 
    // [ManufactureDate] => 
    // [Make] => Gelman Sciences
    // [ModelNumber] => BH120
    // [SerialNumber] => 3919-91
    // [SupplierName] => Test Supplier 1
    // [SupplierReference] => 
    // [WarrantyExpiryDate] => 
    // [ConditionIndicator] => New

    // $content = serialize( $data );

    $post = array(
      'post_title'    => $primary_key,
      'post_status'   => 'publish',
      'post_type'     => 'asset',
      'post_content'  => $content
    );

  // $post_id = wp_insert_post($post);

  // Create/Update tax_location
  // Create/Update tax_asset_type
  // update Meta
    // Source as CSV
    // Asset_id as $primary Key


  }

  return $error;
}

?>
