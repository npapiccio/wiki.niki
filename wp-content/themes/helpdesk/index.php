<?php get_template_part('templates/page', 'header'); ?>

<?php if (!have_posts()) : ?>
	<p class="no-results">
		<?php _e('There were no results for your search! Try another search or use the links in header to locate what you are looking for.', 'pressapps'); ?>
	</p>
	<div class="not-found">
		<?php get_template_part('templates/search', 'form'); ?>
	</div>
<?php endif; ?>

<?php while (have_posts()) : the_post(); ?>
  <?php get_template_part('templates/content', get_post_format()); ?>
<?php endwhile; ?>

<?php if ($wp_query->max_num_pages > 1) : ?>
  <?php page_navi(); ?>
<?php endif; ?>
