<?
add_action ( 'init', function(){

    // break time in the demo... run on the front and watch the posts pile up
   if ( is_home() && is_admin() ) return;
  
    // Create posts from remote data?
    $to_create_posts = true;
  
    // Load the feed
    $data = RemoteMovies::LoadRemoteMovies( $to_create_posts );
  
  });
  ?>