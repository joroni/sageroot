<?php
/*
 Template Name: Movies
 */


/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */


get_header();

require(dirname(__FILE__)."\data.php");
?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">

        <script src="https://use.fontawesome.com/5e08b41830.js"></script>
            <ul id="sliders" class="cd-hero__slider">
            <?php
$query = new WP_Query( array( 'category_name' => 'slider-item' ) );
if ( $query->have_posts() ) :
    while ( $query->have_posts() ) : $query->the_post();
    ?> 
   <li class="cd-hero__slide <?php (0 == $i ? "cd-hero__slide--selected" : "") ?> js-cd-slide">
    <div class="cd-hero__content cd-hero__content--full-width" style="pointer-events: none;">
  <h2><?php
        the_title();
        ?>
        </h2>
        <p><?php the_content(); ?></p>
        </div></li> <?php
    endwhile;
else :
    echo 'No posts';
endif;
?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();

