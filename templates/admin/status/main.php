<?php
if (!defined('ABSPATH')) {
	exit;
}

$current_tab = !empty($_REQUEST['tab']) ? sanitize_title($_REQUEST['tab']) : 'status';
?>
<div class="wrap fflcommerce">
	<div class="icon32 icon32-fflcommerce-status" id="icon-fflcommerce"><br /></div><h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php
		$tabs = array(
			'status' => __('System Status', 'fflcommerce'),
			'tools' => __('Tools', 'fflcommerce'),
			'logs' => __('Logs', 'fflcommerce'),
		);
		foreach ($tabs as $name => $label) {
			echo '<a href="'.admin_url('admin.php?page=fflcommerce_system_info&tab='.$name).'" class="nav-tab ';
			if ($current_tab == $name) {
				echo 'nav-tab-active';
			}
			echo '">'.$label.'</a>';
		}
		?>
	</h2><br/>
	<?php
	switch ($current_tab) {
		case "tools":
			Jigoshop_Admin_Status::status_tools();
			break;
		case "logs":
			Jigoshop_Admin_Status::status_logs();
			break;
		default:
			Jigoshop_Admin_Status::status_report();
			break;
	}
	?>
</div>
