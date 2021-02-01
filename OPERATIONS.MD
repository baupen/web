# Operations
useful information for operations.

## Deployment

`famoser/agnes` does the deployment heavy lifting.  
server must fulfil requirements of `composer.json` & include ghostscript (`gs`)  

see the `./.env` file for how to configure

## Backup

you only need to backup the files in the `/var/persistent` folder.
Using rsync, do something like this: 
```
rsync -azP ~/www/app.mangel.io/production/shared/var/persistent/ user@IP:~/backup/
```

## User default values
 
if a user registers & validates its email successfully, its information is pre-filled using files at `var/transient/authorization/user_data/*.json`.
for each of the found files, `givenName`, `familyName` and `phone` are prefilled if the email matches.
hence the file should look something like this:
```json
[
    {
      "email": "git@mangel.io",
      "givenName": "git",
      "familyName": "mangel.io",
      "phone": "0991234567"
    }
]
```

## Impersonation

you may impersonate another user if you an admin (must be set directly in the database) with 
```
http://example.com/somewhere?_switch_user=git@mangel.io
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
