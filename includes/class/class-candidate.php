<?php

namespace includes\post;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

use includes\object as Obj;

final class Candidate extends UserParticular implements \iCandidate {
  // Added Trait Class
  use \Auth;

  private $activated;
  public $title;
  public $candidate_url;
  public $reference;
  public $region; // Region
  public $status; // Je cherche...
  public $driveLicences; // A, B, C & A`
  public $jobSought;
  public $languages = [];
  public $softwares = [];
  public $trainings = [];
  public $experiences = [];
  public $centerInterest;
  public $jobNotif = false;
  public $trainingNotif = false;
  public $newsletter;
  public $branch_activity;
  public $tags;
  public $avatar;

  /**
   * Candidate constructor.
   *
   * @param int $postId - ID post 'candidate' type
   */
  public function __construct( $postId = null ) {
    if ( is_null( $postId ) ) {
      return false;
    }
    /**
     * @func get_post
     * (WP_Post|array|null) Type corresponding to $output on success or null on failure.
     * When $output is OBJECT, a WP_Post instance is returned.
     */
    $output = get_post( (int) $postId );
    if ( is_null( $output ) ) {
      return false;
    }
    $this->setId( $output->ID );
    // Initialiser l'utilisateur particulier
    parent::__construct();

    $this->title    = $this->reference = $output->post_title;
    $this->candidate_url = get_the_permalink($this->getId());
    $this->postType = $output->post_type;

    if ( $this->is_candidate() ) {
      $this->acfElements();
      $this->email      = get_field( 'itjob_cv_email', $this->getId() );
      $User             = get_user_by( 'email', $this->email );
      // Remove login information (security)
      $this->author = Obj\jobServices::getUserData( $User->ID );
      $this->avatar = wp_get_attachment_image_src( get_post_thumbnail_id( $this->getId() ), 'large' );

      // get Terms
      $this->fieldTax();
    }
  }

  public static function get_candidate_by( $value, $handler = 'user_id' ) {
    if ( $handler === 'user_id' ) {
      $usr        = get_user_by( 'id', (int) $value );
      $args       = [
        'post_type'      => 'candidate',
        'post_status'    => [ 'publish', 'pending' ],
        'posts_per_page' => - 1,
        'meta_key'       => 'itjob_cv_email',
        'meta_value'     => $usr->user_email,
        'meta_compare'   => '='
      ];
      $candidates = get_posts( $args );
      if ( empty( $candidates ) ) {
        return null;
      }
      $candidate = reset( $candidates );

      return new Candidate( $candidate->ID );
    } else {
      return false;
    }
  }

  /**
   * @return bool
   */
  public function is_candidate() {
    return $this->postType === 'candidate';
  }

  public function is_activated() {
    $activation = get_field('activated', $this->getId());
    return (bool)$activation;
  }

  public function is_publish() {
    $post_status = ['pending', 'draft', 'private', 'trash'];
    return !in_array($this->postType, $post_status) ? 1 : 0;
  }

  public function acfElements() {
    if ( ! function_exists( 'the_field' ) ) {
      return false;
    }
    $this->activated   = get_field( 'activated', $this->getId() );
    $this->status      = get_field( 'itjob_cv_status', $this->getId() );
    $this->trainings   = $this->acfRepeaterElements( 'itjob_cv_trainings', [
      'training_dateBegin',
      'training_dateEnd',
      'training_country',
      'training_city',
      'training_diploma',
      'training_establishment',
    ] );
    $this->experiences = $this->acfRepeaterElements( 'itjob_cv_experiences', [
      'exp_dateBegin',
      'exp_dateEnd',
      'exp_city',
      'exp_country',
      'exp_company',
      'exp_mission'
    ] );

    $this->centerInterest = $this->acfGroupField( 'itjob_cv_centerInterest', [ 'various', 'projet' ] );
    $this->newsletter     = get_field( 'itjob_cv_newsletter', $this->getId() );
    $this->driveLicences  = get_field( 'itjob_cv_driveLicence', $this->getId() );

    return true;
  }

  /**
   * Vérifier si le candidate est notifier pour les formations
   * @return bool
   */
  protected function getTrainingNotif() {
    $notif = get_field( 'itjob_cv_notifFormation', $this->getId() );
    if ( $notif ) {
      if ( $notif['notification'] ) {
        $this->trainingNotif = (object) [ 'branch_activity' => $notif['branch_activity'] ];
      }
    }
  }

  /**
   * Vérifier si le candidate est notifier pour les emplois publier
   * @return bool
   */
  protected function getJobNotif() {
    $notif = get_field( 'itjob_cv_notifEmploi', $this->getId() );
    if ($notif) {
      if ( $notif['notification'] ) {
        $this->jobNotif = [
          'branch_activity' => $notif['branch_activity'],
          'job_sought' => $notif['job_sought']
        ];
      }
    }
  }

  /**
   * Vérifier si le CV est le mien
   * Si oui, Ajouter mes notifications sur les emplois et formations
   */
  public function isMyCV() {
    $User = wp_get_current_user();
    if ($User->user_email === $this->email || is_user_admin()) {
      $this->getJobNotif();
      $this->getTrainingNotif();
      $this->cellphone = $this->getCellphone();
      $this->display_name = $this->get_display_name();
      $this->hasCV();
    }
  }

  /**
   * Récuperer les terms
   */
  private function fieldTax() {
    // Get postuled offer
    $this->postuled = get_field('itjob_cv_offer_apply', $this->getId());
    $this->languages       = wp_get_post_terms( $this->getId(), 'language', [ "fields" => "all" ] );
    // Get softwares
    $softwares             = wp_get_post_terms( $this->getId(), 'software', [ "fields" => "all" ] );
    $this->softwares       = $this->getActivateField( $softwares );
    // Get region
    $this->region        = wp_get_post_terms( $this->getId(), 'region', [ "fields" => "all" ] );
    $this->region        = $this->region[0];
    // Get region
    $this->country        = wp_get_post_terms( $this->getId(), 'city', [ "fields" => "all" ] );
    $this->country        = $this->country[0];
    // Récuperer les emplois recherché
    $jobSoughts            = wp_get_post_terms( $this->getId(), 'job_sought', [ "fields" => "all" ] );
    $this->jobSought       = $this->getActivateField( $jobSoughts );

    $this->tags            = wp_get_post_terms( $this->getId(), 'itjob_tag', [ "fields" => "names" ] );
    $this->branch_activity = wp_get_post_terms( $this->getId(), 'branch_activity', [ "fields" => "all" ] );
    $this->branch_activity = !is_array($this->branch_activity) || !empty($this->branch_activity) ? $this->branch_activity[0] : null;
  }

  private function getActivateField( $terms ) {
    $validTerms = [];
    if ( ! is_wp_error($terms)):
      foreach ( $terms as $term ) {
        if (! is_wp_error($term)) {
          $valid = get_term_meta( $term->term_id, 'activated', true );
          if ( $valid ) {
            array_push( $validTerms, $term );
          }
        }
      }
    endif;

    return $validTerms;
  }

  /**
   * @param null|string $rp - Groupe field name
   * @param array $subField
   *
   * @return array
   */
  private function acfRepeaterElements( $repeaterField = null, $subField = [] ) {
    if ( is_null( $repeaterField ) ) {
      return [];
    }
    $resolve = [];
    $rows    = get_field( $repeaterField, $this->getId() );
    if ( $rows ) {
      foreach ( $rows as $row ) {
        array_push( $resolve, (object) $row );
      }
    }

    return $resolve;
  }

  /**
   * @param null|string $group
   * @param array $fields
   *
   * @return array|stdClass
   */
  private function acfGroupField( $group = null, $fields = [] ) {
    if ( is_null( $group ) ) {
      return [];
    }

    $groupe = get_field( $group, $this->getId() );
    if ( $groupe ) {
      $resolve = new \stdClass();
      foreach ( $fields as $field ) {
        $resolve->{$field} = $groupe[ $field ];
      }

      return $resolve;
    } else {
      return [];
    }
  }

  public static function getAllCandidate( $paged = - 1 ) {
    $allCandidate = [];
    $args         = [
      'post_type'      => 'company',
      'posts_per_page' => $paged,
      'post_status'    => 'publish',
      'orderby'        => 'date',
      'order'          => 'DESC'
    ];

    $posts = get_posts( $args );
    foreach ( $posts as $post ) : setup_postdata( $post );
      array_push( $allCandidate, new self( $post->ID ) );
    endforeach;
    wp_reset_postdata();

    return $allCandidate;
  }

  public function remove() {
  }

  public function update() {
  }
}
