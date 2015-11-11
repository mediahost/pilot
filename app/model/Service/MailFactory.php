<?php

namespace Model\Service;

use Nette\Http\Session,
    Nette\Mail\Message,
    Nette\Mail\SendmailMailer,
    Nette\Mail\SmtpMailer,
    Nette\Localization\ITranslator,
    Nette\Templating\FileTemplate;

/**
 * MailFactory
 *
 * @author Petr Poupě
 */
class MailFactory
{

    const SEND_FROM = "info@pilotincommands.com";
    const EMAIL_SUPPORT = "support@pilotincommands.com";
    const RECIEVER_HELLO_MESSAGE = "support@pilotincommands.com";
    const MAIL_SIGN_CREATE_ACCOUNT = 1;
    const MAIL_SIGN_CHANGE_PASSWORD = 2;
    const MAIL_SIGN_VERIFY = 3;
    const MAIL_SEND_CV = 4;
    const PRIVATE_MAIL_GET_IN_TOUCH = 5;
    const PRIVATE_MAIL_JOB_APPLY = 6;
    const MAIL_PASSWORD_RESET = 7;
    const MAIL_THANKS_FOR_APPLY = 8;
    const MAIL_CHAT_NOTIFY = 9;
    const MAIL_MATCHED_NOTIFY = 10;
    const MAIL_REJECTED = 11;
    const MAIL_APPLY_JOB = 12;
    const FROM_NOREPLY = 1;
    const FROM_SUPPORT = 2;

    private $lang;

    /**
     * @var \Nette\Mail\IMailer
     */
    private $mailer;

    /**
     * @var Message
     */
    private $mail;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ITranslator
     */
    private $translator;

    public function __construct(Session $session, $lang, ITranslator $translator, \Nette\Mail\IMailer $mailer)
    {
        $this->session = $session;
        $this->lang = $lang;
        $this->translator = $translator;

        $this->mail = new Message;
        $this->mailer = $mailer;
    }

    public function setTo($to)
    {
        if (is_array($to)) {
            foreach ($to as $email => $name) {
                if (\Nette\Utils\Validators::isEmail($email)) {
                    $this->mail->addTo($email, !empty($name) ? $name : NULL);
                } else if (\Nette\Utils\Validators::isEmail($name)) {
                    $this->mail->addTo($name);
                }
            }
        } else if (is_string($to) && \Nette\Utils\Validators::isEmail($to)) {
            $this->mail->addTo($to);
        }
    }

    public function selectFrom($from)
    {
        switch ($from) {
            case self::FROM_NOREPLY:
                $this->mail->setFrom(self::SEND_FROM, "pilotincommands.com");
                break;
            case self::FROM_SUPPORT:
                $this->mail->setFrom(self::EMAIL_SUPPORT, "pilotincommands.com");
                break;
            default:
                break;
        }
    }

    public function selectMail($name, $params = array())
    {
        $fromTemplate = NULL;
        $template = new FileTemplate;
        $emailTemplatesDir = "../app/components/EmailTemplates/";
        switch ($name) {
            case self::MAIL_SIGN_CREATE_ACCOUNT:
            case self::MAIL_SIGN_CHANGE_PASSWORD:
            case self::MAIL_SIGN_VERIFY:
                $fromTemplate = $emailTemplatesDir;
                switch ($name) {
                    case self::MAIL_SIGN_CREATE_ACCOUNT:
                        $fromTemplate .= "emailSignCreateAccount.latte";
                        break;
                    case self::MAIL_SIGN_CHANGE_PASSWORD:
                        $fromTemplate .= "emailSignChangePassword.latte";
                        break;
                    case self::MAIL_SIGN_VERIFY:
                        $fromTemplate .= "emailSignVerify.latte";
                        break;
                }
                $this->mail->setSubject($this->translator->translate("Source-Code"));

                $password = array_key_exists('password', $params) ? $params['password'] : "";
                $username = array_key_exists('username', $params) ? $params['username'] : "";
                $link = array_key_exists('link', $params) ? $params['link'] : "";
                $code = array_key_exists('code', $params) ? $params['code'] : "";

                $template->username = $username;
                $template->password = $password;
                $template->link = $link;
                $template->code = $code;
                break;
            case self::MAIL_SEND_CV:
                $attach = array_key_exists('attach', $params) ? $params['attach'] : "";
                $text = array_key_exists('text', $params) ? $params['text'] : NULL;
                $this->mail->setSubject("My new CV");

				if ($text) {
					$this->mail->setBody($text);
				} else {
					$this->mail->setBody(
							"Hi," .
							"\n" .
							"Look at my new cv form pilotincommands.com"
					);
				}
				$this->mail->addAttachment($attach);
                break;
            case self::PRIVATE_MAIL_GET_IN_TOUCH:
                $from = array_key_exists('from', $params) ? $params['from'] : "";
                $name = array_key_exists('name', $params) ? $params['name'] : $from;
                $subject = array_key_exists('subject', $params) ? $params['subject'] : "";
                $message = array_key_exists('message', $params) ? $params['message'] : "";
                $feelings = array_key_exists('feelings', $params) ? $params['feelings'] : "";

                $this->setTo(self::RECIEVER_HELLO_MESSAGE);
                $this->mail->setFrom($from, $name);
                $this->mail->setSubject($subject);
				
				$body = $message . "\n\n";
				if ($feelings) {
					$body .= "My feelings: " . $feelings;
				}
                $this->mail->setBody($body);
                break;
            case self::MAIL_APPLY_JOB:
                $company = array_key_exists('company', $params) ? $params['company'] : "";
                $candidate = array_key_exists('candidate', $params) ? $params['candidate'] : "";
                $job = array_key_exists('job', $params) ? $params['job'] : "";
                $link = array_key_exists('link', $params) ? $params['link'] : "";

                $this->setTo(self::RECIEVER_HELLO_MESSAGE);
                $this->mail->setSubject("New Job Application");
                $template->company = $company;
                $template->candidate = $candidate;
                $template->job = $job;
                $template->link = $link;
                
                $fromTemplate = $emailTemplatesDir . 'emaiApplyNotification.latte';
                break;
            case self::PRIVATE_MAIL_JOB_APPLY:
                $to = array_key_exists('to', $params) ? $params['to'] : "";
                $from = array_key_exists('from', $params) ? $params['from'] : "";
                $name = array_key_exists('name', $params) ? $params['name'] : $from;
                $subject = array_key_exists('subject', $params) ? $params['subject'] : "";
                $message = array_key_exists('message', $params) ? $params['message'] : "";
                $attach = array_key_exists('attachmets', $params) ? $params['attachmets'] : "";

                $this->setTo($to);
//                $this->setTo("info@cvgdynamics.com");
                $this->mail->setFrom($from, $name);
                $this->mail->setSubject($subject);

                $this->mail->setBody($message);
                foreach ($attach as $att) {
                    if ($att !== NULL) {
                        $this->mail->addAttachment($att);
                    }
                }
                break;
            case self::MAIL_PASSWORD_RESET:
                $password = array_key_exists('password', $params) ? $params['password'] : "";
                $username = array_key_exists('username', $params) ? $params['username'] : "";

                $this->mail->setSubject("Password reset");

                $this->mail->setBody(
                        "Password for username : '{$username}' has been reset to : '{$password}'."
                );
                break;
            case self::MAIL_THANKS_FOR_APPLY:
                $to = array_key_exists('to', $params) ? $params['to'] : "";
                $template->name = array_key_exists('name', $params) ? $params['name'] : $from;
                $template->refnr = array_key_exists('refnr', $params) ? $params['refnr'] : "";
                
                $this->setTo($to);
                $this->mail->setSubject('Job Application');
                $this->selectFrom(self::FROM_NOREPLY);
                $fromTemplate = $emailTemplatesDir . 'emailThanksForApply.latte';
                break;
            case self::MAIL_CHAT_NOTIFY:
                
                $this->mail->setSubject('New message on pilotincommands.com');
                $template->from = $params['message_from'];
                $template->text = $params['message_text'];
                $template->jobLink = $params['job_link'];
                
                $this->setTo($params['to']);
                $this->selectFrom(self::FROM_NOREPLY);
                $fromTemplate = $emailTemplatesDir . 'emailChatMessageNotify.latte';
                break;
            case self::MAIL_MATCHED_NOTIFY:
                $this->mail->setSubject('pilotincommands.com');
                $this->setTo($params['to']);
                $this->selectFrom(self::FROM_SUPPORT);
                
                $template->jobLink = $params['job_link'];
                $template->candidateName = $params['candidate_name'];
                $template->jobName = $params['job_name'];
                $template->companyName = $params['company_name'];
                
                $fromTemplate = $emailTemplatesDir . 'emailJobMatched.latte';
                break;
            case self::MAIL_REJECTED:
                $this->mail->setSubject('pilotincommands.com');
                $this->setTo($params['to']);
                $this->selectFrom(self::FROM_NOREPLY);
                
                $template->name = $params['candidate_name'];
                $template->jobName = $params['job_name'];
                $template->companyName = $params['company_name'];
                
                $fromTemplate = $emailTemplatesDir . 'emaiRejected.latte';
                break;
            default:
                break;
        }

        if ($fromTemplate !== NULL) {
            $template->setFile($fromTemplate);
            $template->setTranslator($this->translator);
            $template->registerFilter(new \Nette\Latte\Engine);
            $template->registerHelperLoader('Nette\Templating\Helpers::loader');
            $this->mail->setHtmlBody($template);
        }
    }

    public function send()
    {
        $this->mail->setMailer($this->mailer);
        $this->mail->send();
    }

}
