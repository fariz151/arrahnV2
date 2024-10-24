/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * Initialisation of the page
 */
akeeba.System.documentReady(function () {
    // Hook for the Next button
    akeeba.System.addEventListener('btnNext', 'click', function (e) {
        document.forms.publicfolderForm.submit();
        return false;
    });
});
