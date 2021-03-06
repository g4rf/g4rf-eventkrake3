<?php

global $post;

?>

<?php // damit WP nur Änderungen vom Edit-Screen durchführt ?>
<input type="hidden" name="eventkrake_on_edit_screen" />

<table class="form-table">
<tr>

    <th><?=__('Der Ort der Veranstaltung', 'g4rf_eventkrake2')?></th>
    <td>
        <select class="eventkrake_formselect" name="eventkrake_locationid">
            <option value="0">---</option>
            <?php
                $locations = Eventkrake::getLocations(false);
                $postLocationId = Eventkrake::getSinglePostMeta(
                                            $post->ID, 'locationid');
                foreach($locations as $l) {
                    ?><option value='<?=$l->ID?>'<?php
                        ?><?=$l->ID == $postLocationId ? ' selected' : '' ?>><?=
                            get_the_title($l->ID)?> (<?=
                            Eventkrake::getSinglePostMeta($l->ID, 'address')
                        ?>)<?php
                    ?></option><?php
                } ?>
        </select>
        <a id="eventkrake_locationid_edit_location" href="#"
           data-url="<?=site_url("wp-admin/post.php?action=edit&post=")?>">
            <?=__('Ort bearbeiten', 'g4rf_eventkrake2')?>
        </a><br />
        <span class="description"><?php
_e('Wähle einen Ort für die Veranstaltung aus. Unter
    <a href="edit.php?post_type=eventkrake_location">Orte</a> kannst Du
    <a href="post-new.php?post_type=eventkrake_location">neue Orte anlegen</a>.',
        'g4rf_eventkrake2');
        ?></span>
    </td>
</tr>
</table>

<hr />

<table class="form-table"><tr>

<th><?=__('Zeiten', 'g4rf_eventkrake2')?></th>

<td>

    <?php // template
    $templateDate = new DateTime();
    $templateDate->setTime(12, 0, 0);

    Eventkrake::printDatePeriodPicker($templateDate, $templateDate,
            'eventkrake-template');
    ?>

    <?php // dates
    $startDates = Eventkrake::getPostMeta($post->ID, 'start');
    $endDates = Eventkrake::getPostMeta($post->ID, 'end');

    for($i = 0; $i < count($startDates); $i++) {
        $startDate = new DateTime($startDates[$i]);
        $endDate = new DateTime($endDates[$i]);

        Eventkrake::printDatePeriodPicker($startDate, $endDate);
    }
    ?>

    <div>
        <hr />
        <input type="button" class="eventkrake-add-time"
            value="<?=__('Zeit hinzufügen', 'g4rf_eventkrake2')?>" />
    </div>
</td></tr></table>

<hr />

<table class="form-table">
<tr>
    <th><?=__('Teilnehmende Künstler:innen', 'g4rf_eventkrake2')?></th>
    <td>
        <input type="text" class="eventkrake-select-search"
               placeholder="<?=__('Suche', 'g4rf_eventkrake2')?>">
        <div class="eventkrake-select-multiple">
            <?php
                $artists = Eventkrake::getArtists(false);
                $artistIds = Eventkrake::getPostMeta($post->ID, 'artists');
                foreach($artists as $a) { 
                    $selected = in_array($a->ID, $artistIds); ?>
                    <label style="<?= $selected ? 'order:-1;' : '' ?>">
                        <input type="checkbox" name="eventkrake_artists[]"
                            value="<?= $a->ID ?>" <?=
                            $selected ? 'checked' : ''
                        ?> />
                        <?= get_the_title($a->ID) ?>
                    </label>
                <?php }
            ?>
        </div>
        <span class="description"><?php
_e('Wähle hier die Künstler·innen aus, die an der Veranstaltung teilnehmen.',
    'g4rf_eventkrake2');
       ?></span>
    </td>
</tr>


<tr>

    <th><?=__('Die Kategorien', 'g4rf_eventkrake2')?></th>
    <td>
        <textarea class="eventkrake-textarea" name="eventkrake_categories"><?=
            implode(', ', Eventkrake::getPostMeta($post->ID, 'categories'));
        ?></textarea><br />
        <span class="description"><?php
            _e('Notiere hier mit Komma getrennt Kategorien, z.B.:',
                'g4rf_eventkrake2');
            ?><br /><?php
            foreach(Eventkrake::getCategories() as $c) {
                ?><span class="eventkrake-cat-suggestion"><?=
                    $c
                ?></span><?php
            }
        ?></span>
    </td>

</tr><tr>

    <th><?=__('Links zur Veranstaltung', 'g4rf_eventkrake2')?></th>
    <td class="eventkrake-flexcol">
        <div>
            <span class="description"><?=
                __('Gebe Weblinks zur Webseite und sozialen Netzwerken an.',
                    'g4rf_eventkrake2')
            ?></span>
        </div>

        <div class="eventkrake-links-template eventkrake-hide">
            <input value="" type="text" name="eventkrake-links-key[]"
                   class="regular-text" placeholder="Name des Links" />
            <input type="text" name="eventkrake-links-value[]"
                   class="regular-text" value="https://" />
        </div><?php

        $links = Eventkrake::getSinglePostMeta($post->ID, 'links');
        if(empty($links)) { // no links yet ?>
            <div>
                <input value="" type="text" name="eventkrake-links-key[]"
                       class="regular-text" placeholder="Name des Links" />
                <input type="text" name="eventkrake-links-value[]"
                       class="regular-text" value="https://" />
            </div>

        <?php } else {
            foreach($links as $link) { // show links ?>
                <div>
                    <input type="text" name="eventkrake-links-key[]"
                           class="regular-text"
                           value="<?=htmlspecialchars($link['name'])?>" />
                    <input type="text" name="eventkrake-links-value[]"
                           class="regular-text"
                           value="<?=htmlspecialchars($link['url'])?>" />
                </div>
            <?php }
        } ?>
        <div><input type="button" class="eventkrake-add-link"
            value="<?=__('Weblink hinzufügen', 'g4rf_eventkrake2')?>" /></div>
    </td>

</tr><tr>

    <th><?=__('Zusatzinfos zur Veranstaltung', 'g4rf_eventkrake2')?></th>
    <td>
        <input value="<?=Eventkrake::getSinglePostMeta($post->ID, 'tags')?>"
            type="text" name="eventkrake_tags" class="regular-text" /><br />
        <span class="description">
            <?=__('Ein Feld, das beliebige Infos zur Veranstaltung enthält. Die'
                    . ' Infos lassen sich für die Suche nutzen. müssen'
                    . ' jedoch nicht angezeigt werden.', 'g4rf_eventkrake2')?>
        </span>
    </td>

</tr></table>
