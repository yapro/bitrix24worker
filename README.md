# bitrix24worker

Simulate clicks by button Start work, Stop work, Continue work

How to use
----------------

Add to cron the next commands (example):
```
55 10 * * * /bitrix24worker/worker.php domain.bitrix24.ru start user@mail.com passWord
59 19 * * * /bitrix24worker/worker.php domain.bitrix24.ru stop user@mail.com passWord
```
If you returned from lunch, use command:
```
/bitrix24worker/worker.php domain.bitrix24.ru restart user@mail.com passWord
```

Helpful hint
----------------

If you want to consider days established by the government, you can copy files from folder "example" to root folder of the project.