<?php
add_action( 'wp_enqueue_scripts', 'joroni_theme_enqueue_styles' );
function joroni_theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}


/**
 * Do not edit anything in this file unless you know what you're doing
 */

use Roots\Sage\Config;
use Roots\Sage\Container;

/**
 * Helper function for prettying up errors
 * @param string $message
 * @param string $subtitle
 * @param string $title
 */
$sage_error = function ($message, $subtitle = '', $title = '') {
    $title = $title ?: __('Sage &rsaquo; Error', 'sage');
    $footer = '<a href="https://roots.io/sage/docs/">roots.io/sage/docs/</a>';
    $message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
    wp_die($message, $title);
};

/**
 * Ensure compatible version of PHP is used
 */
if (version_compare('7.1', phpversion(), '>=')) {
    $sage_error(__('You must be using PHP 7.1 or greater.', 'sage'), __('Invalid PHP version', 'sage'));
}

/**
 * Ensure compatible version of WordPress is used
 */
if (version_compare('4.7.0', get_bloginfo('version'), '>=')) {
    $sage_error(__('You must be using WordPress 4.7.0 or greater.', 'sage'), __('Invalid WordPress version', 'sage'));
}

/**
 * Ensure dependencies are loaded
 */
if (!class_exists('Roots\\Sage\\Container')) {
    if (!file_exists($composer = __DIR__.'/../vendor/autoload.php')) {
        $sage_error(
            __('You must run <code>composer install</code> from the Sage directory.', 'sage'),
            __('Autoloader not found.', 'sage')
        );
    }
    require_once $composer;
}

/**
 * Sage required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
array_map(function ($file) use ($sage_error) {
    $file = "../app/{$file}.php";
    if (!locate_template($file, true, true)) {
        $sage_error(sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file), 'File not found');
    }
}, ['helpers', 'setup', 'filters', 'admin']);

/**
 * Here's what's happening with these hooks:
 * 1. WordPress initially detects theme in themes/sage/resources
 * 2. Upon activation, we tell WordPress that the theme is actually in themes/sage/resources/views
 * 3. When we call get_template_directory() or get_template_directory_uri(), we point it back to themes/sage/resources
 *
 * We do this so that the Template Hierarchy will look in themes/sage/resources/views for core WordPress themes
 * But functions.php, style.css, and index.php are all still located in themes/sage/resources
 *
 * This is not compatible with the WordPress Customizer theme preview prior to theme activation
 *
 * get_template_directory()   -> /srv/www/example.com/current/web/app/themes/sage/resources
 * get_stylesheet_directory() -> /srv/www/example.com/current/web/app/themes/sage/resources
 * locate_template()
 * ├── STYLESHEETPATH         -> /srv/www/example.com/current/web/app/themes/sage/resources/views
 * └── TEMPLATEPATH           -> /srv/www/example.com/current/web/app/themes/sage/resources
 */
array_map(
    'add_filter',
    ['theme_file_path', 'theme_file_uri', 'parent_theme_file_path', 'parent_theme_file_uri'],
    array_fill(0, 4, 'dirname')
);
Container::getInstance()
    ->bindIf('config', function () {
        return new Config([
            'assets' => require dirname(__DIR__).'/config/assets.php',
            'theme' => require dirname(__DIR__).'/config/theme.php',
            'view' => require dirname(__DIR__).'/config/view.php',
        ]);
    }, true);


/********************************************************* */

function add_pages_function() {

    global $wpdb; 
    // this is how you get access to the database
    $data = $_POST['data']; 
    //loop through json
    foreach($data as $d){

    echo $d['Name'];
    //Add pages
    $post["id"] = wp_insert_post( array(
            "post_title" => $d['Name'],
            "post_author" => 1,
            "post_content" => $d['Synopsis'],
            "post_type" => 'movies',   //or add 'custom_post_type' slug
            "post_status" => "publish"
        )); 
    //UPDATED - for adding meta data (linked custom fields)
    update_post_meta( $post["id"],'my-custom-meta' , $d['Name'] );
   
    }
    wp_die();
}
//  uses 'add_pages' from the action after the wp_ajax
add_action( 'wp_ajax_add_pages', 'add_pages_function' );

/********************************* */

    add_theme_support( 'post-thumbnails' );

    /*************Fetching field data from externalAPI ************** */
    
    add_action( 'rest_api_init', 'add_custom_fields' );
    function add_custom_fields() {
            register_rest_field(
                'movies', 
                'custom_fields', //New Field Name in JSON RESPONSEs
                array(
                  //  'director' => 'Director',
                    'get_callback'    => 'get_custom_fields', // custom function name 
                    'update_callback' => null,
                    'schema'          => null,
                    )
            );
        }


    function movie_init() {
                // set up movies labels
            $labels = array(
                    'name' => __('Movies'),
                    'singular_name' => __('Movies'),
                    'add_new' => 'Add New Movie',
                    'add_new_item' => 'Add New Movie',
                    'edit_item' => 'Edit Movie',
                    'new_item' => 'New Movie',
                    'all_items' => 'All Movies',
                    'view_item' => 'View Movie',
                    'search_items' => 'Search Movies',
                    'not_found' =>  'No Movies Found',
                    'not_found_in_trash' => 'No Movies found in Trash', 
                    'parent_item_colon' => '',
                    'menu_name' => 'Movies',
                );
                
                // register post type
            $args = array(
                    'labels' => $labels,
                    'public' => true,
                    'has_archive' => true,
                    'show_ui' => true,
                    'capability_type' => 'post',
                    'hierarchical' => false,
                    'rewrite' => array('slug' => 'movies'),
                    'query_var' => true,
                    'menu_icon' => 'dashicons-randomize',
                    'supports' => array(
                        'title',
                        'editor',
                        'description',
                        'content',
                        'excerpt',
                        'trackbacks',
                        'custom-fields',
                        'comments',
                        'revisions',
                        'thumbnail',
                        'author',
                        'page-attributes'
                    )
                );
                register_post_type( 'movies', $args );
                
                // register taxonomy
                register_taxonomy('movie_category', 'movies', array('hierarchical' => true, 'label' => 'Category', 'query_var' => true, 'rewrite' => array( 'slug' => 'movies' )));
            }
        add_action( 'init', 'movie_init' );




/*
        add_filter( 'pre_get_posts', 'my_get_posts' );
        function my_get_posts( $query ) {
            if ( is_home() && false == $query->query_vars['suppress_filters'] )
            $query->set( 'post_type', array(
            'movies') );
                return $query;
        }  

*/

    function fetchMovies(){

        if ( is_home() )
            
            return;
              
                // Create posts from remote data?
            $to_create_posts = true;
              
                // Load the feed
            $data = RemoteMovies::LoadRemoteMovies( $to_create_posts );
        
              
            }
  add_action( 'init', 'fetchMovies' );



/************************ */

        if (!class_exists('RemoteMovie')) :
                class RemoteMovie
                {
                    // original data
                    public $raw;

                    // public items
                    public $title;
                    public $director;
                    public $maincast;
                    public $movie_html;
                    public $id;

                    public function __construct($dirty_data)
                    {
                        $this->parse_data($dirty_data);
                    }

                    /**
                     * Clean the data, never expect good data
                     */
                    private function sanitize_remote_data($data_as_array)
                    {
                        foreach ($data_as_array as $inx => $data) {
                            $data_as_array [$inx] = sanitize_text_field($data);
                        }
                        return $data_as_array;
                    }

                    /**
                     *  Take the dirty data, clean it, then set our properties
                     */
                    private function parse_data($dirty_data_as_array)
                    {
                        $clean_data = $this->sanitize_remote_data($dirty_data_as_array);

                        // save the raw data (cleaned)
                        $this->raw = $clean_data;

                        // grab the values we need
                        $this->title = $this->raw ['Name'];
                        $this->movie_html = $this->raw ['Synopsis'];
                        $this->director = $this->raw ['Director'];
                        $this->maincast = $this->raw ['MainCast'];
                        $this->id = $this->raw ['Id'];
                    }

                    /**
                     * Update a movies with the latest meta data
                     */
                    public function update_movie_post_meta($post_id)
                    {
                        $meta_values_to_set = array (
                            '__movie_dimensions' => $this->raw['Name'],
                            'director' => $this->raw['Director'],
                            'maincast' => $this->raw['MainCast']
                            
                        );

                        foreach($meta_values_to_set as $key => $meta_value){
                            $success = $this->update_meta($post_id, $key, $meta_value );
                        }
                    }

                    public function update_meta ( $post_id, $key, $meta_value ){
                        if ( isset( $meta_value ) && 0 < strlen( trim( $meta_value ) ) ) {
                            return update_post_meta ( $post_id, $key, $meta_value );
                        }
                        return false;
                    }

                    public function get_meta ( $post_id, $key ){
                        $meta_value = get_post_meta( $post_id, $key, true );
                        return empty( $value ) ? null : $meta_value;
                    }

                } 
            endif; 


        if (!class_exists('RemoteMovies')) :
            class RemoteMovies
            {
                /** data from url */
                public static $remote_data;

                /** parsed data -- use these instances for stuffs */
                public static $movies;

                /**
                 * Pull the main feed & extra pages
                 */
                private static function GetRemoteMovies($url)
                {
                    // remote data
                    $response = wp_remote_get($url);

                    if (is_wp_error($response) || !isset($response['body'])) return; // bad response

                    // the good stuff
                    $body = wp_remote_retrieve_body($response);

                    if (is_wp_error($body)) return; // bad body

                    // decode the data
                    $data = json_decode($body, true);

                    if (!$data || empty($data)) return; // bad data

                    // make sure there isn't anymore
                    if (isset($data['page']) && isset($data['no_of_pages'])) {
                        $page = $data['page'];
                        $pages = $data['no_of_pages'];
                        $next_page_link = $data['next_page_link'];
                        if ($page !== $pages) {
                            // keep loading data from $next_page_link.....
                        }
                    }

                    // final remote data
                    return $data;
                }

                /**
                 * Load the remote feed and parse the data
                 */
                public static function LoadRemoteMovies($to_create_posts)
                {
                    global $wpdb;
                    self::$remote_data = self::GetRemoteMovies(' http://www.rcksld.com/GetNowShowing.json');
                
                    if (!isset(self::$remote_data ['Data'])) return; // no Data --- booo!

                    $works = array();
             
                    foreach (self::$remote_data ['Data'] as $inx => $remote_item_data) {
                        // create movie instance
 
                        $return = $wpdb->get_row( "SELECT * FROM wp_posts WHERE post_title = '" . $remote_item_data["Name"] . "' " );
                        if( empty( $return ) ) {
                           // return false;
                           $movie = new RemoteMovie($remote_item_data);
                           $works[] = $movie;
                        } else {
                           // return true;
                        
                        }
    
                    
                    
                    }


                    // set our local data that has been cleaned and parsed
                    self::$movies = $works;

                    // let's make new POSTS!!!
                    if ($to_create_posts) {
                        $count = 0;
                       
                        foreach (self::$movies as $inx => $movie) {
                            $count++; // for demo ---- let's not go overboard here

                            $post_id = self::CreateLocalMoviePost($movie);

                            if ($post_id == -1) {
                                // Nothing happened
                                return false;
                            } else if ($post_id == -2) {
                                // already exists
                                // ... I guess we could update a movies's data here...
                                return false;
                            } else {
                                
                                // new movies was created, let's update it's meta
                                $success = $movie->update_movie_post_meta($post_id);


                            }

                            if ($count >= 21) return self::$remote_data;

                        }
                    }

                    // return the remote data... for whatever reason outsite of this class
                    return self::$remote_data;
                }

                public static function CreateLocalMoviePost($movie)
                {
                    if (!$movie) return -1;


                        // DEBUG
                  //  echo '<pre>';
                   // print_r( $movie );
                   // echo '</pre>';


                    // do movies creation from $movie data that has been sanitized

                    $template = 'homepage.blade.php'; // add a custom template if you want

                    $post_type = 'movies';

                    $img_url = $movie->raw['LargePosterUrl'];
                    $movie_html = $movie->movie_html;

                    $content = "<a target=\"_blank\" href=\"${img_url}\"><img width=\"100%\" height=\"auto\" src=\"${img_url}\"></a>\n\r<br />\r\n<p><label>Synopsis: </label>\r\n ${movie_html}</p>";

                    $new_post_id = self::CreateNewMoviePost($post_type, $movie->title, $content, $template, $movie);

                    return $new_post_id;
                }

              
                public static function CreateNewMoviePost($post_type = 'movies', $title, $content, $template_rel_path = '', $data)
                {
                    // Initialize the page ID to -1. This indicates no action has been taken.
                    $post_id = -1;

                    if (!current_user_can('publish_posts')) {
                        // sorry...
                        return $post_id;
                    }

                    // Prep
                    $author_id = get_current_user_id();
                    if (!$author_id) {
                        return $post_id;
                    }

                    $title = sanitize_text_field(wp_strip_all_tags($title)); // remove any junk
                    $title = esc_html(wp_unslash($title));
                    //$slug = sanitize_title_with_dashes($title); // converts to a usable post_name
                    $slug =  sanitize_text_field(wp_strip_all_tags(sanitize_title_with_dashes($title)));
                   
                    $director = sanitize_title_with_dashes($director); // converts to a usable post_name
                    $maincast = sanitize_title_with_dashes($maincast); // converts to a usable post_name
                  //  $content = sanitize_text_field(wp_strip_all_tags($content)); // converts to a usable post_name
                    $content = $content; // converts to a usable post_name
                    $post_type = post_type_exists($post_type) ? $post_type : 'movies'; // make sure it exists

                    // If the page doesn't already exist, then create it (by title & slug)
                    if (null == get_page_by_title($title) && empty(get_posts(array('name' => $slug)))) {

                        // Set the movies ID so that we know the movies was created successfully
                        $post_id = wp_insert_post(
                            array(
                                'post_name' => $slug,
                                'post_title' => $title,
                                'post_content' => $content,
                                'post_type' => $post_type,
                                'Director' => $director,
                                'MainCast' => $maincast,
                                'post_author' => $author_id,
                                'comment_status' => 'closed',
                                'ping_status' => 'closed',
                                'post_status' => 'publish',
                            )
                        );

                        if ($post_id && $post_id > 0 && !empty($template_rel_path)) {

                            // make sure the template exists
                            $template_full_path = trailingslashit(get_stylesheet_directory()) . $template_rel_path;
                            if (file_exists($template_full_path)) {

                                // set the movies meta data -- use relative path
                                update_post_meta($post_id, '_wp_page_template', $template_rel_path);
                            }
                        } // end template check

                        // Otherwise, we'll stop
                    } else {
                        // Arbitrarily use -2 to indicate that the page with the title already exists
                        $post_id = -2;

                    } // end if

                    return $post_id;

                } // end programmatically_create_post

            }// RemoteMovies
        endif; // RemoteMovies



    add_action( 'rest_api_init', 'custom_api_get_all_posts' );   
        function custom_api_get_all_posts() {
            register_rest_route( 'custom/v1', '/all-posts', array(
                'methods' => 'GET',
                'callback' => 'custom_api_get_all_posts_callback'
            ));
        }





    function custom_api_get_all_posts_callback( $request ) {
        // Initialize the array that will receive the posts' data. 
        $posts_data = array();
        // Receive and set the page parameter from the $request for pagination purposes
        $paged = $request->get_param( 'page' );
        $paged = ( isset( $paged ) || ! ( empty( $paged ) ) ) ? $paged : 1; 
        // Get the posts using the 'post' and 'news' post types
        $posts = get_posts( array(
                'paged' => $paged,
                'post__not_in' => get_option( 'sticky_posts' ),
                'posts_per_page' => 21,            
                'post_type' => array( 'movies' ) // This is the line that allows to fetch multiple post types. 
            )
        ); 
        // Loop through the posts and push the desired data to the array we've initialized earlier in the form of an object
        foreach( $posts as $post ) {
            $id = $post->ID; 
            $post_thumbnail = ( has_post_thumbnail( $id ) ) ? get_the_post_thumbnail_url( $id ) : null;

            $posts_data[] = (object) array( 
                'id' => $id, 
                'slug' => $post->post_name, 
                'type' => $post->post_type,
                'content' => $post->post_content,
                'title' => $post->post_title,
                'director' => $post->director,
                'maincast' => $post->maincast,
                'featured_img_src' => $post_thumbnail
            );
        }                  
        return $posts_data;                   
    } 













function replace_core_jquery_version() {
    wp_deregister_script( 'jquery-core' );
    wp_register_script( 'jquery-core', "https://code.jquery.com/jquery-3.1.1.min.js", array(), '3.1.1' );
    wp_deregister_script( 'jquery-migrate' );
    wp_register_script( 'jquery-migrate', "https://code.jquery.com/jquery-migrate-3.0.0.min.js", array(), '3.0.0' );
}

add_action( 'wp_enqueue_scripts', 'replace_core_jquery_version' );



// Scheduled Action Hook
// Set pulling of movies at 8PM
// calling a command on the server's cronjob to visit the site and run this task

// chromium-browser https://myjoroni.ml
function run_my_script() {
    date_default_timezone_set("Asia/Manila");
    $t=time();
      $time = date("H",$t);
    if ($time == '20') {
        echo '<div class="alert alert-success" role="alert">Fetching...</div>';
        add_action( 'init', 'fetchMovies' );
     }else{
       //  echo 'NO';
       echo '<div class="alert alert-success hidden" role="alert">Fetching...</div>';
     }
}

add_action( 'wp', 'run_my_script' );

 
