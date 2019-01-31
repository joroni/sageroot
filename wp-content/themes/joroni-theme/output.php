<?php

if (!class_exists('RemoteArtwork')):
    class RemoteArtwork
    {
        // original data
        public $raw;
        
        // public items
        public $title;
        public $artwork_html;
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
                $data_as_array[$inx] = sanitize_text_field($data);
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
            $this->title        = $this->raw['_title'];
            $this->artwork_html = $this->raw['artwork_html'];
            $this->id           = $this->raw['id'];
        }
        
        /**
         * Update a post with the latest meta data
         */
        public function update_artwork_post_meta($post_id)
        {
            $meta_values_to_set = array(
                '__artwork_dimensions' => $this->raw['dimensions']
            );
            
            foreach ($meta_values_to_set as $key => $meta_value) {
                $success = $this->update_meta($post_id, $key, $meta_value);
            }
        }
        
        public function update_meta($post_id, $key, $meta_value)
        {
            if (isset($meta_value) && 0 < strlen(trim($meta_value))) {
                return update_post_meta($post_id, $key, $meta_value);
            }
            return false;
        }
        
        public function get_meta($post_id, $key)
        {
            $meta_value = get_post_meta($post_id, $key, true);
            return empty($value) ? null : $meta_value;
        }
        
    } // RemoteArtwork
endif; // RemoteArtwork


if (!class_exists('RemoteArtworks')):
    class RemoteArtworks
    {
        /** data from url */
        public static $remote_data;
        
        /** parsed data -- use these instances for stuffs */
        public static $artworks;
        
        /**
         * Pull the main feed & extra pages
         */
        private static function GetRemoteArtworks($url)
        {
            // remote data
            $response = wp_remote_get($url);
            
            if (is_wp_error($response) || !isset($response['body']))
                return; // bad response
            
            // the good stuff
            $body = wp_remote_retrieve_body($response);
            
            if (is_wp_error($body))
                return; // bad body
            
            // decode the data
            $data = json_decode($body, true);
            
            if (!$data || empty($data))
                return; // bad data
            
            // make sure there isn't anymore
            if (isset($data['page']) && isset($data['no_of_pages'])) {
                $page           = $data['page'];
                $pages          = $data['no_of_pages'];
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
        public static function LoadRemoteArtworks($to_create_posts)
        {
            self::$remote_data = self::GetRemoteArtworks('http://feeds.artlogic.net/artworks/artlogiconline/json/');
            
            if (!isset(self::$remote_data['rows']))
                return; // no rows --- booo!
            
            $works = array();
            foreach (self::$remote_data['rows'] as $inx => $remote_item_data) {
                // create artwork instance
                $artwork = new RemoteArtwork($remote_item_data);
                $works[] = $artwork;
            }
            
            // set our local data that has been cleaned and parsed
            self::$artworks = $works;
            
            // let's make new POSTS!!!
            if ($to_create_posts) {
                $count = 0;
                foreach (self::$artworks as $inx => $artwork) {
                    $count++; // for demo ---- let's not go overboard here
                    
                    $post_id = self::CreateLocalArtworkPost($artwork);
                    
                    if ($post_id == -1) {
                        // Nothing happened
                    } else if ($post_id == -2) {
                        // already exists
                        // ... I guess we could update a post's data here...
                    } else {
                        // new post was created, let's update it's meta
                        $success = $artwork->update_artwork_post_meta($post_id);
                    }
                    ///////////////////////////////////////////////////
                    ///////////////////////////////////////////////////
                    if ($count >= 5)
                        return self::$remote_data; ///////
                    ///////////////////////////////////////////////////
                    /////////////////// FOR DEMO... DON'T DO EVERY POST
                    ///////////////////////////////////////////////////
                    ////////////////////////////////// BUT YOU COULD...
                    ///////////////////////////////////////////////////
                    ///////////////////////////////////////////////////
                }
            }
            
            // return the remote data... for whatever reason outsite of this class
            return self::$remote_data;
        }
        
        public static function CreateLocalArtworkPost($artwork)
        {
            if (!$artwork)
                return -1;
            
            ///////////////////////////////////////////////////
            ///////////////////////////////////////////////////
            // DEBUG
            // echo '<pre>';
            // print_r( $artwork );
            // echo '</pre>';
            ///////////////////////////////////////////////////
            ///////////////////////////////////////////////////
            
            // do post creation from $artwork data that has been sanitized
            
            $template = ''; // add a custom template if you want
            
            $post_type = 'post';
            
            $img_url      = $artwork->raw['img_url'];
            $artwork_html = $artwork->artwork_html;
            
            $content = "<p>${artwork_html}</p>\n\r<br />\r\n<a target=\"_blank\" href=\"${img_url}\"><img width=\"100%\" height=\"auto\" src=\"${img_url}\"></a>";
            
            $new_post_id = self::CreateNewArtworkPost($post_type, $artwork->title, $content, $template, $artwork);
            
            return $new_post_id;
        }
        
        /**
         * orginal from - https://tommcfarlin.com/programmatically-create-a-post-in-wordpress/
         */
        public static function CreateNewArtworkPost($post_type = 'post', $title, $content, $template_rel_path = '', $data)
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
            
            $title     = sanitize_text_field(wp_strip_all_tags($title)); // remove any junk
            $title     = esc_html(wp_unslash($title));
            $slug      = sanitize_title_with_dashes($title); // converts to a usable post_name
            $post_type = post_type_exists($post_type) ? $post_type : 'post'; // make sure it exists
            
            // If the page doesn't already exist, then create it (by title & slug)
            if (null == get_page_by_title($title) && empty(get_posts(array(
                'name' => $slug
            )))) {
                
                // Set the post ID so that we know the post was created successfully
                $post_id = wp_insert_post(array(
                    'post_name' => $slug,
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_type' => $post_type,
                    'post_author' => $author_id,
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_status' => 'publish'
                ));
                
                if ($post_id && $post_id > 0 && !empty($template_rel_path)) {
                    
                    // make sure the template exists
                    $template_full_path = trailingslashit(get_stylesheet_directory()) . $template_rel_path;
                    if (file_exists($template_full_path)) {
                        
                        // set the post meta data -- use relative path
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
        
    } // RemoteArtworks
endif; // RemoteArtworks