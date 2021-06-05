Blog Test Symfony 5.3
======

### Installation Procedure

Install the vendors of the project running:

```sh
composer install
```
fill the blanks..


Create database, schema and default datafixtures:

```sh
php app/console doctrine:database:create
php app/console doctrine:schema:create
```

Now run the server:

```sh
php app/console server:run
```

you can browse the site in http://localhost:8000

**Enjoy developing!**
