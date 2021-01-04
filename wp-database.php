<?php
/*
 * Plugin Name: Demo Database
 * Plugin URI:
 * Author: mostofa kamal
 * Author URI:
 * Description: demo database plugin
 * Version: 1.0
 * Text Domain:wpdatabase
 * Domain Path:/languages/
 */

define("DATABASE_DB_VERSION","1.1");
function wpdatabase_init(){
    // $wpdb is a object. It is connected Wordpress Database
 global $wpdb;
 $table_name = $wpdb->prefix."persons";

 $sql = "CREATE TABLE {$table_name}(
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(150),
    email VARCHAR(200),
    PRIMARY key (id)
);";

 require_once (ABSPATH."wp-admin/includes/upgrade.php");
 dbDelta($sql);
 add_option("database_db_version",DATABASE_DB_VERSION);
 // update table column (add)  version
 if(get_option('database_db_version') != DATABASE_DB_VERSION){

     $sql = "CREATE TABLE {$table_name}(
                id INT NOT NULL AUTO_INCREMENT,
                name VARCHAR(150),
                email VARCHAR(200),
                age INT,
                PRIMARY key (id)
            );";
     dbDelta($sql);
     update_option('database_db_version',DATABASE_DB_VERSION);
 }

}
// special hooks
register_activation_hook(__FILE__,'wpdatabase_init');

function wpdatabase_plugin_loaded(){
    // $wpdb is a object. It is connected Wordpress Database
    global $wpdb;
    $table_name = $wpdb->prefix."persons";
$sql = "ALTER TABLE {$table_name} ADD COLUMN address VARCHAR(250), ADD COLUMN age INT";
if(get_option('database_db_version') != DATABASE_DB_VERSION) {
    $wpdb->query($sql);
}
 /*$insert = "INSERT INTO {$table_name} (name,age,email,address) VALUES ('mostofa kamal','20','abc@gmail.com','gazipur');";
$wpdb->query($insert);*/


}

add_action('plugin_loaded', 'wpdatabase_plugin_loaded');

function wpdatabase_load_data(){
    global $wpdb;
    $table_name = $wpdb->prefix."persons";
    $wpdb->insert($table_name, array(
        'name'=> 'mostofa kamal passa',
        'email' => 'mostofakamal1990@gmail.com',
        'age' => '33',
        'address' => 'gazipur'
    ));
}
register_activation_hook(__FILE__,'wpdatabase_load_data');

function wpdatabase_deactivation(){
    global $wpdb;
    $table_name = $wpdb->prefix."persons";
    // remove data form persons tables
    $sql = "TRUNCATE TABLE {$table_name}";
    $wpdb->query($sql);
}
// spacial hook
register_deactivation_hook(__FILE__,'wpdatabase_deactivation');


// add menu page

function wpdatabase_add_menu_page(){

    add_menu_page(
        __('db demo database', 'wpdatabase'),//  page title
        __('db demo ', 'wpdatabase'), // menu  title
        'manage_options', // capability
        'dbdemo', // menu slug or menu url(link)
        'wpdatabase_display_menu_data', // callback function for display data
        '', // icon url
        '' // position of menu

    );
}
add_action('admin_menu', 'wpdatabase_add_menu_page');


function wpdatabase_display_menu_data(){
    global $wpdb;
    $table_name = $wpdb->prefix."persons";
    echo "<h2> hello menu data </h2>";

    $id = $_GET['pid'] ?? 0;
    $id = sanitize_key($id);

    if($id){
        $result = $wpdb->get_row("SELECT * FROM {$table_name} WHERE id=".$id, OBJECT);
        if($result){
            echo "name ". $result->name . '<br>';
            echo "email ". $result->email . '<br>';
            echo "age ". $result->age . '<br>';
        }
    }


    ?>
<!--    <form action="" method="post">-->
    <form action="<?php echo admin_url('admin-post.php');?>" method="post">
        <?php wp_nonce_field('dbdemo', 'nonce'); ?>
        <input type="hidden" name="action" value="add_record">
        <p><label for="name">Name</label>
            <input type="text" name="name" value="<?php if($id) echo $result->name ?>">
        </p>
        <p><label for="email">email</label>
            <input type="text" name="email" value="<?php if($id) echo $result->email ?>">
        </p>
         <p><label for="age">AGe</label>
            <input type="text" name="age" value="<?php if($id) echo $result->age ?>">
        </p>
        <p><label for="address">Address</label>
            <input type="text" name="address" value="<?php if($id) echo $result->address ?>">
        </p>
    <?php  if($id){
        echo "<input type='hidden' name='id' value='".$id."'";
        submit_button("Update Record");
    }else{
        submit_button("Add Record");
    }?>
    </form>

   <?php
   /* if(isset($_POST['submit'])){
        $noce = sanitize_text_field($_POST['nonce']);
        if(wp_verify_nonce($noce, 'dbdemo')) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);
            $age = sanitize_text_field($_POST['age']);
            $address = sanitize_text_field($_POST['address']);

            $wpdb->insert($table_name, array(
                'name' => $name,
                'age' => $age,
                'email' => $email,
                'address' => $address

            ));
        }else{
            echo "you are not allowed";
        }
    }*/
}


function wpdatabase_add_record()
{
    global $wpdb;
    $table_name = $wpdb->prefix."persons";

        $noce = sanitize_text_field($_POST['nonce']);
        if (wp_verify_nonce($noce, 'dbdemo')) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);
            $age = sanitize_text_field($_POST['age']);
            $address = sanitize_text_field($_POST['address']);
            $id = sanitize_text_field($_POST['id']);
            if($id){
                $wpdb->update($table_name,

                    [ 'name' => $name,
                    'age' => $age,
                    'email' => $email,
                    'address' => $address],

                    ['id' => $id]
                );
                wp_redirect(admin_url('admin.php?page=dbdemo&pid='.$id));
            }else{
                $wpdb->insert($table_name, array(
                    'name' => $name,
                    'age' => $age,
                    'email' => $email,
                    'address' => $address

                ));
                $new_id = $wpdb->insert_id;
                wp_redirect(admin_url('admin.php?page=dbdemo&pid='.$new_id));
            }



        } else {
            echo "you are not allowed";
        }



}
add_action('admin_post_add_record', 'wpdatabase_add_record');