# Operations
useful information for operations.

## Backup

you only need to backup the files in the `/var/persistent` folder.
Using rsync, do something like this: 
```
rsync -azP ~/www/app.mangel.io/production/shared/var/persistent/ user@IP:~/backup/
```

## Authorization

see the `./.env` file on how to configure

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

