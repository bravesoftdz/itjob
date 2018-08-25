<?php

namespace includes\post;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

use includes\object as Object;

final class Company implements \iCompany {
  // Added Trait Class
  use \Auth;

  public $ID;
  public $userAuthor;
  public $email;
  public $title;
  public $address;
  public $nif;
  public $stat;
  public $phones = array();
  public $newsletter = false;
  public $notification = false;

  /**
   * Company constructor.
   *
   * @param int $postId - ID du post de type 'company'
   */
  public function __construct( $postId ) {
    if ( is_null( get_post( $postId ) ) ) {
      return null;
    }

    /**
     * @func get_post
     * (WP_Post|array|null) Type corresponding to $output on success or null on failure.
     * When $output is OBJECT, a WP_Post instance is returned.
     */
    $output = get_post( $postId );
    if ( is_null( $output ) ) {
      return false;
    }
    $this->ID         = $output->ID;
    $this->title      = $output->post_title;

    if ( $this->is_company() ) {
      // FIX: Corriger une erreur sur l'utilisateur si l'admin ajoute une company
      $this->email = get_field( 'itjob_company_email', $this->ID );
      $user        = get_user_by( 'email', trim( $this->email ) ); // WP_User

      // TODO: Ajouter ou crée un utilisateur quand un entreprise est publier ou ajouter
      $this->userAuthor = Object\jobServices::getUserData( $user->ID );
      $this->acfElements();
    }
  }

  public function is_company() {
    return get_post_type( $this->ID ) === 'company';
  }

  private function acfElements() {
    global $wp_version;
    if ( ! function_exists( 'get_field' ) ) {
      _doing_it_wrong( 'get_field', 'Function get_field n\'existe pas', $wp_version );

      return false;
    }
    $this->address      = get_field( 'itjob_company_address', $this->ID );
    $this->nif          = get_field( 'itjob_company_nif', $this->ID );
    $this->stat         = get_field( 'itjob_company_stat', $this->ID );
    $this->phones       = get_field( 'itjob_company_phones', $this->ID );
    $this->newsletter   = get_field( 'itjob_company_newsletter', $this->ID );
    $this->notification = get_field( 'itjob_company_notification', $this->ID );

    return true;
  }


  /**
   * @param int $paged
   * @param array $order
   *
   * @return array - Un tableau qui contient des objets'Company'
   */
  public static function getAllCompany( $paged = - 1 ) {
    $allCompany = [];
    $args       = [
      'post_type'      => 'company',
      'posts_per_page' => $paged,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC'
    ];
    $posts      = get_posts( $args );
    foreach ( $posts as $post ) : setup_postdata( $post );
      array_push( $allCompany, new self( $post->ID ) );
    endforeach;
    wp_reset_postdata();

    return $allCompany;
  }


  public function updateCompany() {
    // TODO: Implement updateCompany() method.
  }

  public function removeCompany() {
    // TODO: Implement removeCompany() method.
  }
}