<?php
if (count($_POST) && isset($_POST['taskOption'])) {
    /*Change delay time*/
    $selected = $_POST['taskOption'];
    $selectOption = $_POST['taskOption'];
    owasp_time_edit($selectOption);
    /*Send email*/
    $email = $_POST['email'];
    $selectEmail = $_POST['email'];
    owasp_email_edit($selectEmail);
} else {
    /*Change delay time*/
    $selected = owasp_time();
    /*Send email*/
    $email = owasp_email();
}

$active_tab = '';
if( isset( $_GET[ 'tab' ] ) ) {
   $active_tab = $_GET[ 'tab' ];
}
?>

<h2>OWASP Settings</h2>

<?php
if (!empty($_POST)) {
?>
<div class="notice notice-success is-dismissible"> 
	<p><strong>Settings saved.</strong></p>
	<button type="button" class="notice-dismiss">
		<span class="screen-reader-text">Dismiss this notice.</span>
	</button>
</div>
<?php
}
?>

<h2 class="nav-tab-wrapper">
    <a href="?page=owasp&tab=main_options" class="nav-tab <?php echo ($active_tab == 'main_options' || !$active_tab) ? 'nav-tab-active' : ''; ?>">Main Options</a>
    <a href="?page=owasp&tab=white_list" class="nav-tab <?php echo $active_tab == 'white_list' ? 'nav-tab-active' : ''; ?>">White list IPs</a>
    <a href="?page=owasp&tab=black_list" class="nav-tab <?php echo $active_tab == 'black_list' ? 'nav-tab-active' : ''; ?>">Black list IPs</a>
</h2>

<?php
if ($active_tab == 'main_options' || !$active_tab) {
?>

<form method="post">
    <h3>Default lockout time for simultaneous login from different geographic region</h3>
    <select name="taskOption">
        <?php
           for ($i = 1; $i <= 24; $i++) {
        ?>
        <option <?php if ($selected == $i) { ?>selected<?php } ?> value="<?php echo $i?>"><?php echo $i?></option>
        <?php
           }
        ?>
    </select>
    <h3>Notification email</h3>
<!-- <p>If field empty notification send to user Email</p> -->
    <input type="email" name="email" value="<?=$email?>">
    <br>
    <!-- <input type="submit" value="save"> -->
    <?php submit_button(); ?>
</form>


<?php

}

if ($active_tab == 'white_list' || $active_tab == 'black_list') {

include_once "functions.php";
$tablename = 'owasp';
if ($active_tab == 'black_list') $tablename = 'owasp_black';
$c = isset($_GET['c']) ? $_GET['c'] : '';
if ($c == 'delete' && (int)$_GET['id'] > 0) {
  owasp_delete($tablename, (int)$_GET['id']);
}
$owasp = owasp_all($tablename);

?>
<br />
<div class="wrap">
 <a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=owasp&tab=<?php echo $active_tab?>&c=add" class="page-title-action">Add New IP</a>
</div>
<?php 
   if (count($owasp)) { 
?>
<br />
<table class="wp-list-table widefat fixed striped users">
	<thead>
	<tr>
		<th scope="col" id="title" class="manage-column column-username column-primary"><span>Title</span></th><th scope="col" id="ip" class="manage-column column-name">IP</th><th scope="col" id="action" class="manage-column column-name">Action</th></tr>
	</thead>

	<tbody id="the-list" data-wp-lists="list:user">

    <? foreach ($owasp as $ow): ?>
	<tr id="ip-<?php echo $ow['id_owasp'] ?>"><td class="username column-username has-row-actions column-primary" data-colname="Title"><strong><?php echo $ow['title'] ?></strong><button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="name column-name" data-colname="IP"><?php echo $ow['ip'] ?></td><td><span class="delete"><a class="submitdelete" href="<?php echo $_SERVER['PHP_SELF'] ?>?page=owasp&tab=<?php echo $active_tab?>&c=delete&id=<?php echo $ow['id_owasp'] ?>">Delete</a></span></td></tr>
    <? endforeach ?>
		

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-username column-primary"><span>Title</span></th><th scope="col" class="manage-column column-name">IP</th><th scope="col" class="manage-column column-name">Action</th>	</tr>
	</tfoot>

</table>
<?php
   }
}
?>