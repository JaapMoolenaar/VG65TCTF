<html>
  <body style="font-family: Arial, Helvetica, sans-serif">
    Hi <?= ($user->first_name ? $user->first_name : $user->username) ?>,
    <br/>
    <br/>
    <p>		
		<b><?= ($owner->first_name && $owner->last_name ? "$owner->first_name $owner->last_name" : $owner->username) ?></b> added you as a member in pod <b><?= $pod->name ?></b>. Just click the button below if you want to accept this request.
    </p>
    <br/>
    <br/>
    <p>
      <a href="<?= URL::to('/pods/accept'); ?>/<?= $pod->id ?>/<?= $user->id ?>/<?= $podUser->invite_token ?>" style="background-color: #204d74;color: white;padding: 10px 20px 10px 20px;">ACCEPT</a>
    </p>
    <br/>
    <br/>
    Have a great day!
    <br/>
    The Dolphin Team
  </body>
</html>