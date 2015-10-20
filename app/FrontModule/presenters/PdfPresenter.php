<?php

namespace FrontModule;

/**
 * PdfPresenter
 *
 * @author Petr Poupě
 */
class PdfPresenter extends BasePresenter
{
    
    public function startup()
    {
        parent::startup();
    }

    private function createPdf(\Model\Entity\CvEntity $cv, $template = NULL, $theme = NULL)
    {
        $cvPrinter = new \CvPrinter;
        if ($template !== NULL) {
            $cvPrinter->setTemplate($template);
        }
        if ($theme !== NULL) {
            $cvPrinter->setCssTheme($theme);
        }
        $pdf = $cvPrinter->generate($cv, $this, $this->translator, $this->pageInfo);
        return $pdf;
    }

    /**
     *
     * @param type $cvId
     * @return \Model\Entity\CvEntity
     */
    private function loadCv($cvId)
    {
        // load actual CV
        /* @var $cv \Model\Entity\CvEntity */
        $cv = $this->context->cv->getCv($cvId);
        
        if ($cv !== FALSE) {
            $user = $this->userService->find($cv->userId);
        } else {
            $this->noCvError(); // cv neexistuje
        }
        
        if (!$cv->isCompleted()) {
            $this->noCvError($cv);  // cv neni completed
        }
        
        if ($cv->public || $user->is_profile_public) {
            return $cv; // cv je public po sharovani na fb alebo sharovani profilu
        }
        
        if ($cv->userId == $this->user->id) {
            return $cv; // cv si prezera jeho vlastnik
        }
        
        if ($this->user->isCompany() && $this->user->companyAllowedToCv($cv)) {
            return $cv; // cv si prezera company
        }
        
        if ($this->user->isInRole('admin') || $this->user->isInRole('superadmin')) {   
            return $cv; // cv si prezera admin
        }
        
        $this->noCvError();
    }
    
    private function noCvError(\Model\Entity\CvEntity $cv = NULL)
    {
        if ($cv && $this->user->isLoggedIn() && $this->user->id == $cv->userId) {
            $this->flashMessage("CV isn't completed", "warning");
            $this->redirect("Cv:default", $cv->id);
        }
        $this->flashMessage("This CV doesn't exist.", "warning");
        $this->redirect("Homepage:");
    }

//    public function actionSavePdf($cv, $redirect = NULL)
//    {
//        $cv = $this->loadCv($cv);
//        $pdf = $this->createPdf($cv);
//
//        $dirName = \CommonHelpers::concatStrings("/", ".", "tmp", $cv->userId, "cv_saved") . "/";
//        if (\CommonHelpers::dir_exists($dirName)) {
//            $pdf->save($dirName, $cv->id);
//        }
//
//        if ($redirect === NULL) {
//            $this->flashMessage("CV was succesfull saved", "success");
//            $this->redirect("Homepage:");
//        } else {
//            $this->redirect($redirect);
//        }
//    }

    /**
     * USE:
     * - 1. render template in browser and terminate, e.g. testing
     * $pdf->test();
     * - 2. save file to server
     * $pdf->save("../www_root/generated/"); // as a filename $this->documentTitle will be used
     * $pdf->save("../www_root/generated/", "another file 123"); // OR use a custom name
     * OR in case of mail attachment, returns path to file on server
     * $savedFile = $pdf->save("../www_root/contracts/");
     * $mail = new Nette\Mail\Message;
     * $mail->addTo("john@doe.com");
     * $mail->addAttachment($savedFile);
     * $mail->send();
     * - 3. send pdf file to output (save/open by user) and terminate
     * $pdf->output();
     *
     * @param type $cv
     * @param type $print
     * @param type $send
     */
    public function actionCv($cv = NULL, $print = FALSE, $send = "", $text = NULL)
    {
        $cvId = $cv;
        $cv = $this->loadCv($cvId);

        if (!$cv->hasPhoto()) {
            $this->flashMessage("Please upload your profile photo, under 'Personal Details' section, before progressing.", "warning");
            $this->redirect("Cv:", array("cv" => $cvId, "step" => 1));
        }

        $pdf = $this->createPdf($cv, $cv->templateName, $cv->templateName);

        $this->setLayout("pdf.layout");

        if ($send !== "") {
            $dirName = "./tmp/" . $cv->userId . "/";
            if (!file_exists($dirName))
                mkdir($dirName);
            $savedFile = $pdf->save($dirName);
            $mail = $this->context->mail->create($this->lang);
            $mail->setTo($send);
            $mail->selectFrom(\Model\Service\MailFactory::FROM_NOREPLY);
            $mail->selectMail(\Model\Service\MailFactory::MAIL_SEND_CV, array(
                'attach' => $savedFile,
                'text' => $text,
            ));
            $mail->send();

            $this->flashMessage("CV was succesfully sent", "success");
            $this->redirect("Cv:");
        }

        if ($print) {
            $pdf->mPDF->IncludeJS("print();");
        }

//        $pdf->test();
        $pdf->output();
        $this->terminate();
    }

    public function actionShare($cv)
    {
        $cvId = $cv;
        $cv = $this->loadCv($cvId);

        if (!$cv->public) {
            $cv->public = 1;
            $cvService = $this->context->getByType('\Model\Service\CvService');
            /* @var $cvService \Model\Service\CvService */
            $cvService->save($cv);
        }

        $url = $this->context->httpRequest->getUrl();
        $fbSendPostCv = new \Nette\Http\Url("https://www.facebook.com/dialog/feed");
        $fbSendPostCv->appendQuery(
                array(
                    'app_id' => $this->context->parameters['facebook']['app_id'],
                    'link' => $this->link("//Pdf:cv", $cv->id),
                    'picture' => $url->hostUrl . $url->scriptPath . $this->context->parameters["pageInfo"]['logoOg'],
                    'name' => $this->context->parameters['facebook']['app_name'],
                    'caption' => $this->context->parameters['facebook']['app_name'],
                    'description' => $this->context->parameters['facebook']['description'],
                    'redirect_uri' => $this->link("//this"),
                )
        );
        $this->redirectUrl($fbSendPostCv);
    }

}
