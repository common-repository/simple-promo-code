<?php
/*
Plugin Name: Simple Promo Code
Plugin URI: http://devondev.com/simple-promo-code/
Description: Allows editors to request a promo code or email before displaying a document. 
Version: 1.1
Author: Peter Wooster
Author URI: http://www.devondev.com/
*/

/*  Copyright (C) 2011 Devondev Inc.  (http://devondev.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* ===================== Simple Promo Code ===================================*/

function s_promo_form($atts) {
    extract( shortcode_atts( array('code' => '', 'document' => '', 'name' => '', 
        'id' => 'promo',
        'email' => 'no', 
        'promolabel' => '', 
        'emaillabel' => 'Please enter your email address',
        'liststyle' => 'style="list-style:none"'), $atts ) );
    if(!$code)return "Simple Promo Code: Code required for '$name' and '$document'";
    if(!$name)return "Simple Promo Code: Name required for '$code' and '$dcoument'";
    if(!$document)return "Simple Promo Code: Document required for '$code' and '$name'";
    
    
    if(!$promolabel)$promolabel = "Please enter the promo code for '$name'";
    
    $hash = sha1($code);
    $method = $_SERVER['REQUEST_METHOD'];
    $emailReq = strtolower($email) == 'yes';
    
    
        $val = $input = $message = $search = '';
        $isEditor = current_user_can('edit_pages');
        $eMessage = '';
        $q = '"';
        $err_code = $err_email = ''; 
        $ucode = $uemail = '';
        if ('POST' == $method) {
            $submit = $_POST['s_promo_submit'];
            $ucode = $_POST['promo_code'];
            $docName = $_POST['docname'];
            if(isset($_POST['email']))$uemail = $_POST['email'];
            $uhash=sha1($ucode);
                
            if($submit == 'Submit') {
                if ($uhash != $hash)$err_code = "<span class='error'>The promo code is not correct.</span>";
                if($emailReq) {
                    if(!s_promo_validEmail($uemail)) $err_email .= "<span class='error'>The email address is not valid.</span>";
                }
            }  else if($isEditor && $submit == 'Clear') {
                $phash = $_POST['hash'];
                if($hash == $phash && $name == $docName) {
                    s_promo_clear($code, $name);
                }
            }
        }
        if($isEditor){
            $stats = s_promo_stats($code, $name);
        } else {
            $stats = '';
        }
        
        
        $form = <<<QEND
<form id="$id" action="?#$id" method="POST" >
<input type="hidden" value="$hash" name="hash" id="hash" />
<input type="hidden" value="$document" name="docpath" id="docpath" />
<input type="hidden" value="$name" name="docname" id="docname" />
<input type="hidden" value="$emailReq" name="emailReq" id="emailReq" />
QEND;
        $form .= <<<QEND
        <ul $liststyle">
        <li><label for="promo_code">$promolabel</label> 
        <input type="text" value="$ucode" name="promo_code" id="promo_code" /> $err_code </li>   
QEND;
        
        if($emailReq) {
            $form .= <<<QEND
        <li><label for="email">$emaillabel</label>   
        <input type="text" value="$uemail" name="email" id="email" /> $err_email</li>
QEND;
        }
        $form .= '<li><input type="submit" value="Submit"  name="s_promo_submit" id="s_promo_submit" /></li>';
        if($isEditor) {
            $form .= <<<QEND
            <li class="info">$stats downloads for '$code' and '$name'     
            <input type="submit" value="Clear"  name="s_promo_submit" id="s_promo_submit" /></li>
QEND;
        }
        $form .= "</ul></form>";
        return $form;    
}

/**
 * process the results of a user post, possibly update db
 */
function s_promo_process_post() {
    if(isset($_POST['s_promo_submit']) && 'Submit' == $_POST['s_promo_submit'] ){
        $hash = trim($_POST['hash']);
        $promo_code = trim($_POST['promo_code']);
        $document = trim($_POST['docpath']);
        $name = trim ($_POST['docname']);
        $emailReq = trim($_POST['emailReq']);
        if($emailReq){
            $email = $_POST['email'];
        } else $email = '';
    }
    else return;
    
    $load = ($hash == sha1($promo_code)); 
    if($load && $emailReq) $load = s_promo_validEmail($email);
    
    if($load) {
        s_promo_hit($promo_code, $name, $email);
        wp_redirect($document);
        exit;
    }
}

/**
 * a simple check for a valid looking email address
 * @param type $email
 * @return type 
 */
function s_promo_validEmail($email) {
    $val = filter_var($email, FILTER_VALIDATE_EMAIL);
    return($val !== false);
}

/**
 * count a successful hit on a document
 * @global type $wpdb
 * @param type $code the promo code
 * @param type $code the document name
 * @param type $email the email address
 */
function s_promo_hit($code, $name, $email) {
    global $wpdb;
    if(current_user_can('edit_pages'))return;       

    $tn = $wpdb->prefix . 's_promo_hits';
    $ts = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO $tn (promo_code, name, created_date, email)VALUES(%s, %s, %s, %s);";
    $ssql = $wpdb->prepare($sql, array($code, $name, $ts, $email));
    
    $res = $wpdb->query($ssql);
}

/**
 * get download hits
 * @return type number of hits
 */
    function s_promo_stats($code, $name) {
        global $wpdb;
        
        $table = $wpdb->prefix. "s_promo_hits";
        $values = array($name, $code);
        $sql = "SELECT COUNT(*) FROM $table WHERE  `name`= %s AND `promo_code` = %s";
        $ssql = $wpdb->prepare($sql, $values);
        $n = $wpdb->get_var($ssql);
        return $n;
    }
    
/**
 * clear download stats for this promo code and item name
 */
    function s_promo_clear($code, $name) {
        global $wpdb;
        
        $table = $wpdb->prefix. "s_promo_hits";
        $values = array($name, $code);
        $sql = "DELETE FROM $table WHERE  `name`= %s AND `promo_code` = %s";
        $ssql = $wpdb->prepare($sql, $values);
        $n = $wpdb->query($ssql);
    }

add_shortcode( 'promo', 's_promo_form' );
add_action('init', 's_promo_process_post');


/**
 * create the database table
 * @global type $wpdb 
 */    
    function s_promo_install(){
        global $wpdb;
        
        $table = $wpdb->prefix. "s_promo_hits";
	if ($wpdb->get_var("show tables like '$table'") != $table) {
            $sql = <<<QEND
CREATE TABLE $table (
  `promo_code` VARCHAR(20),
  `name` VARCHAR(100),
  `created_date` DATETIME,
  `email` VARCHAR(100));
QEND;
       $wpdb->query($sql);
        }
    }


register_activation_hook(__FILE__,'s_promo_install');

/* =========================================================================
 * end of program, php close tag intentionally omitted
 * ========================================================================= */
