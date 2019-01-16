<?php

/**
 * Login form extension
 *
 * Expects that your login form call updateExternalAuth with a FieldGroup where
 * all external authentications buttons are pushed
 *
 * @author Kalyptus SPRL <thomas@kalyptus.be>
 */
class EidLoginFormExtension extends Extension
{
    public function updateExternalAuth(FieldGroup $holder)
    {
        $lang = i18n::get_lang_from_locale(i18n::get_locale());
        if (!in_array($lang, ['en', 'fr', 'nl'])) {
            $lang = 'en';
        }
        $href = '/eid/performLogin';
        $label = '<img src="' . EidHelper::getButtonImage() . '" />';
        $buttonHtml = '<a href="' . $href . '" id="external-auth-button-eid" class="external-auth-button">' . $label . '</a>';
        $holder->push(new LiteralField('EidLogin', $buttonHtml));
    }
}
