# PinkCrab Debug Plugin

A wordpress plugin to help with debugging and development.

**[Download the latest release](https://github.com/Pink-Crab/Debug-Plugin/releases/latest/download/debug-plugin.zip)**

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

This writes to a custom `pc_debug.log` file in the wp-content directory (resolved via `WP_CONTENT_DIR`). This is useful for separating out debug data, from general errors.

```php
pclog($var, 'error');
pclog($var);
```
> Will only create the file if it does not exist.

The log can be viewed in the admin under **Tools > PC Debug Log**. Newest entries are at the top.

### pinkcrab_is_rest()

This function will return true if the current request is a rest request.

```php
if(pinkcrab_is_rest()){
    // Do something
}
```

### formatBytes($bytes, $precision = 2)

Formats a byte count as a readable string (B, KB, MB, GB, TB).

```php
echo formatBytes(1536); // 1.50 KB
```

## URL Parameters

### ?pc_show_enqueue

This will output all the scripts and styles that have been enqueued on the page.

### ?pc_show_hooks=hook,hook2

This will output all the hooks that have been added to the page. You can pass multiple hooks by comma separating them.

## Error views

Fatal errors are shown in place of the WordPress "critical error" screen, using the `wp_php_error_args` filter. The view depends on how the request was made:

| Request | View |
|---|---|
| Browser | `views/web-error.php`, a styled page with the message, stack trace (paths shortened to `../`), file and line, and a full backtrace. |
| AJAX or REST | `views/ajax-error.php`, plain text for reading in the network tab. |
| WP CLI | `views/cli-error.php`, plain text for the terminal. |

Under WP CLI, PHP warnings and notices are also caught by a custom error handler and printed once each, with HTML stripped and colourised for the terminal.