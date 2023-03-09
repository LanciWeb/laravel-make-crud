# Laravel MakeCrud

Laravel MakeView is a Laravel Package that helps you create all the files you need for CRUD operations on your models.

## Installation

Use composer to install Laravel MakeCrud

```bash
composer require lanciweb/laravel-make-crud
```

## Usage

### Default command
This command has no options and represent the default behaviour:
```bash
php artisan make:crud Post

 # - Generates the model (Post.php)
 # - Generates a resource controller (PostController.php)
 # - Automatically imports the Post model in the controller
 # - Generates a seeder (PostSeeder.php)
 # - Generates a create table migration (create_posts_table***.php)
 # - Registers all the Resource routes in web.php for the posts
 # - Creates a view folder  (resources/views/posts)
 # - The following blade files will be created in the folder
 # ----- index.blade.php
 # ----- show.blade.php 
 # ----- create.blade.php 
 # ----- edit.blade.php
```

### Prefix
Adding a prefix to the model (i.e: `Admin/Post`) name will result in the following differences:
```bash 
php artisan make:crud Admin\Post

# - The controller will be placed in the 'Admin' folder
# - Routes URIs will be prefixed with 'admin/'
# - Routes names will be prefixed with 'admin.'
# - Views will be placed in 'resources/views/admin/posts'
```

<br>

### Api option
Adding the `--api` option will result in the following differences:
```bash
php artisan make:crud Post --api

# - The controller will be placed in the 'Api' folder
# - The controller will not have the 'create' and 'edit' methods
# - Routes will be registered in the 'api.php' file
# - Views will not be generated
```

<br>

### Picking options
Adding any of the following options (except for `--api`) prevents the default behaviour and let you pick manually what you want to generate.

option|alias|result
---|---|---
`--controller`|`-c`|Generates the Resource Controller
`--migration`|`-m`|Generates the create table Migration
`--seeder`|`-s`|Generates the Seeder
`--factory`|`-f`|Generates the Factory
`--policy`|`-p`|Generates the Policy
`--requests`|`-R`|Generates the Form Request
`--views`|`-b`|Generates the Blade views
`--api`||See the Api option section
`--all`|`-a`|Adds all options available (except for the `--api`)


> Please note that **a model will always be generated**

> Please not that the `--all` option can be used together with the `--api` option.

<br>

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

<br>

## License

[MIT](./LICENSE.md)