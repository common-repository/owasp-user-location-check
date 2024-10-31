<?php
/*WHITE LIST*/
function owasp_all($tablename)
{
    global $wpdb;
    $table = $wpdb->prefix . $tablename;
    $query = "SELECT id_owasp, title, ip FROM $table ORDER BY id_owasp DESC";
    return $wpdb->get_results($query, ARRAY_A);
}

function owasp_get($id_owasp)
{
    global $wpdb;
    $table = $wpdb->prefix . 'owasp';
    $t = "SELECT title, ip FROM $table WHERE id_owasp='%d'";
    $query = $wpdb->prepare($t, $id_owasp);
    return $wpdb->get_row($query, ARRAY_A);
}

function owasp_add($tablename, $title, $ip)
{
    global $wpdb;

    $title = trim($title);
    $ip = trim($ip);

    if ($title == '' || $ip == '') {
        print "<p>Please, fill all field</p>";
        return false;
    }
    $table = $wpdb->prefix . $tablename;
    $t = "INSERT INTO $table (title, ip) VALUES('%s', '%s')";
    $query = $wpdb->prepare($t, $title, $ip);
    $result = $wpdb->query($query);

    if ($result === false){
        die('error');
    }
    return true;
}

function owasp_delete($tablename, $id_owasp)
{
    global $wpdb;
    $table = $wpdb->prefix . $tablename;
    $t = "DELETE FROM `$table` WHERE `id_owasp`='%d'";
    $query = $wpdb->prepare($t, $id_owasp);
    return $wpdb->query($query);
}

/*TIME*/
function owasp_time()
{
    $changeTime = get_option( 'time_owasp' );
    return $changeTime;
}

function owasp_time_edit($selectOption)
{
    update_option( 'time_owasp', $selectOption, false );
    return true;
}
/*EMAIL*/
function owasp_email()
{
    $changeEmail = get_option( 'email_owasp' );
    return $changeEmail;
}

function owasp_email_edit($selectEmail)
{
    update_option( 'email_owasp', $selectEmail, false );
    return true;
}

function get_owasp_option() {
  $owasp_options = array();
  $owasp = owasp_all('owasp');
  $ow_array = array();
  foreach ($owasp as $ow) {
    array_push($ow_array, $ow['ip']);
  }
  $owasp_options['owasp_field_whitelist'] = $ow_array;
  $owasp = owasp_all('owasp_black');
  $ow_array = array();
  foreach ($owasp as $ow) {
    array_push($ow_array, $ow['ip']);
  }
  $owasp_options['owasp_field_blacklist'] = $ow_array;
  $owasp_options['owasp_field_bantime'] = owasp_time();
  $owasp_options['owasp_field_email'] = owasp_email();
  return $owasp_options;
}

function owasp_addip() {
  $tab = $_GET['tab'];
  if (!$tab) $tab = 'white_list';
  $tablename = 'owasp';
  if ($tab == 'black_list') $tablename = 'owasp_black';
  if (!empty($_POST)) {
    owasp_add($tablename, $_POST['title'], $_POST['ip']);
    return array($_POST['title'], $_POST['ip']);
  } else {
    return array('', '');
  }
}
