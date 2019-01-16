<?php

class EidHelper
{
    protected static $langs = [
        'en', 'fr', 'nl'
    ];

    public static function getIdentityServer()
    {
        if (defined('EID_IDENTITY_SERVER') && EID_IDENTITY_SERVER) {
            return EID_IDENTITY_SERVER;
        }
        return 'https://www.e-contract.be/eid-idp/endpoints/openid/auth-ident';
    }

    /**
     * Get the url to a button svg
     *
     * @return string
     */
    public static function getButtonImage()
    {
        $lang = self::getLang();
        return '/eid/images/login-' . $lang . '.svg';
    }

    /**
     * Get a supported ItsMe lang
     *
     * @return string EN, FR, NL or DE
     */
    public static function getLang()
    {
        $lang = strtolower(i18n::get_lang_from_locale(i18n::get_locale()));

        if (in_array($lang, self::$langs)) {
            return $lang;
        }
        return self::$default_langs;
    }
}
