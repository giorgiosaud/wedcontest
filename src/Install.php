<?php 
namespace Zonapro\WedContest;

class Install{
	/**
	 * install initial dependencies
	 * @return void
	 */
	public static function install(){
		// if ( ! is_blog_installed() ) {
			// return;
		// }

		// Check if we are not already running this routine.
		// if ( 'yes' === get_transient( 'wedcontest_installing' ) ) {
			// return;
		// }

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wedcontest_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		
		wedcontest_maybe_define_constant( 'WEDCONTEST_INSTALLING', true );

		// self::remove_admin_notices();
		// self::create_options();
		// self::create_tables();
		self::create_roles();
		// self::setup_environment();
		// self::create_terms();
		// self::create_cron_jobs();
		// self::create_files();
		// self::maybe_enable_setup_wizard();
		// self::update_wc_version();
		// self::maybe_update_db_version();

		delete_transient( 'wedcontest_installing' );

		do_action( 'wedcontest_flush_rewrite_rules' );
		do_action( 'wedcontest_installed' );
	}

	/**
	 * Create roles and capabilities.
	 */
	public static function create_roles() {
		// Customer role
		add_role( 'representant', __( 'Representant', 'wedcontest' ), array(
			'read' 					=> true,
			'edit_participant'=>true,
			'read_participant'=>true,
			'delete_participant'=>true,
		) );
	}
	/**
	 * Setup WC environment - post types, taxonomies, endpoints.
	 *
	 * @since 3.2.0
	 */
	private static function setup_environment() {
		// new PostTypes();
		// WedContest_Post_types::register_taxonomies();
		// WC()->query->init_query_vars();
		// WC()->query->add_endpoints();
		// WC_API::add_endpoint();
		// WC_Auth::add_endpoint();
	}
}