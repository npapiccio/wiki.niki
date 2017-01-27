<?php global $helpdesk; ?>
<div class="headline">
  <div class="container">
    <div class="row">
      <div class="col-md-7 hidden-xs hidden-sm">
        <?php //breadcrumbs(); ?>
        <?php if ($helpdesk['headline_search']) { ?>
          <?php get_template_part('templates/search', 'form'); ?>
        <?php } ?>
      </div>
      <div class="col-md-5">
      </div>
    </div>
  </div>
</div>