<?php

/**
 * EidController
 *
 * @author Kalyptus SPRL <thomas@kalyptus.be>
 */
class EidController extends Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'index',
        'performLogin',
    );

    public function index()
    {
        return $this->httpError(404);
    }

    public function performLogin()
    {
        $openid = new LightOpenID(Director::absoluteBaseURL());

        // It will be redirected back to this page after login
        if ($openid->mode) {
            if ($openid->validate()) {
                $attrs = $openid->getAttributes();
                $nationalNumber = $attrs['eid/rrn'];

                $memberField = Member::config()->national_number_field;
                if (!$memberField) {
                    throw new Exception("Member field is empty");
                }

                $member = Member::get()->filter($memberField, $nationalNumber)->first();
                if ($member) {
                    // Provide extension point
                    if ($member->hasMethod('onEidLogin')) {
                        $member->onEidLogin($attrs);
                    }
                    $member->login();
                    $url = '/';
                    return $this->redirect($url);
                } else {
                    $this->log("No member for national number $nationalNumber");
                    $this->setLoginFormMessage(_t('EidController.NO_MEMBER_FOUND', 'No member found for this national number'));
                    return $this->redirect('/Security/login');
                }
            } else {
                $this->log("Failed OpenID request from IP " . $this->getRequest()->getIP(), SS_Log::WARN);
                $this->setLoginFormMessage(_t('EidController.FAIL_TO_VALIDATE', 'Failed to validate OpenID request'));
                return $this->redirect('/Security/login');
            }
        }

        // Proceed to identity server
        $openid->identity = EidHelper::getIdentityServer();
        $openid->required = array(
            'namePerson/first', 'namePerson/last',
            'namePerson', 'person/gender', 'contact/postalCode/home',
            'contact/postalAddress/home', 'contact/city/home', 'eid/nationality',
            'eid/pob', 'birthDate', 'eid/card-number', 'eid/card-validity/begin',
            'eid/card-validity/end', 'eid/photo', 'eid/rrn'
        );
        $openid->lang = EidHelper::getLang();
        $url = $openid->authUrl();
        return $this->redirect($url);
    }

    protected function setLoginFormMessage($message, $status = 'warning')
    {
        $authenticator = Authenticator::get_default_authenticator();
        $form = $authenticator::get_login_form($this);
        $form->sessionMessage($message, $status);
        Session::set('MemberLoginForm.force_message', 1);
    }

    protected function log($message, $level = null)
    {
        if ($level === null) {
            $level = SS_LOG::DEBUG;
        }
        SS_Log::log("EID : $message", $level);
    }
}
