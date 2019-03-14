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
        'readUserData',
    );

    public function index()
    {
        return $this->httpError(404);
    }

    /**
     * Endpoint to perform login from the LoginForm
     *
     * @return void
     */
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
        $openid->required = EidHelper::getDataset();
        $openid->lang = EidHelper::getLang();
        $url = $openid->authUrl();
        return $this->redirect($url);
    }

    /**
     * Endpoint to read user data and do something with it
     *
     * @return void
     */
    public function readUserData()
    {
        $openid = new LightOpenID(Director::absoluteBaseURL());

        // It will be redirected back to this page after login
        if ($openid->mode) {
            $backURL = Session::get('Eid.BackURL');
            Session::clear('Eid.BackURL');
            if ($openid->validate()) {
                $attrs = $openid->getAttributes();
                EidHelper::setEidAttributes($attrs);
            } else {
                $this->log("Failed OpenID request from IP " . $this->getRequest()->getIP(), SS_Log::WARN);
                $this->setLoginFormMessage(_t('EidController.FAIL_TO_VALIDATE', 'Failed to validate OpenID request'));
            }
            return $this->redirect($backURL);
        }

        Session::set('Eid.BackURL', $this->getBackURL());

        // Proceed to identity server
        $openid->identity = EidHelper::getIdentityServer();
        $openid->required = EidHelper::getDataset();
        $openid->lang = EidHelper::getLang();
        $url = $openid->authUrl();
        return $this->redirect($url);
    }

    protected function getBackURL()
    {
        $url = '/';
        if ($this->getRequest()) {
            if ($this->getRequest()->requestVar('BackURL')) {
                $url = $this->getRequest()->requestVar('BackURL');
            } elseif ($this->getRequest()->isAjax() && $this->getRequest()->getHeader('X-Backurl')) {
                $url = $this->getRequest()->getHeader('X-Backurl');
            } elseif ($this->getRequest()->getHeader('Referer')) {
                $url = $this->getRequest()->getHeader('Referer');
            }
        }

        if (Director::is_site_url($url)) {
            $url = Director::absoluteURL($url, true);
        } else {
            $url = '/';
        }

        return $url;
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
