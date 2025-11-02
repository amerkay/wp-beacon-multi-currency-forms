# How to restore Duplicator Archive

## First use
Navigate to the folder on CLI and run docker compose:
```sh
cd test_wp_docker_envs/wp_divi
docker compose up -d
```

Then navigate to: http://localhost/installer.php

Then Enter the "Database Connection" settings as follows:
```
Action: Empty Database (default)
Host: db
Database: wordpress
User: wordpress
Password: wordpress
```

Click `Validate`, and ignore the `Some folders do not have write permissions` warning. Check the terms tick box and click `Next`.

Ignore the `Can't remove / extract files` warning, that's just because we mount our `../src` directory as read-only.

**Login with username `wordpress` and password `wordpress`.**

## Reset the docker volumes
To remove the docker volumes and start over:
```sh
docker compose down -v
```