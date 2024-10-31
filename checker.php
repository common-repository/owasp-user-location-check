<?php
/**
 * Plugin Name: OWASP User Location Check
 * Author: Off-Site Services, Inc.
 * Description: Sends warning notification to admin email if someone logs in to user’s account from another country within setup hours of last login session.
 * Author URI: http://oss-usa.com
 * Version: 1.1
 * Licence: GPLv2
 */
include_once "includes/functions.php";

//$changeTime = get_option( 'time_owasp' );

function owasp_activation()
{
    add_option('time_owasp', $value = '2', $autoload = 'no');
    add_option('email_owasp', $value = '', $autoload = 'no');
    global $wpdb;
    $table_name = $wpdb->prefix . 'owasp';
    $table_name2 = $wpdb->prefix . 'owasp_black';

    if ($wpdb->get_var("SHOW TABLES LIKE $table_name") != $table_name) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `id_owasp` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(40) NOT NULL,
            `ip` text NOT NULL,
            PRIMARY KEY (`id_owasp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $wpdb->query($sql);
    }
    if ($wpdb->get_var("SHOW TABLES LIKE $table_name2") != $table_name2) {
        $sql = "CREATE TABLE IF NOT EXISTS `$table_name2` (
            `id_owasp` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(40) NOT NULL,
            `ip` text NOT NULL,
            PRIMARY KEY (`id_owasp`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        $wpdb->query($sql);
    }
}

register_activation_hook(__FILE__, 'owasp_activation');


function owasp_deactivation()
{
}

register_deactivation_hook(__FILE__, 'owasp_deactivation');


function owasp_uninstall()
{
    delete_option( 'time_owasp' );
    delete_option( 'email_owasp' );

    global $wpdb;
    $table_name = $wpdb->prefix . 'owasp';
    $table_name2 = $wpdb->prefix . 'owasp_black';

    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    $sql = "DROP TABLE IF EXISTS $table_name2";
    $wpdb->query($sql);

}

register_uninstall_hook(__FILE__, 'owasp_uninstall');


// action function for above hook
function owasps_add_pages()
{
    // Add a new submenu under Manage:
    add_options_page('OWASP', 'OWASP', 'manage_options', 'owasp', 'owasps_manage_page');
}

// mt_manage_page() displays the page content for the Test Manage submenu
function owasps_manage_page()
{
    $c = isset($_GET['c']) ? $_GET['c'] : '';
    switch ($c) {
        case 'add':
            $field_values = array("", "");
            if (count($_POST)) $field_values = owasp_addip();
            $title = $field_values[0];
            $ip = $field_values[1];           
            $action = 'addIP';
            if ($title && $ip) $action = 'mainSettings';
            break;
        default:
            $action = 'mainSettings';
            break;
    }
    include_once("includes/$action.php");
}

// Hook for adding admin menus
add_action('admin_menu', 'owasps_add_pages');

function checkerUserChecking($user_login, $user)
{

    if (isset($user) && (int)$user->ID > 0) { // is_user_logged_in()

        function checkerGetIp()
        {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                return $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                return $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                return $ip = $_SERVER['REMOTE_ADDR'];
            }
        }

        $user_id = $user->ID; // get_current_user_id()
        $userIp = checkerGetIp();
        $details = json_decode(file_get_contents("http://ipinfo.io/{$userIp}"));
        $currentCountry = $details->country;

        $userVisitDate = date('Y-m-d H:i', time());
        $metaArray = array(
            'country' => $currentCountry,
            'date' => $userVisitDate
        );

        $errorIp = false;
        $owasp_options = get_owasp_option();

        $whiteiplist_array = $owasp_options['owasp_field_whitelist'];
        if (in_array($userIp, $whiteiplist_array)) {
          update_user_meta($user_id, 'userVisitMeta', $metaArray);
          return;
        }
        $blackiplist_array = $owasp_options['owasp_field_blacklist'];
        if (in_array($userIp, $blackiplist_array)) {
          wp_logout();
          wp_redirect(home_url());
          wp_die();
        }


        if (!$errorIp){
            if (get_user_meta($user_id, 'userVisitMeta')) {
                //user already have meta data
                $getData = get_user_meta($user_id, 'userVisitMeta');
		
                $getCountry = $getData[0]['country'];
                $getDate = $getData[0]['date'];

		$changeTime = $owasp_options['owasp_field_bantime'];
		$email = $owasp_options['owasp_field_email'];

                $presentTimeMinusHours = date('Y-m-d H:i', strtotime("- $changeTime hours"));

                if ($presentTimeMinusHours < $getDate) {
                    //successfully work
                    // last user log-in less then 2 hours ago

                    if ($getCountry == $currentCountry) {
                        //successfully work
                        //same country - just update data
                        update_user_meta($user_id, 'userVisitMeta', $metaArray);
                    } else {
                        /**
                         * MAIN IF - if going here - different countries
                         * send warning-notification to user email
                         */

                        // $recipient = get_userdata($user_id)->user_email || get_userdata($user_id)->user_email;

                        $siteName = get_bloginfo();
                        $pageTitle = "Connection warning: \"$siteName\"";
                        $mailHeaders = "Content-Type: text/plain; \n";
                        $mailHeaders .= "Connection warning: $siteName \n";
                        $adminUrl = admin_url() . 'users.php';
                        $message = "User \"".$user->user_login."\" is attempting CMS login from different location (".($currentCountry ? $currentCountry : 'country not defined').") than previous successful CMS login (".($getCountry ? $getCountry : 'country not defined').").";
                        //$message = "User with username: ".$user->user_login." is attempting to connect from a different location (".($currentCountry ? $currentCountry : 'country not defined').") than previous recent login (".($getCountry ? $getCountry : 'country not defined').").";
                        //$message = "You are attempting to connect from a different location than your previous recent login. \n";
                        //$message .= "If you did not recently login or attempt to login from a different geographic location, we strongly recommend that you change your password in $adminUrl";

                        //mail($recipient, $pageTitle, $message, $mailHeaders);
			wp_mail($email, $pageTitle, $message);
			wp_logout();
			//wp_redirect(home_url());
			//wp_die();

                    }
                } else {
                    //successfully work
                    //last log-in more than 2 hours ago - just update meta data
                    update_user_meta($user_id, 'userVisitMeta', $metaArray);
                }
            } else {
                //add user data if it's isn't exist - create metadata for user
                add_user_meta($user_id, 'userVisitMeta', $metaArray, true);
            }
        }

        ?>
        <?php


    }
}


//add_action('wp_head', 'checkerUserChecking');
//add_action('admin_head', 'checkerUserChecking');
add_action('wp_login', 'checkerUserChecking', 10, 2);
