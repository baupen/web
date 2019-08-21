# Developers
useful tips for operations.

## Setup

How your local environment needs to be prepared to start developing.

#### CentOS
if you need to prepare a CentOS server for the project, these commands may be useful
```
yum update
yum install https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
yum install yum-utils
yum install http://rpms.remirepo.net/enterprise/remi-release-7.rpm
yum-config-manager --enable remi-php72
yum install php72 php72-php-fpm php72-php-opcache php72-php-xml php72-php-gd php72-php-mbstring php72-php-json php72-php-iconv php72-php-imagick php72-php-pdo_sqlite php72-php-simplexml php72-php-ldap
```

### Certificates
use `certbot` to manage lets encrypt certificates.

## Backup

### Rsync

if you need to backup the files to another server, you only need to sent the `/var/persistent` folder. 
```
rsync -azP ~/www/app.mangel.io/production/shared/var/persistent/ user@IP:~/backup/
```

## Authorization

the application supports to authorize users:
 
- using the variable `VALID_REGISTRATION_EMAILS` in `.env`
- using one email per line in `var/transient/whitelist.txt`
 
additionally, if a user registers, and authorization was successful, 
his personal information is pre-filled using the file `var/transient/user_data.json`.
The correct entry is identified using the email, and then all properties which match `ConstructionManager` properties 
are prefilled from the entry if they are defined. The file hence may look like this:

```json
{
  "email": "git@mangel.io",
  "givenName": "git"
}
```


### LDAP

to make the following scripts run on CentOS, run:

```bash
sudo yum install epel-release
sudo yum install python34-setuptools
sudo easy_install-3.4 pip
sudo pip3 install ldapdomaindump
```

investigate how LDAP is setup by dumping the entries:

```bash
python3.4 -m ldapdomaindump -u domain\\user -p ldap_user -o /var/www/pendenzen/supporting/ --no-html --no-grep 199.199.199.199
```

then write a script to extract LDAP emails; some example code: 

```python
from ldap3 import Server, Connection, ALL

def read_out_ldap():
    server = Server('199.199.199.199', get_info=ALL)
    conn = Connection(server, 'domain\\user', 'password', auto_bind=True)
    conn.search('DC=stuff,DC=local', '(objectClass=person)',
                attributes=["mail"])

    return conn.entries
    
   
def parse_ldap_entries(entries):
    results = []
    for entry in entries:
        results.append({"email": entry["mail"].values[0]});

    return results

```