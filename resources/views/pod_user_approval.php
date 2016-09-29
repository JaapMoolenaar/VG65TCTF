<!DOCTYPE html>
<html>
  <head>
    <title>Approve User</title>
  </head>
  <body>
    <?php if(isset($status) && $status) : ?>
		<h1>User has been approved!</h1>
	<?php else : ?>
		<h1><?php echo $error; ?></h1>
	<?php endif; ?>
  </body>
</html>