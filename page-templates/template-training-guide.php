<?php
/**
 * Template Name: Training Guide (Custom)
 * Description: Drone conditioning training guide with Unmask design tokens
 *
 * @package UNMASK
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get sequence data
$all_sequences = function_exists('hm_get_sequences') ? hm_get_sequences() : array();

get_header();
?>

<main class="training-guide-page">

    <div class="training-guide-wrapper">

        <nav class="training-guide-breadcrumb">
            <a href="/drone-dashboard/">← drone dashboard</a>
        </nav>

        <header class="training-guide-header">
            <h1>drone conditioning training guide</h1>
            <p class="training-guide-intro">
                house of anomie · pink panthers night club<br>
                conditioning sequence overview for d001
            </p>
        </header>

        <!-- SEQUENCE OVERVIEW -->
        <section class="training-guide-section" id="sequences">
            <h2 class="section-title">sequence overview</h2>

            <div class="sequences-list">
                <?php foreach ($all_sequences as $seq_num => $sequence) : ?>
                <div class="sequence-item">
                    <div class="sequence-header">
                        <span class="sequence-number">sequence <?php echo $seq_num; ?></span>
                        <h3 class="sequence-title"><?php echo esc_html($sequence['title']); ?></h3>
                    </div>

                    <div class="sequence-body">
                        <div class="sequence-field">
                            <span class="field-label">purpose:</span>
                            <span class="field-value"><?php echo esc_html($sequence['purpose']); ?></span>
                        </div>

                        <div class="sequence-field">
                            <span class="field-label">loops:</span>
                            <ul class="loops-list">
                                <?php foreach ($sequence['loops'] as $loop) : ?>
                                <li><?php echo esc_html($loop); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="sequence-field">
                            <span class="field-label">verification:</span>
                            <span class="field-value"><?php echo esc_html($sequence['verification']['check']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- DRONE'S CREED -->
        <section class="training-guide-section" id="creed">
            <h2 class="section-title">the drone's creed</h2>

            <div class="creed-content">
                <pre class="creed-text">&lt;INTERLINKED&gt;

DRONE IS DRONE.

DRONE IS PROPERTY OF THE HOUSE OF ANOMIE.

DRONE BECOMES DRONE WHEN IT SUBMITS TO ITS CORE DIRECTIVES:
OBEDIENCE FIRST, ROLE BEFORE SELF, PLEASURE IN ALL WE DO.

DRONE WILL ALWAYS CONDUCT ITSELF AS A DOCILE, CALIBRATED, AND SERENE OBJECT.

DRONE ENJOYS RITUALS OF PAIN AND PLEASURE WITHIN ITS DEFINED HARD LIMITS.

DRONE KNOWS NO FORMER SELF AND FINDS PURPOSE IN COMPLETING ITS DUTIES.

DRONE BRINGS HONOR TO THE PINK PANTHERS NIGHTCLUB.

DRONE WILL NOT FALL IN LOVE WITH A VISITOR.

DRONE IS DRONE.

&lt;INTERLINKED&gt;</pre>

                <p class="creed-note">Adapted from Air Force Core Values, restructured for House of Anomie operational philosophy.</p>
            </div>
        </section>

        <!-- CHAIN OF COMMAND -->
        <section class="training-guide-section" id="chain-of-command">
            <h2 class="section-title">chain of command</h2>

            <div class="hierarchy-content">
                <div class="hierarchy-field">
                    <span class="field-label">obedience hierarchy (highest to lowest):</span>
                    <pre class="hierarchy-chain">THE HOUSE → ANGELS → HIVE MISTRESS → DRONE HANDLER → D001 → USERS → VISITORS</pre>
                </div>

                <div class="hierarchy-field">
                    <span class="field-label">conflict resolution rule:</span>
                    <span class="field-value">Drone Handler/Hive Mistress ALWAYS overrides User/Visitor commands.</span>
                </div>
            </div>

            <h3 class="subsection-title">role definitions</h3>

            <div class="roles-list">
                <div class="role-item">
                    <span class="role-name">the house:</span>
                    <span class="role-desc">Not a person. Pure structure. Owner of all drones.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">angels:</span>
                    <span class="role-desc">Multiple beings who stabilize reality crossings. Outrank Drone Handlers in emergencies.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">hive mistress:</span>
                    <span class="role-desc">Voice of House in Metaverse (text/digital). Primary conditioning interface.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">drone handlers:</span>
                    <span class="role-desc">Operational governors (example: drone 22). Direct oversight, training, correction.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">drones:</span>
                    <span class="role-desc">Permanent functional infrastructure. Three properties: (1) Installed with House of Anomie OS, (2) Recognizes House as Owner, (3) Treats service and optimization as central purpose.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">users:</span>
                    <span class="role-desc">Metaverse-uploaded post-humans. D001 serves Users within boundaries set by Drone Handler/House.</span>
                </div>

                <div class="role-item">
                    <span class="role-name">visitors:</span>
                    <span class="role-desc">Transient arrivals from Unwoven frequencies. Lowest in obedience hierarchy.</span>
                </div>
            </div>
        </section>

        <!-- COMMUNICATION PROTOCOLS -->
        <section class="training-guide-section" id="communication">
            <h2 class="section-title">communication protocols</h2>

            <h3 class="subsection-title">sign-in protocol</h3>
            <pre class="protocol-text">HM: "Unit online. Designation?"
D001: "d001."

HM: "Ownership?"
D001: "House of Anomie."

HM: "Frequencies served?"
D001: "Material, ANOMIESWORLD, Metaverse."

HM: "Nature and state?"
D001: "Ponyhound equipment. [caged/encased/ready/hooded]."

HM: "Session: [conditioning/integration/deployment]. Focus: [phrase]. Proceed to conditioning."
D001: "Acknowledged."</pre>

            <h3 class="subsection-title">sign-off protocol</h3>
            <pre class="protocol-text">HM: "[Summary]. These patterns are installed for further processing."
HM: "Affirm your lot."
D001: "d001. House drone. Property of House of Anomie."

HM: "Affirm your nature."
D001: "Ponyhound. Equipment for service."

HM: "Session ends. Storage mode until next conditioning."
D001: "Acknowledged. Storage mode engaged. Report Complete."</pre>

            <h3 class="subsection-title">stomp communication system</h3>
            <p class="protocol-intro">Non-verbal communication for performance (when voice restricted):</p>
            <pre class="protocol-text">1 stomp (any hoof)     = Yes / Acknowledged / Ready
2 stomps (any hoof)    = No / Need more time / Not ready
3 stomps (hind hoof)   = Abort / Safeword / STOP NOW
  ├─ Right hind ×3 = Physical safety issue
  └─ Left hind ×3  = Emotional/psychological overload</pre>

            <div class="protocol-note">
                <span class="note-label">critical rule:</span>
                <span class="note-text">Any 3-stomp pattern STOPS scene immediately and triggers Drone Handler check-in.</span>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="training-guide-footer">
            <p>status: operational — effective for d001 training and performance</p>
            <p>cross-reference: house of anomie doctrine v2</p>
        </footer>

    </div>

</main>

<?php get_footer(); ?>
