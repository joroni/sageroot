<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
  <style>
  @import './wp-content/themes/joroni-theme/resources/assets/styles/main.css';
  .hidden {
    display: none;
  }
  #spinner{
  background:rgba(245,245,245,0.8);
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  text-align: center;
  padding-top:20%;
  z-index: 100000;
}

body{
  background:#000;
  color:#fff;
}
  
  @import url('https://fonts.googleapis.com/css?family=Roboto');
  </style>
@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @include('partials.content-single-'.get_post_type())
  @endwhile
@endsection
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>

  $(document).ready(function () {
    $('article.movies .entry-content').wrap("<div class='movieentry row'/>")
    $('article.movies .entry-content').find('p').unwrap().wrap("<div class='col-4'/>");
    $('.movieentry div:nth-child(2)').css('display', 'none');
  })
 
  </script>