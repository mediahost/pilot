<?php

/**
 * CvPrinter
 *
 * @author Petr Poupě
 */
class CvPrinter
{

    private $templateFile = "default";
    private $cssTheme = "default";

    /** @var \Model\Service\UserService */
    private $userService;

    /**
     * CvPrinter constructor.
     */
    public function __construct(\Model\Service\UserService $userService)
    {
        $this->userService = $userService;
    }

    public function setTemplate($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    public function setCssTheme($theme)
    {
        $this->cssTheme = $theme;
    }

    public function generate(Model\Entity\CvEntity $cv, $presenter, $translator, $pageInfo)
    {
        $presenter->setLayout(FALSE);
        $templatePath = $presenter->context->parameters["appDir"] . "/FrontModule/templates/Pdf/";
        $template = $presenter->createTemplate()->setFile($templatePath . "@pdf.layout.latte");

        $template->cv = $cv;
        $template->userEntity = $this->userService->find($cv->userId);
        $template->pageInfo = $pageInfo;
        $templatePath = "templates/" . $this->templateFile . ".latte";
        $template->templateName = $templatePath;
        $template->theme = $this->cssTheme;
        // Tip: In template to make a new page use <pagebreak>

        $template->translator = $translator;
        $template->registerHelper("CvFullName", "\Model\Entity\CvEntity::helperGetFullName");
        $template->registerHelper("CvAdress", "\Model\Entity\CvEntity::helperGetAddress");
        $template->registerHelper("CvLanguage", "\Model\Entity\CvEntity::helperGetLanguage");
        $template->registerHelper("CvItSkillLang", "\Model\Entity\CvItScaleEntity::helperGetLanguage");
        $template->registerHelper("CvItSkillLScale", "\Model\Entity\CvItScaleEntity::helperGetScale");
        $template->registerHelper("CvNationality", "\Model\Entity\CvEntity::helperGetNationality");
        $template->registerHelper("CvSector", "\Model\Entity\CvEntity::helperGetSector");
        $template->registerHelper("CvLicenses", "\Model\Entity\CvEntity::helperGetLicenses");
        $template->registerHelper("CvLangLevel", "\Model\Entity\CvLangEntity::helperGetScale");
        $template->registerHelper("CvLangLevelHtm1", "\Model\Entity\CvLangEntity::helperGetScaleHtm1");
        $template->registerHelper("CvEducInstitution", "\Model\Entity\CvEducEntity::helperGetInstitution");
        $template->registerHelper("CvEducDates", "\Model\Entity\CvEducEntity::helperGetDates");
        $template->registerHelper("CvWorkDates", "\Model\Entity\CvWorkEntity::helperGetDates");
        $template->registerHelper("CvWorkReferences", "\Model\Entity\CvWorkEntity::helperGetReferences");
        $template->registerHelper("currency", "\CommonHelpers::currency");

        $pdf = new \PdfResponse($template, $presenter->presenter);

        // PDF settings
        $pdf->documentTitle = $cv->name;
        $pdf->pageFormat = "A4"; // wide format
        $pdf->pageOrientation = \PDFResponse::ORIENTATION_LANDSCAPE;
        $pdf->documentAuthor = $pageInfo->author;
        $pdf->pageMargins = "23,15,16,15,9,9";
        $pdf->getMPDF()->SetHTMLHeader("<table class='header'><tr><td class='left'></td><td class='middle'>Curriculum Vitae</td><td class='right1'></td><td class='right2'></td><td class='right3'></td></tr></table>"); // footer
        $pdf->getMPDF()->SetHTMLFooter("<table class='footer'><tr><td class='left1'></td><td class='left2'></td><td class='left3'></td><td class='middle'></td><td class='right'></td></tr></table>"); // footer

        return $pdf;
    }
}
