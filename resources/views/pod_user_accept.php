<!DOCTYPE html>
<html>
  <head>
    <title>Accept Invite Request</title>
  </head>
  <body>
    <?php if(isset($status) && $status) : ?>
		<h1>You accept this request!</h1>
	<?php else : ?>
		<h1><?php echo $error; ?></h1>
	<?php endif; ?>
  </body>
</html>