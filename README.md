# laravel-interactive-make
Interactive make commands for making Model and Migration with fields

- Asks for field name
  - Then, data type. Based on field name, it'd try preselecting best option for you.
  - If the field name has "_id" in it, you can optinally create a relationship with correct model.<br>
    For example, if the name is "user_id" and the model User already exists, you can optionally create HasOne relationship.
- Asks to create OneToMany/ BelongsTo relationships.
- Generates Model and Migration files.

```shell
composer require mirhamzah/laravel-interactive-make:dev-master
```
Then
```shell
php artisan make:imodel ModelName
```
Or
```shell
php artisan make:imodel ModelName -m
```
for making a new migration as well, just like with normal make:model command.

![Screenshot](images/screenshot.png)