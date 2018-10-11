<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The maintenance layout.
 *
 * @package   theme_eguru
 * @copyright 2015 Nephzat Dev Team,nephzat.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$footnote = theme_eguru_get_setting('footnote', 'format_text');
$footnote = theme_eguru_lang($footnote);
$footerbtitle2 = theme_eguru_get_setting('footerbtitle2', 'format_text');
$footerbtitle2 = theme_eguru_lang($footerbtitle2);
$footerbtitle3 = theme_eguru_get_setting('footerbtitle3', 'format_text'); // used as site regulation link
$footerbtitle3 = theme_eguru_lang($footerbtitle3);
$footerbtitle4 = theme_eguru_get_setting('footerbtitle4', 'format_text');
$footerbtitle4 = theme_eguru_lang($footerbtitle4);
/*.      פייסבוק של isteam: https://www.facebook.com/groups/886220068079963/
2.      חשבון אינסטגרם ortisraeliSTEAM#  https://www.instagram.com/explore/tags/ortisraelisteam/
3.      ערוץ יו-טיוב – playlist  -   https://www.youtube.com/playlist?list=PL9Bbzm7UqdIAl-M8ZfKWz9DpslKnfnG4j */

$fburl = theme_eguru_get_setting('fburl');
$fburl = trim($fburl);
$pinurl = theme_eguru_get_setting('pinurl'); // used as about link
$pinurl = trim($pinurl);
$twurl = theme_eguru_get_setting('twurl'); // used as instagram link
$twurl = trim($twurl);
$gpurl = theme_eguru_get_setting('gpurl');  // used as youtube link
$gpurl = trim($gpurl);

$address = theme_eguru_get_setting('address');
$emailid = theme_eguru_get_setting('emailid');
$phoneno = theme_eguru_get_setting('phoneno');

?>

    <footer id="footer" class="footer-foot">

        <div class="social-media">
            <ul class="smedia-list">
                <?php if (!empty($fburl)): ?>
                    <li class="smedia smedia-01">
                        <a href="<?php echo $fburl; ?>" target="_blank">
                                	<span class="media-icon">
                                    <i class="fa <?php echo get_string('mediaicon1', 'theme_eguru'); ?>"></i>
                                    </span>
                                            <span
                                                class="media-name"><?php echo get_string('medianame1', 'theme_eguru'); ?></span>
                        </a>
                    </li>
                    <?php
                endif;
                ?>

                <?php if (!empty($twurl)): ?>
                    <li class="smedia smedia-02">
                        <a href="<?php echo $twurl; ?>" target="_blank">
                                	<span class="media-icon">
                                    <i class="fa <?php echo get_string('mediaicon2', 'theme_eguru'); ?>"></i>
                                    </span>
                                            <span
                                                class="media-name"><?php echo get_string('medianame2', 'theme_eguru'); ?></span>
                        </a>
                    </li>
                    <?php
                endif;
                ?>

                <?php if (!empty($gpurl)): ?>
                    <li class="smedia smedia-03">
                        <a href="<?php echo $gpurl; ?>" target="_blank">
                                	<span class="media-icon">
                                    <i class="fa <?php echo get_string('mediaicon3', 'theme_eguru'); ?>"></i>
                                    </span>
                                            <span
                                                class="media-name"><?php echo get_string('medianame3', 'theme_eguru'); ?></span>
                        </a>
                    </li>
                    <?php
                endif;
                ?>

                <?php if (!empty($footerbtitle3)): ?>
                    <li class="site-link">
                        <a href="<?php echo $footerbtitle3; ?>" target="_blank">
                            <span class="media-name"><?php echo get_string('site-regulation', 'theme_eguru'); ?></span>
                        </a>
                    </li>
                <?php
                endif;
                ?>

                <?php if (!empty($pinurl)): ?>
                    <li class="site-link">
                        <a href="<?php echo $pinurl; ?>" target="_blank">
                            <span class="media-name"><?php echo get_string('about', 'theme_eguru'); ?></span>
                        </a>
                    </li>
                <?php
                endif;
                ?>


            </ul>
        </div>

        <div class="credits">Copyright &copy; 2015 - Developed by
            <a href="http://www.lmsthemes.com/">Lmsthemes.com</a>.Powered by <a
                href="https://moodle.org">Moodle</a>
        </div>
    </footer>
    <!--E.O.Footer-->
<?php
echo $OUTPUT->standard_end_of_body_html();
