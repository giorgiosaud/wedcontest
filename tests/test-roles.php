<?php
/**
 * Class SampleTest
 *
 * @package Wedcontest
 */

/**
 * Sample test case.
 */
class RoleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 * @test
	 */
	function a_user_can_be_a_representant() {
		$user_query = new WP_User_Query( array( 'role' => 'representant' ) );
		$users_count = (int) $user_query->get_total();
		$this->assertEquals( $users_count, 0);
		$user_id = $this->factory->user->create( array( 'role' => 'representant' ) );
		$user_query = new WP_User_Query( array( 'role' => 'representant' ) );
		$users_count = (int) $user_query->get_total();
		$this->assertEquals( $users_count, 1);
	}
	/**
	 * A register_representant shortcode.
	 * @test
	 */
	public function a_visitant_can_see_a_register_form_via_shortcode_in_page(){
		// $post = $this->factory->post->create( array( 'post_content' => 'representant' ) );
		$content = '[register_representant]';
		$shortcoderesult=do_shortcode($content);
		// var_dump($shortcoderesult);
        $hopeHas='Registering Representant';
        $this->assertContains($hopeHas,$shortcoderesult);

		// var_dump($go);
		// do_shortcode('[register_representant/]');
		// $this->assertSee('Registering Representant');

	}
}
