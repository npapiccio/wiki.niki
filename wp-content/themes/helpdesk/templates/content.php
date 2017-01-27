<article <?php post_class(); ?>>
  <header>
    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
  </header>
    <?php 
    if ( has_post_thumbnail() ) {
        the_post_thumbnail();
    } 
    ?>
  <div class="entry-summary">
    <?php pa_excerpt(30); ?>
  </div>
</article>
