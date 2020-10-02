<?php

$defaultInputClass = (isset($inputClass)) ? $inputClass : 'input';
$containerType     = 'div-wrapper';

include __DIR__.'/../../../../app/bundles/FormBundle/Views/Field/field_helper.php';

$action   = $app->getRequest()->get('objectAction');
$settings = $field['properties'];

$formName       = str_replace('_', '', $formName);
$hashedFormName = md5($formName);
$formButtons    = (!empty($inForm)) ? $view->render(
    'MauticFormBundle:Builder:actions.html.php',
    [
        'deleted'        => false,
        'id'             => $id,
        'formId'         => $formId,
        'formName'       => $formName,
        'disallowDelete' => false,
    ]
) : '';

$label = (!$field['showLabel'])
    ? ''
    : <<<HTML
<label $labelAttr>{$view->escape($field['label'])}</label>
HTML;

$inputs = '';

for ($i=0; $i < $settings['referrals']; ++$i) {
    $inputs .= <<<INPUTS
        <input name="mauticform[{$field['alias']}][]" $inputAttr type="email" style="margin-bottom: 3px">
INPUTS;
}

$html = <<<HTML

            <div $containerAttr>
                {$label}
                {$inputs}
                <span class="mauticform-errormsg" style="display: none;"></span>
            </div>
HTML;
?>


<?php
echo $html;
?>

