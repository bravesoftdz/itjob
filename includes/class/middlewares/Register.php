<?php
/**
 * Created by IntelliJ IDEA.
 * User: Tiafeno
 * Date: 16/08/2018
 * Time: 13:00
 */

trait Register {
  private function createCompanyRole() {
    $capabilities = array(
      'read'         => true,  // true allows this capability
      'upload_files' => true,

      'edit_others_pages' => true,
      'edit_others_posts' => true,
      'edit_pages'        => true,
      'edit_posts'        => true,
      'edit_users'        => true,

      'manage_options' => false,

      'remove_users' => false,

      'delete_others_pages'    => true,
      'delete_posts'           => false,
      'delete_pages'           => false,
      'delete_published_posts' => false,
      'delete_users'           => false,
      'delete_themes'          => false,
      'delete_plugins'         => false,

      'create_posts'      => true, // Allows user to create new posts
      'manage_categories' => true, // Allows user to manage post categories
      'publish_posts'     => true, // Allows the user to publish, otherwise posts stays in draft mode
      'edit_themes'       => false, // false denies this capability. User can’t edit your theme
      'install_plugins'   => false, // User cant add new plugins
      'update_plugin'     => false, // User can’t update any plugins
      'update_core'       => false, // user cant perform core updates
      'create_users'      => false,
      'install_themes'    => false,
    );

    return add_role(
      'company',
      'Entreprise',
      $capabilities
    );
  }

  private function createCandidateRole() {
    $capabilities = array(
      'read'                   => true,  // true allows this capability
      'upload_files'           => true,
      'edit_posts'             => true,
      'edit_users'             => true,
      'manage_options'         => false,
      'remove_users'           => false,
      'edit_others_posts'      => true,
      'delete_others_pages'    => true,
      'delete_published_posts' => true,
      'edit_others_posts'      => true, // Allows user to edit others posts not just their own
      'create_posts'           => true, // Allows user to create new posts
      'manage_categories'      => true, // Allows user to manage post categories
      'publish_posts'          => true, // Allows the user to publish, otherwise posts stays in draft mode
      'edit_themes'            => false, // false denies this capability. User can’t edit your theme
      'install_plugins'        => false, // User cant add new plugins
      'delete_plugins'         => false,
      'update_plugin'          => false, // User can’t update any plugins
      'update_core'            => false, // user cant perform core updatesy
      'create_users'           => false,
      'delete_themes'          => false,
      'install_themes'         => false,
    );

    return add_role(
      'candidate',
      'Candidat',
      $capabilities
    );
  }

  public function createRoles() {
    $this->createCandidateRole();
    $this->createCompanyRole();
  }

  protected function registerPostTypes() {
    register_post_type( 'offers', [
      'label'           => "Les offres",
      'labels'          => [
        'name'               => "Les offres",
        'singular_name'      => "Offre",
        'add_new'            => 'Ajouter',
        'add_new_item'       => "Ajouter une nouvelle offre",
        'edit_item'          => 'Modifier',
        'view_item'          => 'Voir',
        'search_items'       => "Trouver des offres",
        'all_items'          => "Tous les offres",
        'not_found'          => "Aucune offre trouver",
        'not_found_in_trash' => "Aucune offre dans la corbeille"
      ],
      'public'          => true,
      'hierarchical'    => false,
      'menu_position'   => null,
      'show_ui'         => true,
      'has_archive'     => true,
      'rewrite'         => [ 'slug' => 'offers' ],
      'capability_type' => 'post',
      'menu_icon'       => 'dashicons-businessman',
      'supports'        => [ 'title', 'excerpt', 'thumbnail', 'custom-fields' ],
      'show_in_rest'    => true
    ] );

    register_post_type( 'company', [
      'label'           => "Entreprises",
      'labels'          => [
        'name'               => "Les entreprises",
        'singular_name'      => "Entreprise",
        'add_new'            => 'Ajouter',
        'add_new_item'       => "Ajouter une nouvelle entreprise",
        'edit_item'          => 'Modifier',
        'view_item'          => 'Voir',
        'search_items'       => "Trouver des entreprises",
        'all_items'          => "Tous les entreprises",
        'not_found'          => "Aucune entreprise trouver",
        'not_found_in_trash' => "Aucune entreprise dans la corbeille"
      ],
      'public'          => true,
      'hierarchical'    => false,
      'menu_position'   => null,
      'show_ui'         => true,
      'has_archive'     => true,
      'rewrite'         => [ 'slug' => 'company' ],
      'capability_type' => 'post',
      'menu_icon'       => 'dashicons-welcome-widgets-menus',
      'supports'        => [ 'title', 'excerpt', 'thumbnail', 'custom-fields' ],
      'show_in_rest'    => true
    ] );

    register_post_type( 'candidate', [
      'label'           => "Candidate",
      'labels'          => [
        'name'               => "Les candidats",
        'singular_name'      => "Candidat",
        'add_new'            => 'Ajouter',
        'add_new_item'       => "Ajouter une nouvelle candidate",
        'edit_item'          => 'Modifier',
        'view_item'          => 'Voir',
        'search_items'       => "Trouver des candidats",
        'all_items'          => "Tous les candidats",
        'not_found'          => "Aucun candidat trouver",
        'not_found_in_trash' => "Aucun candidat dans la corbeille"
      ],
      'public'          => true,
      'hierarchical'    => false,
      'menu_position'   => null,
      'show_ui'         => true,
      'has_archive'     => true,
      'rewrite'         => [ 'slug' => 'candidate' ],
      'capability_type' => 'post',
      'menu_icon'       => 'dashicons-welcome-widgets-menus',
      'supports'        => [ 'title', 'excerpt', 'thumbnail', 'custom-fields' ],
      'show_in_rest'    => true
    ] );

  }
  protected function registerTaxonomy() {

    // Now register the taxonomy (Secteur d'activité)
    register_taxonomy( 'branch_activity', [ 'company', 'candidate' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Secteur d\'activité',
        'singular_name'     => 'Secteur d\'activité',
        'search_items'      => 'Trouver une secteur d\'activité',
        'all_items'         => 'Trouver des secteur d\'activités',
        'parent_item'       => 'Activité parent',
        'parent_item_colon' => 'Activité parent:',
        'edit_item'         => 'Modifier l\'activité',
        'update_item'       => 'Mettre à jour l\'activité',
        'add_new_item'      => 'Ajouter une nouvelle activité',
        'menu_name'         => 'Secteur d\'activité',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'branch_activity' ),
    ] );

    // Now register the taxonomy (Emploi)
    register_taxonomy( 'job_sought', [ 'candidate' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Emplois',
        'singular_name'     => 'Emploi',
        'search_items'      => 'Trouver une emploi',
        'all_items'         => 'Trouver des emplois',
        'parent_item'       => 'Emploi parent',
        'parent_item_colon' => 'Emploi parent:',
        'edit_item'         => 'Modifier l\'emploi',
        'update_item'       => 'Mettre à jour l\'emploi',
        'add_new_item'      => 'Ajouter un emploi',
        'menu_name'         => 'Emplois',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'emploi' ),
    ] );

    // Now register the taxonomy (Logiciel maitrisés)
    register_taxonomy( 'software', [ 'candidate' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Logiciels',
        'singular_name'     => 'Logiciel',
        'search_items'      => 'Trouver un logiciel',
        'all_items'         => 'Trouver des logiciels',
        'parent_item'       => 'Logiciel parent',
        'parent_item_colon' => 'Logiciel parent:',
        'edit_item'         => 'Modifier le logiciel',
        'update_item'       => 'Mettre à jour le logiciel',
        'add_new_item'      => 'Ajouter un logiciel',
        'menu_name'         => 'Logiciels',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'software' ),
    ] );

    // Now register the taxonomy (Région)
    register_taxonomy( 'region', [ 'offers', 'candidate', 'company' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Région',
        'singular_name'     => 'Région',
        'search_items'      => 'Trouver une région',
        'all_items'         => 'Trouver des région',
        'parent_item'       => 'Région parent',
        'parent_item_colon' => 'Région parent:',
        'edit_item'         => 'Modifier la région',
        'update_item'       => 'Mettre à jour la région',
        'add_new_item'      => 'Ajouter une nouvelle région',
        'menu_name'         => 'Région',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'region' ),
    ] );

    // Now register the taxonomy (Langage)
    register_taxonomy( 'language', [ 'candidate' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Langage',
        'singular_name'     => 'Langage',
        'search_items'      => 'Trouver un langage',
        'all_items'         => 'Trouver des langage',
        'parent_item'       => 'Langage parent',
        'parent_item_colon' => 'Langage parent:',
        'edit_item'         => 'Modifier le langage',
        'update_item'       => 'Mettre à jour le langage',
        'add_new_item'      => 'Ajouter un nouveau langage',
        'menu_name'         => 'Langage',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'langage' ),
    ] );

    // Now register the taxonomy (Tag)
    register_taxonomy( 'itjob_tag', [ 'offers', 'candidate' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Étiquettes',
        'singular_name'     => 'Étiquette',
        'search_items'      => 'Trouver une étiquette',
        'all_items'         => 'Trouver des Étiquettes',
        'parent_item'       => 'Étiquette parent',
        'parent_item_colon' => 'Étiquette parent:',
        'edit_item'         => 'Modifier l\'étiquette',
        'update_item'       => 'Mettre à jour l\'étiquette',
        'add_new_item'      => 'Ajouter une nouvelle étiquette',
        'menu_name'         => 'Étiquettes',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'tag' ),
    ] );

    // Now register the taxonomy (City)
    register_taxonomy( 'city', [ 'offers', 'candidate', 'company' ], [
      'hierarchical'      => true,
      'labels'            => array(
        'name'              => 'Code postal & Ville',
        'singular_name'     => 'Code postal & Ville',
        'search_items'      => 'Trouver une code postal ou des villes',
        'all_items'         => 'Trouver des villes ou code postal',
        'parent_item'       => 'Parent',
        'parent_item_colon' => 'Parent:',
        'edit_item'         => 'Modifier',
        'update_item'       => 'Mettre à jour ',
        'add_new_item'      => 'Ajouter une nouvelle',
        'menu_name'         => 'Code postal & Ville',
      ),
      'show_ui'           => true,
      'show_admin_column' => false,
      'query_var'         => true,
      'public'            => true,
      'show_in_rest'      => true,
      'rewrite'           => array( 'slug' => 'postal_code__city' ),
    ] );

  }

  public function initRegister() {
    $this->registerPostTypes();
    $this->registerTaxonomy();
  }
}