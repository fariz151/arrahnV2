<?php

use AcyMailing\Classes\FieldClass;
use AcyMailing\Classes\ListClass;
use AcyMailing\Classes\UserClass;
use Joomla\CMS\Factory;

$ds = DIRECTORY_SEPARATOR;
if (!include_once(rtrim(JPATH_ADMINISTRATOR, $ds).$ds.'components'.$ds.'com_acym'.$ds.'helpers'.$ds.'helper.php')) {
    echo 'This module cannot work without AcyMailing';

    return;
};

acym_initModule($params);

$identifiedUser = null;
$currentUserEmail = acym_currentUserEmail();
if ($params->get('userinfo', '1') == '1' && !empty($currentUserEmail)) {
    $userClass = new UserClass();
    $identifiedUser = $userClass->getOneByEmail($currentUserEmail);
}

$visibleLists = $params->get('displists', []);
$hiddenLists = $params->get('hiddenlists', []);
$fields = $params->get('fields', []);
$allfields = is_array($fields) ? $fields : explode(',', $fields);
if (!in_array('2', $allfields)) {
    $allfields[] = 2;
}
acym_arrayToInteger($visibleLists);
acym_arrayToInteger($hiddenLists);
acym_arrayToInteger($allfields);

$listClass = new ListClass();
$fieldClass = new FieldClass();

$allLists = $listClass->getAllWithoutManagement(true);
$visibleLists = array_intersect($visibleLists, array_keys($allLists));
$hiddenLists = array_intersect($hiddenLists, array_keys($allLists));
$allfields = $fieldClass->getFieldsByID($allfields);
$fields = [];
foreach ($allfields as $field) {
    if (intval($field->active) === 0) continue;
    $fields[$field->id] = $field;
}

if (empty($visibleLists) && empty($hiddenLists)) {
    $hiddenLists = array_keys($allLists);
}

// Make sure we don't display a list that's in "automatically subscribe to"
if (!empty($visibleLists) && !empty($hiddenLists)) {
    $visibleLists = array_diff($visibleLists, $hiddenLists);
}

if (empty($identifiedUser->id)) {
    //Check lists based on the option
    $checkedLists = $params->get('listschecked', []);
    if (!is_array($checkedLists)) {
        if (strtolower($checkedLists) == 'all') {
            $checkedLists = $visibleLists;
        } elseif (strpos($checkedLists, ',') || is_numeric($checkedLists)) {
            $checkedLists = explode(',', $checkedLists);
        } else {
            $checkedLists = [];
        }
    }
} else {
    $checkedLists = [];
    $userLists = $userClass->getUserSubscriptionById($identifiedUser->id);

    $countSub = 0;
    $countUnsub = 0;
    $formLists = array_merge($visibleLists, $hiddenLists);
    foreach ($formLists as $idOneList) {
        if (empty($userLists[$idOneList]) || $userLists[$idOneList]->status == 0) {
            $countSub++;
        } else {
            $countUnsub++;
            $checkedLists[] = $idOneList;
        }
    }
}
acym_arrayToInteger($checkedLists);


$config = acym_config();

// Texts
$subscribeText = $params->get('subtext', 'ACYM_SUBSCRIBE');
if (!empty($identifiedUser->id)) $subscribeText = $params->get('subtextlogged', 'ACYM_SUBSCRIBE');
$unsubscribeText = $params->get('unsubtext', 'ACYM_UNSUBSCRIBE');
$unsubButton = $params->get('unsub', '0');

// Formatting
$listPosition = $params->get('listposition', 'before');
$displayOutside = $params->get('textmode') == '0';

// Display success message
$successMode = $params->get('successmode', 'replace');

// Redirections
$redirectURL = $params->get('redirect', '');
$unsubRedirectURL = $params->get('unsubredirect', '');
$ajax = empty($redirectURL) && empty($unsubRedirectURL) && $successMode != 'standard' ? '1' : '0';

// Customization
$formClass = $params->get('formclass', '');
$alignment = $params->get('alignment', 'none');
$style = $alignment == 'none' ? '' : 'style="text-align: '.$alignment.'"';

// Articles
$termsURL = acym_getArticleURL(
    $params->get('termscontent', 0),
    $params->get('articlepopup', 1),
    'ACYM_TERMS_CONDITIONS'
);
$privacyURL = acym_getArticleURL(
    $params->get('privacypolicy', 0),
    $params->get('articlepopup', 1),
    'ACYM_PRIVACY_POLICY'
);

if (empty($termsURL) && empty($privacyURL)) {
    $termslink = '';
} elseif (empty($privacyURL)) {
    $termslink = acym_translationSprintf('ACYM_I_AGREE_TERMS', $termsURL);
} elseif (empty($termsURL)) {
    $termslink = acym_translationSprintf('ACYM_I_AGREE_PRIVACY', $privacyURL);
} else {
    $termslink = acym_translationSprintf('ACYM_I_AGREE_BOTH', $termsURL, $privacyURL);
}


$formName = acym_getModuleFormName();
$formAction = htmlspecialchars_decode(acym_completeLink('frontusers', true, true));

$js = 'window.addEventListener("DOMContentLoaded", (event) => {';
$js .= "\n".'acymModule["excludeValues'.$formName.'"] = [];';
$fieldsToDisplay = [];
foreach ($fields as $field) {
    $fieldsToDisplay[$field->id] = $field->name;
    $js .= "\n".'acymModule["excludeValues'.$formName.'"]["'.$field->id.'"] = "'.acym_translation($field->name, true).'";';
}
$js .= "  });";
// Exclude default values from fields, if the user didn't fill them in
$includeJs = $params->get('includejs', 'header');
if ($includeJs === 'module') {
    echo '<script type="text/javascript">
			'.$js.'
		  </script>';
} else {
    acym_addScript(true, $js);
}
?>
	<div class="acym_module <?php echo acym_escape($formClass); ?>" id="acym_module_<?php echo $formName; ?>">
		<div class="acym_fulldiv" id="acym_fulldiv_<?php echo $formName; ?>" <?php echo $style; ?>>
			<form enctype="multipart/form-data"
				  id="<?php echo acym_escape($formName); ?>"
				  name="<?php echo acym_escape($formName); ?>"
				  method="POST"
				  action="<?php echo acym_escape($formAction); ?>"
				  onsubmit="return submitAcymForm('subscribe','<?php echo $formName; ?>', 'acymSubmitSubForm')">
				<div class="acym_module_form">
                    <?php
                    $introText = $params->get('introtext', '');
                    if (!empty($introText)) {
                        echo '<div class="acym_introtext">'.$introText.'</div>';
                    }

                    if ($params->get('mode', 'tableless') == 'tableless') {
                        $view = 'tableless.php';
                    } else {
                        $displayInline = $params->get('mode', 'tableless') != 'vertical';
                        $view = 'default.php';
                    }

                    $app = Factory::getApplication('site');
                    $template = $app->getTemplate();
                    if (file_exists(str_replace(DS, '/', ACYM_ROOT).'templates/'.$template.'/html/mod_acym/'.$view)) {
                        include ACYM_ROOT.'templates'.DS.$template.DS.'html'.DS.'mod_acym'.DS.$view;
                    } else {
                        include __DIR__.DS.'tmpl'.DS.$view;
                    }

                    ?>
				</div>

				<input type="hidden" name="ctrl" value="frontusers" />
				<input type="hidden" name="task" value="notask" />
				<input type="hidden" name="option" value="<?php echo acym_escape(ACYM_COMPONENT); ?>" />

                <?php
                //The user is authenticated so we will display its email address... we make sure the emailcloaking plugin does not alter our code!
                $currentEmail = acym_currentUserEmail();
                if (!empty($currentEmail)) {
                    echo '<span style="display:none">{emailcloak=off}</span>';
                }

                if (!empty($redirectURL)) echo '<input type="hidden" name="redirect" value="'.acym_escape($redirectURL).'"/>';
                if (!empty($unsubRedirectURL)) echo '<input type="hidden" name="redirectunsub" value="'.acym_escape($unsubRedirectURL).'"/>';

                ?>

				<input type="hidden" name="ajax" value="<?php echo acym_escape($ajax); ?>" />
				<input type="hidden" name="successmode" value="<?php echo acym_escape($successMode); ?>" />
				<input type="hidden" name="acy_source" value="<?php echo acym_escape($params->get('source', 'Module n°'.$module->id)); ?>" />
				<input type="hidden" name="hiddenlists" value="<?php echo implode(',', $hiddenLists); ?>" />
				<input type="hidden" name="fields" value="<?php echo 'name,email'; ?>" />
				<input type="hidden" name="acyformname" value="<?php echo acym_escape($formName); ?>" />
				<input type="hidden" name="acysubmode" value="mod_acym" />
				<input type="hidden" name="confirmation_message" value="<?php echo acym_escape($params->get('confirmation_message', '')); ?>" />

                <?php
                $postText = $params->get('posttext', '');
                if (!empty($postText)) {
                    echo '<div class="acym_posttext">'.$postText.'</div>';
                }
                ?>
			</form>
		</div>
	</div>
<?php
