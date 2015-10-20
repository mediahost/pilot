<?php

use Nette\Diagnostics\Debugger;

/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter
{

    /**
     * @param  Exception
     * @return void
     */
    public function renderDefault($exception)
    {
        $error = 500;

        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->payload->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof Nette\Application\BadRequestException) {
            $code = $exception->getCode();
            $error = in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx';
            // log to access.log
            Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');
        } else {
            Debugger::log($exception, Debugger::ERROR); // and log exception
        }
        switch ($error) {
            case "403":
                $this->template->title = $this->translator->translate("Access Denied");
                $this->template->content = $this->translator->translate("You do not have permission to view this page. " .
                        "Please try contact the web site administrator if you believe you should be able to view this page.");
                break;
            case "404":
                $this->template->title = $this->translator->translate("Page Not Found");
                $this->template->content = $this->translator->translate("The page you requested could not be found. " .
                        "It is possible that the address is incorrect, or that the page no longer exists.");
                break;
            case "405":
                $this->template->title = $this->translator->translate("Method Not Allowed");
                $this->template->content = $this->translator->translate("The requested method is not allowed for the URL.");
                break;
            case "410":
                $this->template->title = $this->translator->translate("Page Not Found");
                $this->template->content = $this->translator->translate("The page you requested has been taken off the site. We apologize for the inconvenience.");
                break;
            case "4xx":
                $this->template->title = $this->translator->translate("Oops...");
                $this->template->content = $this->translator->translate("Your browser sent a request that this server could not understand or process.");
                break;

            case "500":
            default:
                $this->template->title = $this->translator->translate("Server Error");
                $this->template->content = $this->translator->translate("We're sorry! The server encountered an internal error and was unable to complete your request. " .
                        "Please try again later.");
                break;
        }
        $this->template->number = $error;
    }

}
