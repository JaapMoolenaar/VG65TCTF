# Dolphin API
-------------



**Satsume's notes**
------------------

### I'll be adding e-mails to the endpoints with the following names:
```
Register User -> DolphinApi\Jobs\Mails\MailUserCreated
New Post -> DolphinApi\Jobs\Mails\MailPostCreated
Report Post ** Will be ingonred for now)
New Like -> DolphinApi\Jobs\Mails\MailPostLiked
New Comment -> DolphinApi\Jobs\Mails\MailPostCommentCreated
New Topic ** To be completed
New Subject ** To be completed
New Grade ** To be completed
New POD -> DolphinApi\Jobs\Mails\MailPodInviteUser
Update POD -> DolphinApi\Jobs\Mails\MailPodInviteUser
Accept POD Invite -> DolphinApi\Jobs\Mails\MailPodInviteUserAcceptance
User Joins POD -> DolphinApi\Jobs\Mails\MailPodJoinRequest
Approves POD Member -> DolphinApi\Jobs\Mails\MailPodJoinRequestApproval
Remove POD Member -> DolphinApi\Jobs\Mails\MailPodUserRemoval
```

### The "targets" of these mails are:
```
Register User -> user
New Post -> pod owner (when pod owner !== new post owner)
Report Post -> admin
New Like -> user
New Comment -> user
New Topic -> user
New Subject -> user
New Grade -> user
New POD -> pod owner
Update POD  -> users(invites)
Accept POD Invite -> pod owner
User Joins POD -> pod owner
Approves POD Member -> user
Remove POD Member -> user
```