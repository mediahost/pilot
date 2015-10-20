<?php

namespace Model\Service;

/**
 * MailService
 *
 * @author Petr Poupě
 */
class MailService
{
    
    /**
     * @var \Nette\Http\Session
     */
    private $session;

    /**
     * @var \Nette\Localization\ITranslator
     */
    private $translator;
    
    /** @var \Nette\Mail\IMailer */
    private $mailer;

    public function __construct(\Nette\Http\Session $session, \Nette\Localization\ITranslator $translator, \Nette\Mail\IMailer $mailer)
    {
        $this->session = $session;
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    /**
     * Create MailFactory
     * @return \Model\Service\MailFactory
     */
    public function create($lang)
    {
        return new MailFactory($this->session, $lang, $this->translator, $this->mailer);
    }

}