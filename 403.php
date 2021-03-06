<?php
get_header();
?>
<style type="text/css">
  .error-content {
    max-width: 620px;
    margin: 0px auto 0;
  }

  .error-icon {
    height: 160px;
    width: 160px;
    background-image: url(<?= get_template_directory_uri() ?>/img/user-lock.svg);
    background-size: cover;
    background-repeat: no-repeat;
    margin-right: 80px;
  }

  .error-code {
    font-size: 120px;
    color: #5c6bc0;
  }
</style>
  <div class="uk-section uk-section-transparent">

    <div class="uk-container uk-container-medium">
      <div class="error-content flexbox">
        <span class="error-icon"></span>
        <div class="flex-1">
          <h1 class="error-code">403</h1>
          <h3 class="font-strong">FORBIDDEN</h3>
          <p class="mb-4">You don't have permission to access.</p>
          <div>
            <a class="text-primary" href="<?= home_url('/') ?>">Accueil</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
<?php
get_footer();