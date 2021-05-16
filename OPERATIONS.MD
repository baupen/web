# Operations
useful information for operations.

## Deployment

`famoser/agnes` does the deployment heavy lifting.  
server must fulfil requirements of `composer.json` & include ghostscript (`gs`)  

see the `./.env` file for how to configure

to deploy master to all instances, simply run
```
- ./vendor/bin/agnes deploy *:*:* master 
```

if you want to use a staging area, run the following commands:
```bash
- ./vendor/bin/agnes run *:*:prod backup_db
- ./vendor/bin/agnes deploy *:*:staging master
- ./vendor/bin/agnes run *:*:staging restore_db
```

## cron commands

Some commands should run regularly to optimized memory usage & security.

| command | proposed schedule |
| ------- | ----------------- |
| `php bin/console app:cache:clear --renders --reports` | sunday every week |
| `php bin/console app:authentication:refresh` | every 30 minutes |
| `php bin/console app:weekly:send` | every week (monday morning recommended) |

## Status

Apps request `/api/status` after each sync. You can place a message there which is then displayed in the app.
For details investigate in `./src/Controller/ApiController.php` the method `statusAction`.

## Backup

you only need to backup the files in the `/var/persistent` folder.
Using rsync, do something like this: 
```
rsync -azP ~/www/app.baupen.ch/production/shared/var/persistent/ user@IP:~/backup/
```

## User default values
 
if a user registers & validates its email successfully, its information is pre-filled using files at `var/transient/authorization/user_data/*.json`.
for each of the found files, `givenName`, `familyName` and `phone` are prefilled if the email matches.
hence the file should look something like this:
```json
[
    {
      "email": "git@baupen.ch",
      "givenName": "git",
      "familyName": "baupen.ch",
      "phone": "0991234567"
    }
]
```

## Impersonation

you may impersonate another user if you an admin (must be set directly in the database) with 
```
http://example.com/somewhere?_switch_user=git@baupen.ch
```

finish impersonation with
```
http://example.com/somewhere?_switch_user=_exit
```

## Setup mysql

- `mysql -u root` to login as root
- `CREATE USER 'symfony'@'localhost' IDENTIFIED BY 'password';` to create `symfony` user with password `password`.
- `GRANT ALL PRIVILEGES ON *.* TO 'symfony'@'localhost';` to allow `symfony` use to create databases
- `FLUSH PRIVILEGES;` to reload privileges
- `quit` to exit 

## Setup ssh

`ssh-copy-id -i ~/.ssh/id_rsa.pub username@domain` to add ssh key  
`cat ~/.ssh/id_rsa.pub` to query the active ssh key  
`ssh-keygen -t rsa -b 4096 -C "username@domain" && eval $(ssh-agent -s) && ssh-add ~/.ssh/id_rsa` generate a new key & add it to ssh  

## Migrate

Migration is automatic at minor versions (any combination is supported; downgrades may suffer data loss).
For major versions, manual intervention is necessary.

### 1.0 -> 2.0

Steps:
- install the new application; ensure `persistent` and `transient` folders are linked as before
- run `php bin/console app:migrate:sqlite` to copy sqlite data into mysql database
- run `php bin/console app:authorization:refresh` to authorize users on whitelists
- run `php bin/console app:cache:clear --construction-sites --renders --force` to remove the old cache
- run `php bin/console app:cache:warmup` to warmup the cache
