<?php
/**
 * Hard Limits Categories and Activities Data
 *
 * Defines all categories and activities for the hard limits checklist.
 * Used by the form template and JavaScript.
 *
 * @package UNMASK
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all limits categories and activities
 *
 * Structure: category_slug => [
 *   'label' => 'Display Name',
 *   'activities' => ['slug' => 'Display Name', ...]
 * ]
 *
 * @return array Categories with activities
 */
function unmask_get_limits_categories() {
    return array(

        'touch_sensation' => array(
            'label' => 'Physical Touch & Sensation',
            'activities' => array(
                'light_touch'      => 'Light Touch / Caressing',
                'scratching'       => 'Scratching',
                'biting'           => 'Biting',
                'pinching'         => 'Pinching',
                'hair_pulling'     => 'Hair Pulling',
                'temperature_ice'  => 'Ice / Cold Play',
                'temperature_wax'  => 'Wax Play',
                'tickling'         => 'Tickling',
                'massage'          => 'Massage',
            ),
        ),

        'bondage_restraint' => array(
            'label' => 'Bondage & Restraint',
            'activities' => array(
                'rope_bondage'     => 'Rope Bondage',
                'leather_cuffs'    => 'Leather Cuffs',
                'metal_cuffs'      => 'Metal Cuffs / Shackles',
                'spreader_bars'    => 'Spreader Bars',
                'suspension_partial' => 'Partial Suspension',
                'suspension_full'  => 'Full Suspension',
                'mummification'    => 'Mummification / Wrapping',
                'predicament'      => 'Predicament Bondage',
                'hogtie'           => 'Hogtie',
                'cage_confinement' => 'Cage / Confinement',
            ),
        ),

        'impact' => array(
            'label' => 'Impact Play',
            'activities' => array(
                'hand_spanking'    => 'Hand Spanking',
                'paddle'           => 'Paddle',
                'flogger'          => 'Flogger',
                'crop'             => 'Crop',
                'cane'             => 'Cane',
                'whip_single'      => 'Single Tail Whip',
                'belt'             => 'Belt',
                'face_slapping'    => 'Face Slapping',
                'punching'         => 'Body Punching',
            ),
        ),

        'power_exchange' => array(
            'label' => 'Power Exchange & Service',
            'activities' => array(
                'verbal_commands'  => 'Following Verbal Commands',
                'positions'        => 'Formal Positions',
                'protocols'        => 'Protocols / Rules',
                'domestic_service' => 'Domestic Service',
                'body_service'     => 'Body Service',
                'boot_worship'     => 'Boot / Shoe Worship',
                'objectification'  => 'Objectification',
                'pet_play'         => 'Pet Play',
                'pony_play'        => 'Pony Play',
                'furniture'        => 'Human Furniture',
            ),
        ),

        'sensory' => array(
            'label' => 'Sensory Play',
            'activities' => array(
                'blindfolds'       => 'Blindfolds',
                'earplugs'         => 'Earplugs / Hearing Restriction',
                'hoods'            => 'Hoods',
                'gags_soft'        => 'Soft Gags (Ball, Bit)',
                'gags_hard'        => 'Hard Gags (Ring, Spider)',
                'sensory_deprivation' => 'Full Sensory Deprivation',
                'electrical_tens'  => 'Electrical (TENS)',
                'electrical_violet' => 'Violet Wand',
            ),
        ),

        'roleplay_psychological' => array(
            'label' => 'Role Play & Psychological',
            'activities' => array(
                'humiliation_private' => 'Humiliation (Private)',
                'humiliation_public'  => 'Humiliation (Public)',
                'degradation'      => 'Degradation',
                'praise_worship'   => 'Praise / Worship',
                'fear_play'        => 'Fear Play',
                'interrogation'    => 'Interrogation Scenes',
                'uniforms_costumes' => 'Uniforms / Costumes',
                'forced_scenarios' => 'Consensual Non-Consent',
                'hypnosis'         => 'Hypnosis / Trance',
                'mindfuck'         => 'Mindfuck / Mind Games',
            ),
        ),

        'exhibition' => array(
            'label' => 'Public & Exhibition',
            'activities' => array(
                'being_watched'    => 'Being Watched',
                'watching_others'  => 'Watching Others',
                'public_collar'    => 'Wearing Collar in Public',
                'public_symbols'   => 'Visible BDSM Symbols',
                'play_events'      => 'Play at Events / Dungeons',
                'photography'      => 'Being Photographed',
                'video'            => 'Video Recording',
                'online_sharing'   => 'Online Sharing (Anonymous)',
            ),
        ),

        'body_worship' => array(
            'label' => 'Body Worship & Fetish',
            'activities' => array(
                'foot_worship'     => 'Foot Worship',
                'hand_worship'     => 'Hand Worship',
                'ass_worship'      => 'Ass Worship',
                'leather_fetish'   => 'Leather',
                'latex_fetish'     => 'Latex / Rubber',
                'uniform_fetish'   => 'Uniforms',
                'lingerie'         => 'Lingerie / Specific Clothing',
                'body_writing'     => 'Body Writing',
            ),
        ),

        'edge_play' => array(
            'label' => 'Edge Play & Risk-Aware',
            'activities' => array(
                'breath_play_hand' => 'Breath Play (Hand)',
                'breath_play_other' => 'Breath Play (Bag, Mask)',
                'knife_play'       => 'Knife Play',
                'fire_play'        => 'Fire Play',
                'needle_play'      => 'Needle Play',
                'cutting'          => 'Cutting',
                'branding'         => 'Branding',
                'piercing_temp'    => 'Temporary Piercing',
            ),
        ),

        'intimacy_aftercare' => array(
            'label' => 'Intimacy & Aftercare',
            'activities' => array(
                'cuddling'         => 'Cuddling',
                'kissing'          => 'Kissing',
                'holding'          => 'Being Held',
                'verbal_processing' => 'Verbal Processing / Talking',
                'physical_aftercare' => 'Physical Aftercare (Blankets, etc.)',
                'alone_time'       => 'Quiet Time Alone After',
                'food_drink'       => 'Food / Drink Care',
                'bathing'          => 'Bathing / Shower Together',
            ),
        ),

    );
}

/**
 * Get flat list of all activity slugs
 *
 * @return array Activity slugs
 */
function unmask_get_all_activity_slugs() {
    $categories = unmask_get_limits_categories();
    $slugs = array();

    foreach ($categories as $cat) {
        $slugs = array_merge($slugs, array_keys($cat['activities']));
    }

    return $slugs;
}

/**
 * Get total activity count
 *
 * @return int Total number of activities
 */
function unmask_get_limits_activity_count() {
    return count(unmask_get_all_activity_slugs());
}
