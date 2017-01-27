<?php global $helpdesk; ?>
<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <header>
      <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>
    <?php 
    if ( has_post_thumbnail() ) {
        the_post_thumbnail();
    } 
    ?>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
    <footer>
      <?php get_template_part('templates/entry-meta'); ?>
    </footer>
    <?php comments_template( '/templates/comments.php' ); ?>
  </article>
<?php endwhile; ?>
