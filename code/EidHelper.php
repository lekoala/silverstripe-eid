<?php

class EidHelper
{
    protected static $langs = [
        'en', 'fr', 'nl'
    ];

    protected static $default_lang = 'en';

    protected static $dataset = [
        'namePerson/first', 'namePerson/last',
        'namePerson', 'person/gender', 'contact/postalCode/home',
        'contact/postalAddress/home', 'contact/city/home', 'eid/nationality',
        'eid/pob', 'birthDate', 'eid/card-number', 'eid/card-validity/begin',
        'eid/card-validity/end', 'eid/photo', 'eid/rrn'
    ];

    public static function getEidAttributes()
    {
        return Session::get('Eid.Attributes');
    }

    public static function setEidAttributes($attrs)
    {
        Session::set('Eid.Attributes', $attrs);
    }

    public static function getReadUserDataLink()
    {
        return '/eid/readUserData';
    }

    public static function doReadUserData()
    {
        header('Location: ' . self::getReadUserDataLink());
        exit();
    }

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
     * Get a supported lang
     *
     * @return string EN, FR, NL or DE
     */
    public static function getLang()
    {
        $lang = strtolower(i18n::get_lang_from_locale(i18n::get_locale()));

        if (in_array($lang, self::$langs)) {
            return $lang;
        }
        return self::$default_lang;
    }

    /**
     * Get the value of dataset
     *
     * @return array
     */
    public static function getDataset()
    {
        return self::$dataset;
    }

    /**
     * Set the value of dataset
     *
     * @param array $dataset
     */
    public static function setDataset($dataset)
    {
        self::$dataset = $dataset;
    }
}
