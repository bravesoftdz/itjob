<?php

class cronModel
{
    private $wpdb;
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = &$wpdb;

    }

    public function getFeaturedOffers()
    {
        $results = [];
        $args = [
            'post_type' => 'offers',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => 'itjob_offer_featured',
                    'value' => 1,
                    'compare' => '='
                ]
            ]
        ];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $results[] = new includes\post\Offers(get_the_ID());
            endwhile;
        }

        return $results;
    }

    public function getUserAdmin() {
        $args = [
            'role__in' => ['administrator', 'editor', 'contributor']
        ];
        $user_query = new WP_User_Query($args);
        return $user_query;
    }

    /**
     * Recuperer les offres 5 jours avants 
     */
    public function getOffer5DaysLimitDate() {
      global $wpdb;
        $today = date('Y-m-d H:i:s');
        $todayDatetime = new DateTime($today);
        $date5Days = new DateTime("$today +5 day");
        $date5DaysFormat = $date5Days->format('Ymd');
        $todayFormat = $todayDatetime->format('Ymd');
        $sql = "SELECT pts.ID as offer_id FROM $wpdb->posts as pts 
                    WHERE 
                    pts.post_type = 'offers'
                    AND pts.post_status = 'publish'
                    AND pts.ID IN (SELECT pm.post_id as post_id 
                        FROM {$wpdb->postmeta} as pm
                        WHERE pm.meta_key REGEXP '(^itjob_offer_datelimit)$' AND pm.meta_value BETWEEN '$todayFormat' AND '$date5DaysFormat' )";
        $rows = $wpdb->get_results($sql);

        return $rows;
    }

    /**
     * Effacer les notifications
     */
    public function deleteNoticeforLastDays($day = 5, $users = []) {
        global $wpdb;
        $today = date('Y-m-d H:i:s');
        $lastDay = new DateTime("$today - $day day");
        $lastDayString = $lastDay->format('Y-m-d H:i:s');
        $query = '';
        foreach ($users as $user) {
            $endEl = end($users);
            $query .= "$user->ID";
            if ($endEl->ID !== $user->ID) $query .= ', ';
        }
        $sql = "DELETE FROM {$wpdb->prefix}notices WHERE date_create <= CAST('$lastDayString' AS DATETIME) AND id_user IN ( $query )";
        $rows = $wpdb->query( $sql );
        
        return $rows;
    }
    
    /**
     * Récupérer les CV en attente de modifications
     */
    public function getPendingEditingCV() {
        global $wpdb;
        $return = [];
        $sql = "SELECT * FROM $wpdb->posts pts WHERE pts.post_type = %s AND pts.post_status = %s
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key REGEXP '^itjob_cv_experiences_[0-9]{1,2}_validated' AND pta.meta_value != 1)
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key = 'itjob_cv_hasCV' AND pta.meta_value = 1)
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key = 'activated' AND pta.meta_value = 1)";
        $prepare = $wpdb->prepare($sql , 'candidate', 'publish');
        $rows    = $wpdb->get_results( $prepare );
        foreach ($rows as $candidate) {
            // Vérifier si l'utilisateur est un candidat
            $Candidate = new includes\post\Candidate((int) $candidate->ID);
            $pending = false;
            
            // On verifie si le candidat a une modification en attente
            $Experiences = $Candidate->experiences;
            $Formations  = $Candidate->trainings;
            foreach ($Experiences as $experience) {
                if (!$experience->validated) $pending = true;
            }

            foreach ($Formations as $formation) {
                if (!$formation->validated) $pending = true;
            }

            if (!$pending) continue;
            $name = $Candidate->getFirstName().' '.$Candidate->getLastName();
            $return[] = ['reference' => $Candidate->reference, 'name' => $name];
        }

        return $return;
    }

    /**
     * Récupérer les informations que les entreprises s'interresent
     * qui sont en attente
     */
    public function getPendingInterest() {
        global $wpdb;
        $interest_tb =  $wpdb->prefix . 'cv_request';
        $prepare = $wpdb->prepare( "SELECT * FROM $interest_tb WHERE type = %s AND status = %s", 'interested', 'pending' );
        $rows    = $wpdb->get_results( $prepare );
        $infos = [];
        foreach ($rows as $row) {
            $infos[] = (object)[
                'candidate' => new includes\post\Candidate( (int) $row->id_candidate ),
                'company'   => new includes\post\Company( (int) $row->id_company ),
                'offer'     => new includes\post\Offers( (int) $row->id_offer ),
                'date'      => $row->date_add
            ];
        }
        return $infos;
    }

    /**
     * Récupérer les informations postulant encore en attente sur des offres 
     */
    public function getPendingApply() {
        global $wpdb;
        $interest_tb =  $wpdb->prefix . 'cv_request';
        $prepare = $wpdb->prepare( "SELECT * FROM $interest_tb WHERE type = %s AND view = %d AND status = %s", 'apply', 0, "pending" );
        $rows    = $wpdb->get_results( $prepare );

        $infos = [];
        foreach ($rows as $row) {
            $infos[] = (object)[
                'candidate' => new includes\post\Candidate( (int) $row->id_candidate ),
                'offer'     => new includes\post\Offers( (int) $row->id_offer ),
                'date'      => $row->date_add
            ];
        }
        return $infos;
    }

    public function getPendingCV() {
        global $wpdb;
        $return = [];
        $sql = "SELECT * FROM $wpdb->posts pts WHERE pts.post_type = %s AND pts.post_status = %s
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key = 'itjob_cv_hasCV' AND pta.meta_value = 1)
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key = 'activated' AND pta.meta_value = 0)";
        $prepare = $wpdb->prepare($sql , 'candidate', 'pending');
        $candidates = $wpdb->get_results( $prepare );
        foreach ($candidates as $candidate) {
            // Vérifier si l'utilisateur est un candidat
            $Candidate = new includes\post\Candidate((int) $candidate->ID);
            $name = $Candidate->getFirstName() . ' ' . $Candidate->getLastName();
            $return[] = ['reference' => $Candidate->reference, 'name' => $name, 'ID' => $Candidate->getId()];
        }

        return $return;
    }

    public function getPendingOffer() {
      global $wpdb;
      $return = [];
      $sql = "SELECT * FROM $wpdb->posts pts WHERE pts.post_type = %s AND pts.post_status = %s
        AND pts.ID IN (SELECT pta.post_id as post_id FROM $wpdb->postmeta pta WHERE pta.meta_key = 'activated' AND pta.meta_value = 0)";
      $prepare = $wpdb->prepare($sql , 'offers', 'pending');
      $offers = $wpdb->get_results( $prepare );
      foreach ($offers as $offer) {
        // Vérifier si l'utilisateur est un candidat
        $Offer = new includes\post\Offers((int) $offer->ID);
        $return[] = ['reference' => $Offer->reference, 'title' => $Offer->postPromote, 'ID' => $Offer->ID];
      }

      return $return;
    }

  /**
   * Cette fonction récupére les candidats qui n'on pas encore postuler sur une offre
   */
    public function getCandidatsNotApplied() {
      global $wpdb;
      $sql = <<<SQL
SELECT pts.ID, pts.post_title as reference FROM $wpdb->posts as pts WHERE pts.post_type = "candidate" 
AND pts.post_status = "publish"
AND pts.ID NOT IN (SELECT pm.post_id as ID FROM $wpdb->postmeta as pm WHERE pm.meta_key = "itjob_cv_offer_apply" AND pm.meta_value != "")
SQL;
      $results = $wpdb->get_results($sql);
      $candidats = [];
      foreach ($results as $result):
        $candidats[] = new \includes\post\Candidate($result['ID']);
      endforeach;

      return $candidats;

    }

    /**
     * Cette fonction permet de récuperer les candidats qui n'ont pas postuler depuis long temp
     */
    public function getCandidatsNotAppliedLongTime() {
      global $wpdb;
      $days = "5 days";
      $sql = <<<SQL
SELECT id_candidate, id_offer, max(date_add) as date_create FROM {$wpdb->prefix}cv_request
WHERE type = 'apply'
GROUP BY id_candidate
HAVING COUNT(*) > 0
SQL;
      $requests = $wpdb->get_results($sql);
      foreach ($requests as $request) {

      }

    }

    /**
     * Cette fonction permet de récuperer les utilisateurs particuliers qui n'on pas de CV
     */
    public function getCandidatsNoCV() {
      global $wpdb;

      $sql = <<<SQL
SELECT pts.ID, pts.post_title as reference FROM $wpdb->posts as pts WHERE pts.post_type = "candidate" 
AND pts.post_status = "publish"
AND pts.ID IN (SELECT pm.post_id as ID FROM $wpdb->postmeta as pm WHERE pm.meta_key = "activated" AND pm.meta_value = 1)
AND pts.ID NOT IN (SELECT pm2.post_id as ID FROM $wpdb->postmeta as pm2 WHERE pm2.meta_key = "itjob_cv_hasCV" AND pm2.meta_value = 1)
SQL;
      $results = $wpdb->get_results($sql);
      $candidats = [];
      foreach ($results as $result):
        $candidats[] = new \includes\post\Candidate($result['ID']);
      endforeach;

      return $candidats;
    }
}