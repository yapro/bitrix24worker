# bitrix24worker

Simulate clicks by button Start work, Stop work, Continue work

How to use
----------------

Add to curl next commands:

55 10 * * * /bitrix24worker/worker.php start user@mail.com passWord
59 19 * * * /bitrix24worker/worker.php stop user@mail.com passWord

If you returned from lunch, use command:

/bitrix24worker/worker.php restart user@mail.com passWord