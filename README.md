charstats (WIP)
===============

**Dynamic [D&D Beyond](https://dndbeyond.com) character health overlay for streaming**

To be hosted on a server with PHP (cURL and MySQLi required) and MySQL. It needs a **sqlcred.json** file with the following structure:

```json
{
  "host": "host.name.here",
  "username": "user_name",
  "password": "P4$sw0Rd",
  "dbname": "database_name"
}
```

The database it points to, should contain a table named **stats** (InnoDB), with the following columns:

* **id:** integer, not null
* **max_orig:** integer, not null
* **damage_orig:** integer, not null
* **damage_fix:** integer, null by default
