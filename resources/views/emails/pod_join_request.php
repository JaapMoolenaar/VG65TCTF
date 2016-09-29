<html>
  <body style="font-family: Arial, Helvetica, sans-serif">
    Hi <?= $owner->first_name ?>,
    <br/>
    <br/>
    <p>
      <b><?= "$user->first_name $user->last_name" ?></b> from <b><?= $user->location ?></b> wants to join your POD <b><?= $pod->name ?></b>. Just click the button below if you want to approve access.
    </p>
    <br/>
    <br/>
    <p>
      <a href="<?= URL::to('/pods/approve'); ?>/<?= $pod->id ?>/<?= $user->id ?>/<?= $pod->approval_token ?>" style="background-color: #204d74;color: white;padding: 10px 20px 10px 20px;">APPROVE</a>
    </p>
    <br/>
    <br/>
    Have a great day!
    <br/>
    The Dolphin Team
  </body>
</html>