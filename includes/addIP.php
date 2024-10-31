<h2>OWASP Settings</h2>

<h3><a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=owasp">< return to main settings</a></h3>

<h2>New IP</h2>

<form method="post">
    Name *:
    <br>
    <input type="text" name="title" required value="<?=$title?>">
    <br>
    <br>
    IP *:
    <br>
    <input type="text" name="ip" required value="<?=$ip?>">
    <br>
    <?php submit_button(); ?>
</form>