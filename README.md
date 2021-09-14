If you are seperating the js from the template file (good idea), the value for the 'url' parameter in the ajax config object needs to be populated via a localized script. The following code should be added to the theme's functions.php to achieve this:

```php
<?php
function enqueue_ajax_scripts() {
    if (is_page_template('ajax-template.php')) {
        $script_handle = 'ajax-script';
        wp_enqueue_script( $script_handle, '/placeholder/path/to/js/file', array('jquery'), '1.0.0', false );
        wp_localize_script( $script_handle, 'ajax_data', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    }
}

add_action( 'wp_enqueue_scripts', 'enqueue_ajax_scripts' );
```

... and the updated js:
```js
$.ajax({
    type : "GET",
    dataType : "json",
    url : ajax_data.ajax_url, // <- Here is the change
```