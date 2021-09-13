If you are seperating the js from the template file (good idea), the value for the 'url' parameter in the ajax config object needs to be populated via a localized script. The following code should be added to the theme's functions.php to achieve this:

```php
<?php
if (is_page_template('ajax-template.php')) {
    wp_enqueue_script( 'ajax-script', get_stylesheet_directory_uri() . 'placeholder/path/to/js/file', array('jquery'), '1.0.0', false );
    wp_localize_script( 'ajax-script', 'ajax_data', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
```

... and the updated js:
```js
$.ajax({
    type : "GET",
    dataType : "json",
    url : ajax_object.ajax_url, // <- Here is the change
```