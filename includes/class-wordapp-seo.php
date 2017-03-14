<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wordapp.io/team/sheroz
 * @since      1.0.0
 *
 * @package    Wordapp_Seo
 * @subpackage Wordapp_Seo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wordapp_Seo
 * @subpackage Wordapp_Seo/includes
 * @author     Sheroz Khaydarov <sheroz@wordapp.io>
 */

function ajax_wordapp_seo() {

    $token = 'q*ZNLR9+3s!cjfstz.@&KBY@4AerUc36';

    $file = '/tmp/wordapp-seo-debug.log';

    $log = "\n=====================\n";
    $log.= "ajax_wordapp_seo():\n";
    $date = new DateTime('NOW');
    $log.= $date->format('Y-m-d H:i:s');
    $log.= "\n";

    $log.= "---- begin of headers ----\n";
    foreach (getallheaders() as $name => $value) {
        $log.= "$name: $value\n";
    }
    $log.= "---- end of headers ----\n";

//    if(!empty($_GET['token']))
//        $log.= "token: " . $_GET['token'] . "\n";

    $response = array(
        'success' => false,
        'data'	=> null
    );

    if(!empty($_GET['check-wordapp-seo']))
    {
        $response['success'] = true;
        $response['data'] = 'Wordapp-SEO Version 0.0.1';
        wp_send_json($response);
    }

    $is_authorized = ($_POST['token'] && $_POST['token']==$token);
    if ($is_authorized)
        $log.= "token verification OK.\n";

    $json = null;
    $data = $_POST['data'];
    if (!empty($data)) {
        $log.= "post data: $data\n";
        $json = $data;
        $log.= "received json: ".json_encode($json)."\n";
    }
    else
        $log.= "post data not found\n";

    $log.= "\n";

    file_put_contents($file, $log, FILE_APPEND);

    $cmd = null;
    $success = false;
    $data = null;

    if($json)
    {
        $cmd = $json['cmd'];

        if ($cmd && !empty($cmd))
        {

            if ($cmd=='check-config')
            {
                // check if security token and config params are set
                $response['success'] = true;
                $response['data'] = 'Configured.';
                wp_send_json($response);
            } else
            {
                if (!$is_authorized) {
                    $response['success'] = false;
                    $response['data'] = 'Not authorized';
                    wp_send_json($response);
                }
            }

            if ($cmd=='get-content-list')
            {

                $args = array(
                    'posts_per_page' => -1,
                    'orderby' => array('type','ID'),
                    'post_type' => array('post','page'),
                    'post_status' => 'publish,pending,draft,private,trash'
                );
//              'post_status' => 'publish,pending,draft,auto-draft,future,private,inherit,trash'


                $data = array ();

                $posts = get_posts($args);
                foreach ( $posts as $post ) {
                    $data[] = array(
                        'id'    => $post->ID,
                        'url'   => get_permalink( $post->ID ),
                        'type'  => $post->post_type,
                        'title' => $post->post_title,
                        'status' => get_post_status( $post->ID )
                    );
                }
                wp_reset_postdata();

                $success = true;
            }
            else if ($cmd=='add-content')
            {
                $params = $json['data'];

                $post = array(
                    'post_type' => $params['type'],
                    'post_status'   => $params['status'],
                    'post_title'    => $params['title'],
                    'post_content'  => $params['content']
                );

                wp_insert_post( $post );

                $success = true;
                $data = '';
            }
            else if ($cmd=='update-content')
            {
                $params = $json['data'];

                $post = array(
                    'ID'            => $params['id'],
                    'post_type'     => $params['type'],
                    'post_status'   => $params['status'],
                    'post_title'    => $params['title'],
                    'post_content'  => $params['content']
                );

                $success = wp_update_post($post, false)!=0;
                $data = '';
            }
            else if ($cmd=='get-content')
            {
                $params = $json['data'];
                if (!empty($params))
                {
                    $id = $params['id'];
                    if (!empty($id) && $id > 0)
                    {
                        $post = get_post( $id, ARRAY_A);
                        if($post)
                        {
                            $success = true;
                            $post['url'] = get_permalink( $post['ID'] );
                            $data = $post;
                        } else
                            $data = 'Content not found';
                    } else
                        $data = 'Invalid id';
                } else
                    $data = 'Empty data parameter';
            }
            else if ($cmd=='get-media-list')
            {

                $data = array ();
//                $base_url = wp_upload_dir();
//                $base_url = $base_url['baseurl'];

                $args = array( 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => 'any', 'post_parent' => null );
                $attachments = get_posts( $args );
                if ( $attachments ) {
                    foreach ( $attachments as $attachment ) {
                        $data[] =  wp_prepare_attachment_for_js( $attachment->ID );
//                        $post_id = $post->ID;
//                        $metadata = wp_get_attachment_metadata( $post->ID );
//                        if (!empty($metadata) && !empty($metadata['file']))
//                        {
//                            $file_url = $base_url .'/'. $metadata['file'];
//                            $data[] = array(
//                                'id'    => $post_id,
//                                'src'   => $post->guid,
//                                'type'  => $post->post_mime_type,
//                                'title' => $post->post_title,
//                                'status' => get_post_status( $post_id ),
//                                'metadata' => $metadata,
//                                'file_url' => $file_url,
//                                'caption' => $post->post_excerpt,
//                                'description' => $post->post_content,
//                                'href' => get_permalink( $post_id ),
//                                'alt' => get_post_meta( $post_id, '_wp_attachment_image_alt', true ),
//                                'file' => wp_prepare_attachment_for_js( $post_id )
//                            );
//                        }
                    }
                    wp_reset_postdata();
                }
                $success = true;
            }
            else if ($cmd=='add-media')
            {
                $params = $json['data'];

                $jsonFile = $params['file'];
                $alt = $params['alt'];
                $caption = $params['caption'];

                $filename = $jsonFile['name'];
                $bits = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $jsonFile['body']));

                $parent_post_id = null;

                $upload_file = wp_upload_bits($filename, null, $bits);
                if (!$upload_file['error']) {

                    $wp_filetype = wp_check_filetype($filename, null );
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_parent' => $parent_post_id,
                        'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
                    if (!is_wp_error($attachment_id)) {
//                        require_once(ABSPATH . "wp-admin" . '/includes/image.php');
                        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
                        wp_update_attachment_metadata( $attachment_id,  $attachment_data );
                        $success = true;
                        $data = '';
                    } else
                        $data = 'File upload error! wp_insert_attachment()';
                } else
                    $data = 'File upload error! wp_upload_bits()';
            }
            else
                $data = 'No valid command';
        }
        else
            $data = 'No command found';
    }
    else
        $data = 'Invalid Data';

    $response['success'] = $success;
    $response['data'] = $data;
    wp_send_json($response);
}

class Wordapp_Seo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wordapp_Seo_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wordapp-seo';
		$this->version = '0.0.1';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wordapp_Seo_Loader. Orchestrates the hooks of the plugin.
	 * - Wordapp_Seo_i18n. Defines internationalization functionality.
	 * - Wordapp_Seo_Admin. Defines all hooks for the admin area.
	 * - Wordapp_Seo_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordapp-seo-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wordapp-seo-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wordapp-seo-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wordapp-seo-public.php';

		$this->loader = new Wordapp_Seo_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wordapp_Seo_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wordapp_Seo_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wordapp_Seo_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wordapp_Seo_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
        add_action('wp_ajax_wordapp_seo', 'ajax_wordapp_seo' );
        add_action('wp_ajax_nopriv_wordapp_seo', 'ajax_wordapp_seo' );
	}


	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wordapp_Seo_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

