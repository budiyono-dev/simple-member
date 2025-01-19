<?php
/**
 * Plugin Name: Simple Member
 * Description: Simple member by Codingduluaja.com
 * Version: 1.0.0
 * Author: Budiyono
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Activation hook to create the database table
function cda_create_members_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'members';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id int NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cda_create_members_table');

// Register admin menu
function cda_register_member_menu()
{
    if (current_user_can('manage_options')) { // Use manage_options for admin access
        $parentSlug = 'member-management';

        add_menu_page('Member Management', 'Members', 'manage_options', $parentSlug, 'cda_member_page', 'dashicons-groups');
        add_submenu_page($parentSlug, 'Member', 'Member', 'manage_options', $parentSlug, 'cda_member_page');
        add_submenu_page($parentSlug, 'Payment', 'Payment', 'manage_options', 'cda-payment-page', 'cda_payment_page'); // Separate callback
    }
}
add_action('admin_menu', 'cda_register_member_menu');

// Member page callback
function cda_member_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'members';

    // Handle form submissions (with nonce verification)
    if (isset($_POST['action'])) {
        if (isset($_POST['cda_add_member_nonce']) && wp_verify_nonce($_POST['cda_add_member_nonce'], 'cda_add_member')) {
            if ($_POST['action'] == 'add_member') {
                $wpdb->insert($table_name, [
                    'name' => sanitize_text_field($_POST['name']),
                    'email' => sanitize_email($_POST['email']),
                ]);
            }
        }
        if (isset($_POST['cda_delete_member_nonce']) && wp_verify_nonce($_POST['cda_delete_member_nonce'], 'cda_delete_member')) {
            if ($_POST['action'] == 'delete_member') {
                $wpdb->delete($table_name, ['id' => intval($_POST['id'])]);
            }
        }
    }

    // Retrieve members
    $members = $wpdb->get_results("SELECT * FROM $table_name");
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Member Management</h1>
        <form method="post" class="form-add-member">
            <?php wp_nonce_field('cda_add_member', 'cda_add_member_nonce'); ?>
            <input type="hidden" name="action" value="add_member">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" class="button button-primary">Add Member</button>
        </form>
        <h2>Members</h2>
        <table class="wp-list-table widefat striped" id="memberTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($members)) : ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No Data</td>
                    </tr>
                <?php else : ?>
                    <?php $i = 1; foreach ($members as $member) : ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo esc_html($member->id); ?></td>
                            <td><?php echo esc_html($member->name); ?></td>
                            <td><?php echo esc_html($member->email); ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <?php wp_nonce_field('cda_delete_member', 'cda_delete_member_nonce'); ?>
                                    <input type="hidden" name="action" value="delete_member">
                                    <input type="hidden" name="id" value="<?php echo intval($member->id); ?>">
                                    <button type="submit" class="button button-secondary">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php $i++; endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}

// Payment page callback (example)
function cda_payment_page() {
    echo '<div class="wrap"><h1>Payment Management</h1><p>Payment content here.</p></div>';
}

// Enqueue admin CSS and JS
function cda_admin_css_js()
{
    //Use plugin directory
    wp_enqueue_style('cda-wp-admin', plugins_url( 'assets/admin.css', __FILE__ ));
    wp_enqueue_script('cda-wp-admin', plugins_url( 'assets/admin.js', __FILE__ ), array('jquery'), false, true);

    wp_enqueue_style('cda-wp-admin-datatable', plugins_url( 'assets/dt/datatables.min.css', __FILE__ ));
    wp_enqueue_script('cda-wp-admin-datatables', plugins_url( 'assets/dt/datatables.min.js', __FILE__ ), array('jquery'), false, true);
}
add_action('admin_enqueue_scripts', 'cda_admin_css_js');
