{{--
  Template Name: HomePage
--}}

@extends('layouts.app')

@section('content')
  @include('partials.page-header')
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
  
  @import url('https://fonts.googleapis.com/css?family=Roboto');
  </style>
 
  <div class="album py-5 bg-light">
    <div class="container">
    <div id="spinner" class="hidden"><img src="./wp-content/themes/joroni-theme/resources/assets/images/spinner.gif" height="50"></div>
      <div id="show-data" class="row">
        
      </div>
    </div>
  </div>

@endsection

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>


<script>

$(document).ready(function () {
  
  $("#spinner").toggleClass("hidden");
    $.ajax({
       // url: "./index.php/wp-json/custom/v1/all-posts",
         url: "./wp-json/custom/v1/all-posts",
        method: "GET",
        dataType: 'json',
        success: function(data) {
          console.log(data);
          var html_to_append = '';
          $.each(data, function(i, item) {
            html_to_append +=
            '<div class="col-md-4">'+
            '<div class="card mb-4 shadow-sm">'+
             // '<img  src="' +item.featured_img_src +'" class="bd-placeholder-img card-img-top" width="100%" height="225"/>'+
              '<title>'+item.title + '</title>'+
              '<div class="card-body">'+
              '<p class="synopsis card-text">' +item.content +'</p>'+
              '<p class="title card-text"><label>Title: </label> ' +item.title  +'</p>'+
                '<p class="director card-text"><label>Director: </label> ' +item.director +'</p>'+
                '<p class="card-text maincast"><label>Main Cast: </label> ' +item.maincast +'</p>'+
                '<div class="d-flex justify-content-between align-items-center">'+
                  '<div class="btn-group">'+
                    '<a href="'+item.slug+'" class="button btn btn-sm btn-outline-secondary">View</a>'+
                  '</div>'+
                  '<small class="text-muted">'+item.slug.slice(0,10)+'</small>'+
                '</div>'+
              '</div>'+
          '</div>'+
          '</div>';
          });
          $("#show-data").html(html_to_append);
          $("#spinner").toggleClass("hidden");
        },
        error: function() {
          console.log(data);
          $("#show-data").html('');
        }
      });

      if ($(".synopsis")[0]){
        $('.synopsis > img').on('mouseover',function(){
          $(this).attr('href','');
        });
      } else {
        // Do something if class does not exist
      }


});




</script>