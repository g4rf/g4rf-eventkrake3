<?php
class Eventkrake {
    const COLOR_ERROR = '#F6CECE';
    const COLOR_NOERROR = '#D0F5A9';

    const MENU_POSITION_START = 5;
    private static $CURRENT_MENU_POSITION = 0;
    public static function getNextMenuPosition() {
        $step = 1;
        return self::MENU_POSITION_START
                + (self::$CURRENT_MENU_POSITION++ * $step);
    }

    public static function getSinglePostMeta($postId, $key) {
        return get_post_meta($postId, "eventkrake_$key", true);
    }
    public static function getPostMeta($postId, $key) {
        return get_post_meta($postId, "eventkrake_$key", false);
    }
    public static function setSinglePostMeta($postId, $key, $value) {
        // If the custom field already has a value
        if(get_post_meta($postId, "eventkrake_$key", false)) {
            update_post_meta($postId, "eventkrake_$key", $value);
        } else { // If the custom field doesn't have a value
            add_post_meta($postId, "eventkrake_$key", $value);
        }
    }
    public static function setPostMeta($postId, $key, $values) {
        delete_post_meta($postId, "eventkrake_$key");
        if(!$values) return;
        foreach($values as $v) {
            add_post_meta($postId, "eventkrake_$key", $v);
        }
    }

    /**
     * Gibt verfügbare Posts vom Typ Location aus (angelegte Orte).
     * @param bool $onlyPublic wenn true, nur veröffentlichte Posts, andernfalls
     *  auch Entwürfe und private Posts.
     * @return array Array von Posts des Typs eventkrake_location
     */
    public static function getLocations($onlyPublic = true) {
        $status = 'publish';
        if(! $onlyPublic) $status .= ',private,draft';
        return get_posts(array(
            'numberposts' => -1,
            'offset' => 0,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_type' => 'eventkrake_location',
            'post_status' => $status
        ));
    }

    /**
     * Gibt verfügbare Posts vom Typ Event an einer Location aus.
     * @param int $locationId Die ID der Location, für den die Events ausgegeben
     *  werden.
     * @param bool $onlyPublic wenn true, nur veröffentlichte Posts, andernfalls
     *  auch Entwürfe und private Posts.
     * @return array Array von Posts des Typs eventkrake_event
     */
    public static function getEvents($locationId, $onlyPublic = true) {
        $status = 'publish';
        if(! $onlyPublic) $status .= ',private,draft';
        return get_posts(array(
            'numberposts' => -1,
            'offset' => 0,
            'order' => 'ASC',
            'orderby' => 'meta_value',
            'meta_key' => 'eventkrake_start',
            'post_type' => 'eventkrake_event',
            'post_status' => $status,
            'meta_query' => array(
                array(
                    'key' => 'eventkrake_locationid',
                    'value' => $locationId
                )
            )
        ));
    }

    /**
     * Gibt verfügbare Posts vom Typ Event aus.
     * @param bool $onlyPublic wenn true, nur veröffentlichte Posts, andernfalls
     *  auch Entwürfe und private Posts.
     * @return array Array von Posts des Typs eventkrake_event
     */
    public static function getAllEvents($onlyPublic = true) {
        $status = 'publish';
        if(! $onlyPublic) $status .= ',private,draft';
        return get_posts(array(
            'numberposts' => -1,
            'offset' => 0,
            'order' => 'ASC',
            'orderby' => 'meta_value',
            'meta_key' => 'eventkrake_start',
            'post_type' => 'eventkrake_event',
            'post_status' => $status
        ));
    }

    /**
     * Gibt verfügbare Posts vom Typ Artist aus.
     * @param bool $onlyPublic wenn true, nur veröffentlichte Posts, andernfalls
     *  auch Entwürfe und private Posts.
     * @return array Array von Posts des Typs Artist
     */
    public static function getArtists($onlyPublic = true) {
        $status = 'publish';
        if(! $onlyPublic) $status .= ',private,draft';
        return get_posts(array(
            'numberposts' => -1,
            'offset' => 0,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_type' => 'eventkrake_artist',
            'post_status' => $status
        ));
    }

    /**
     * Gibt verfügbare Posts vom Typ Event aus, an denen der Artist teilnimmt.
     * @param int $artistId Die post id des Artist.
     * @param bool $onlyPublic wenn true, nur veröffentlichte Posts, andernfalls
     *  auch Entwürfe und private Posts.
     * @return array Array von Posts des Typs Event
     */
    public static function getEventsForArtist($artistId, $onlyPublic = true) {
        $events = array();
        foreach(Eventkrake::getAllEvents($onlyPublic) as $e) {
            if(in_array($artistId,
                    Eventkrake::getPostMeta($e->ID, 'artists'))) {
                $events[] = $e;
            }
        }
        return $events;
    }

    /**
     * Gibt verfügbare Event-Kategorien zurück.
     * @TODO collect categories from database
     * @return array Ein Array von Event-Kategorien.
     */
    public static function getCategories() {
        global $wpdb;

        $categories = [
            'Dating' => 0, 'Führung & Vortrag' => 0, 'Konzert' => 0,
            'Karneval & Fasching' => 0, 'Messe' => 0, 'Party & Feier' => 0,
            'Theater & Bühne' => 0, 'Kinder' => 0, 'Ausstellung & Lesung' => 0,
            'Markt' => 0, 'Volksfest' => 0, 'Freizeit & Ausflug' => 0,
            'Gesundheit' => 0, 'Klassik & Opern' => 0, 'Kurse & Seminare' => 0,
            'Musicals & Shows' => 0, 'Sport' => 0, 'Kino' => 0,
            'Bar & Kneipe' => 0, 'Restaurant & Buffett' => 0,
            'Diskussion & Podium' => 0, 'DJ' => 0, 'Tanz' => 0,
            'Sonstiges' => 0, 'Zirkus, Akrobatik & Jonglage' => 0];

        $usedCategories = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
                'eventkrake_categories'));

        foreach($usedCategories as $u) {
            if(isset($categories[$u])) {
                $categories[$u]++;
            } else {
                $categories[$u] = 1;
            }
        }

        natsort($categories);

        return array_slice(array_reverse(array_keys($categories)), 0, 40);
    }
    
    /**
     * Sorts an array of events ascending by start date.
     * @param array $events Events with index 'start'.
     * @return array The sorted events array.
     */
    public static function sortEvents($events) {
        // sort events
        usort($events, function($a, $b) {
            $aDate = $a['start'];
            $bDate = $b['start'];

            if(is_string($aDate)) $aDate = new DateTime($aDate);
            if(is_string($bDate)) $bDate = new DateTime($bDate);

            if($aDate < $bDate) return -1;
            if($aDate > $bDate) return 1;
            return 0;
        });
        
        return $events;
    }

    /**
     * Gibt Fragen für ein CAPTCHA zurück bzw. prüft ein Captcha.
     * @param string $challenge Die Frage.
     * @param string $response Die Antwort.
     * @return mixed Wenn die beiden Parameter weggelassen werden, gibt die
     *      Funktion eine Frage zurück. Zum Überprüfen muss diese Frage und die
     *      Antwort als Parameter übergeben werden. Falls die Antwort stimmt,
     *      wird true zurückgegeben, sonst false.
     */
    public static function humanChallenge($challenge = null, $response = null) {
        $challenges = array(
            'eins plus eins? (1, 2, 3, oder 4)' => '2',
            'Aufgabe: 4 + 7? (8, 10, 11, 15, 23)' => '11',
            'Abkürzung für Kilogramm? (mm, ml, kg, MB, t)' => 'kg',
            'Welche Farbe hat eine Zitrone? (blau, gelb, grün, rot)' => 'gelb',
            'Bitte 8x3NsQ3 eingeben!' => '8x3NsQ3',
            'Wie lautet der Vorname von Franz Beckenbauer?' => 'Franz',
            'Welches Wort hjkha passt nicht hinein?' => 'hjkha',
            'Welches der folgenden Worte ist keine Farbe? (rot grün Eis blau weiß)'
                => 'Eis'
        );

        if($challenge == null && $response == null) {
            // send question
            return array_rand($challenges);

        } elseif(array_key_exists($challenge, $challenges)) {
            // check the response
            $percent = 0;
            similar_text(
                strtolower($challenges[$challenge]),
                strtolower($response),
                $percent);
            return $percent > 80;

        }
        return false;
    }

    /**
     * @deprecated since version 3.0beta
     * Date display should be done on client side.
     * @param type $startDate
     * @param type $endDate
     * @param type $classes
     * @param type $removable
     */
    public static function printDatePeriodPicker($startDate, $endDate,
            $classes = '', $removable = true) {
        ?>
        <div class="eventkrake-dates <?=$classes?>">

            <?php if($removable) { ?>
                <a class="eventkrake-remove-date"
                   title="<?=__('Zeit entfernen', 'g4rf_eventkrake2')?>">❌</a>
            <?php } ?>

            <div class="eventkrake-date-start">
                <input class="eventkrake-machine-date" name="eventkrake_startdate[]"
                    value="<?=$startDate->format('Y-m-d')?>" type="hidden" />
                <input type="text"
                    value="<?=strftime('%A, %d. %B %Y', $startDate->format('U'))?>"
                    class="datepicker" readonly="readonly" /><?php
                Eventkrake::printTimePicker(
                    'eventkrake_starthour[]', 'eventkrake_startminute[]',
                    $startDate->format('H'), $startDate->format('i'));
                ?>
            </div>

            <div class="eventkrake-date-end">
                <span class="eventkrake-bold"><?=
                        __('bis', 'g4rf_eventkrake2')?></span>
                <input class="eventkrake-machine-date" name="eventkrake_enddate[]"
                    value="<?=$endDate->format('Y-m-d')?>" type="hidden" />
                <input type="text"
                    value="<?=strftime('%A, %d. %B %Y', $endDate->format('U'))?>"
                    class="datepicker" readonly="readonly" /><?php
                Eventkrake::printTimePicker(
                    'eventkrake_endhour[]', 'eventkrake_endminute[]',
                    $endDate->format('H'), $endDate->format('i'));
                ?>
            </div>

        </div>
        <?php
    }

    /**
     * @deprecated since version 3.0beta
     * Date display should be done on client side.
     * Erzeugt einen Timepicker.
     * @param string $nameHour Der Formular-Name der Stundenauswahl.
     * @param string $nameMin Der Formular-Name der Minutenauswahl.
     * @param int $selHour Die selektierte Stunde.
     * @param int $selMin Die selektierte Minute.
     */
    public static function printTimePicker($nameHour, $nameMin, $selHour = 0,
            $selMin = 0) { ?>
        <select name="<?=$nameHour?>"><?php
            for($i = 0; $i < 24; $i++) {
                $h = substr("0$i", -2); ?>
                <option value="<?=$h?>"<?=$selHour == $i ? ' selected' : ''?>>
                    <?=$h?>
                </option>
            <?php } ?>
        </select>:<select name="<?=$nameMin?>"><?php
            for($i = 0; $i < 60; $i+=1) {
                $m = substr("0$i", -2); ?>
                <option value="<?=$m?>"<?=$selMin == $i ? ' selected' : ''?>>
                    <?=$m?>
                </option>
            <?php } ?>
        </select>
    <?php }

    /**
     * @deprecated since version 3.0beta
     * We don't use datetime anymore.
     * Vergleicht zwei Arrays nach Datums- und Zeitangaben. Beide Arrays müssen
     * den assoziativen Index 'datetime' besitzen.
     * @param array $a Ein Array.
     * @param array $b Ein Array.
     * @return integer 0, wenn beide gleich sind, -1 wenn $a kleiner ist und 1
     * 		wenn $b kleiner ist.
     * @see uasort()
     */
    public static function compareDatetime($a, $b) {
        if($a['datetime'] == $b['datetime']) return 0;
        return $a['datetime'] < $b['datetime'] ? -1 : 1;
    }
}
