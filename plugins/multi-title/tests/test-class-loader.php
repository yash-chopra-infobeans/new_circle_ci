<?php

use BigBite\MultiTitle\Loader;

class Test_Class_Loader extends WP_UnitTestCase {
	protected $loader;
	protected $settings;

	public function setUp() {
		$current_user = get_current_user_id();
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );

		$this->loader = new Loader();
	}

	public function test_has_register_multi_title_block_type_action() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', [ $this->loader, 'register_multi_title_block_type' ] ) );
	}

	public function test_register_multi_title_block_type() {
		unregister_block_type( 'bigbite/multi-title' );

		$this->loader->register_multi_title_block_type();

		$registry = \WP_Block_Type_Registry::get_instance();

		$this->assertTrue( $registry->is_registered( 'bigbite/multi-title' ) );
	}

	public function test_has_init_action() {
		$this->assertEquals( 10, has_action( 'init', [ $this->loader, 'register_meta' ] ) );
	}

	public function test_register_meta() {
		global $wp_meta_keys;

		$post_id = $this->factory->post->create();

		$this->loader->register_meta();

		$this->assertEquals( [ 'post' => [ '' => [ 'multi_title' => [
			'type' => 'string',
			'description' => '',
			'single' => true,
			'sanitize_callback' => null,
			'auth_callback' => 'is_user_logged_in',
			'show_in_rest' => true
		] ] ] ], $wp_meta_keys );
	}

	public function test_has_enqueue_block_editor_assets_action() {
		$this->assertEquals( 1, has_action( 'enqueue_block_editor_assets', [ $this->loader, 'enqueue_assets' ] ) );
	}

	public function test_enqueue_assets() {
		wp_dequeue_script( 'multi-title-script' );
		wp_dequeue_style( 'multi-title-styles' );

		$this->loader->enqueue_assets();

		$this->assertTrue( wp_script_is( 'multi-title-script' ) );
		$this->assertTrue( wp_style_is( 'multi-title-styles' ) );
	}
}
