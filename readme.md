# PinkCrab Debug Plugin

A wordpress plugin to help with debugging and development.

## Functions.

### dump(...$data) 

The modern dump function created by symfony. This function will output the variable in a readable format.

```php
dump($var);
```

### dd(...$data)

The modern dump function created by symfony. This function will output the variable in a readable format and then die.

```php
dd($var);
```

> You have access to all underlying symfony var dump functions, uses the same naming conventions.


### adump(...$data)

This is an ajax ready take on dump(), which outputs in a readable format via the network tab on the browser.

```php
adump($var);
```
> Casts true to `TRUE`, false to `FALSE` and null to `NULL`. To avoid confusion caused with phps print_r function.

### adie(...$data)

This is an ajax ready take on dd(), which outputs in a readable format via the network tab on the browser and then dies.

```php
adie($var);
```

### write_log($data)

This function will write to the debug.log file in the wp-content directory.

```php
write_log($var);
```

### pclog($data, $type = 'log')

This writes to a custom pc-debug.log file in the wp-content directory. This is useful for separating out debug data, from general errors.

```php
pclog($var, 'error');
pclog($var);
```
> Will only create the file if it does not exist.

## URL Parameters

### ?show_enqueue

This will output all the scripts and styles that have been enqueued on the page.

### ?show_hooks=hook,hook2

This will output all the hooks that have been added to the page. You can pass multiple hooks by comma separating them.