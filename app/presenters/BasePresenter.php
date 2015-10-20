<?php

use \Acl,
    \NetteTranslator\Gettext;

/**
 * Base presenter for all application presenters.
 * @property-read Model\Security\User $user
 */
abstract class BasePresenter extends \Nette\Application\UI\Presenter
{

    /** @persistent */
    public $lang;

    /** @persistent */
    public $backlink;

    /** @var \NetteTranslator\Gettext */
    public $translator;

    /**
     * Allowed languages - values for automatic detection from explorer
     * search on Google: "locale codes"
     * http://download1.parallels.com/Plesk/Plesk8.2/Doc/plesk-8.2-win-l10n-guide/39382.htm
     * @var array 
     */
    protected $langs = array();

    /** Default information about page */
    public $pageInfo;

    public function startup()
    {
        parent::startup();
        $this->user->setAuthorizator(new \Acl\Permission());

        // detekce jazyka nastaveného prohlížečem
        if (!isset($this->lang)) {
            $this->lang = $this->context->httpRequest->detectLanguage(array_keys($this->langs));
        }
        if (!isset($this->lang) || !array_key_exists($this->lang, $this->langs)) { // pokud není nastaven, použijeme defaultní z configu
            $this->lang = $this->context->parameters["lang"]["default"];
        }

        if (!isset($this->pageInfo)) {
            $this->pageInfo = new \Nette\ArrayHash;
            foreach ($this->context->parameters["pageInfo"] as $param => $info) {
                $this->pageInfo->$param = $info;
            }
            $this->pageInfo->locale = array_key_exists($this->lang, $this->langs) ? $this->langs[$this->lang]->code : NULL;
            $this->pageInfo->url = $this->context->httpRequest->getUrl();
            $this->pageInfo->isDevelopment = \Nette\Config\Configurator::detectDebugMode();
            $this->pageInfo->isProduction = \Nette\Config\Configurator::detectProductionMode();
            $disableJsLessCompiler = isset($this->context->parameters["disableJsLessCompiler"]) ? $this->context->parameters["disableJsLessCompiler"] : FALSE;
            $this->pageInfo->jsLessCompile = $this->pageInfo->isDevelopment && !$disableJsLessCompiler;
        }
        $this->template->pageInfo = $this->pageInfo;
        $this->template->robots = $this->pageInfo->robots;
    }

    protected function beforeRender()
    {
        $this->template->lang = $this->lang;
        $this->template->langs = $this->langs;

        // for modules
        $this->template->viewName = $this->view;
        $this->template->root = isset($_SERVER['SCRIPT_FILENAME']) ? realpath(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) : NULL;

        $a = strrpos($this->name, ':');
        if ($a === FALSE) {
            $this->template->moduleName = '';
            $this->template->presenterName = $this->name;
        } else {
            $this->template->moduleName = substr($this->name, 0, $a + 1);
            $this->template->presenterName = substr($this->name, $a + 1);
        }
    }

    protected function checkAccess($resource = \Acl\Permission::ALL, $privilege = \Acl\Permission::ALL, $redirect = TRUE)
    {
        if (!$this->user->isAllowed($resource, $privilege)) {
            if ($redirect) {
                $bl = $this->storeRequest();
                $this->redirect(":Front:Sign:in", array('backlink' => $bl));
            }
        }
    }

    /**
     * Inject translator
     * @param \NetteTranslator\Gettext
     */
    public function injectTranslator(Gettext $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Set translator for template
     * @param type $class
     * @return type
     */
    public function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);

        $this->translator->setLang($this->lang); // nastavíme jazyk
        $template->setTranslator($this->translator);

        return $template;
    }

    /**
     * Translating FlashMessages
     * @param type $message
     * @param type $type
     * @return type
     */
    public function flashMessage($message, $type = "info")
    {
        $message = $this->translator->translate($message);
        return parent::flashMessage($message, $type);
    }

    public function templatePrepareFilters($template)
    {
        $template->registerFilter($latte = $this->context->nette->createLatte());

        $set = Nette\Latte\Macros\MacroSet::install($latte->getCompiler());
        $set->addMacro('ifCurrentIn', function($node, $writer) {
            return $writer->write('foreach (%node.array as $l) { if ($_presenter->isLinkCurrent($l)) { $_c = true; break; }} if (isset($_c)): ');
        }, 'endif; unset($_c);');
    }

    protected function extendTemplate()
    {
        $this->template->translator = $this->translator;
        $this->template->registerHelper("CvFullName", "\Model\Entity\CvEntity::helperGetFullName");
        $this->template->registerHelper("CvYearsOld", "\Model\Entity\CvEntity::helperGetYearsOld");
        $this->template->registerHelper("CvAdress", "\Model\Entity\CvEntity::helperGetAddress");
        $this->template->registerHelper("CvLanguage", "\Model\Entity\CvEntity::helperGetLanguage");
        $this->template->registerHelper("CvNationality", "\Model\Entity\CvEntity::helperGetNationality");
        $this->template->registerHelper("CvSector", "\Model\Entity\CvEntity::helperGetSector");
        $this->template->registerHelper("CvLicenses", "\Model\Entity\CvEntity::helperGetLicenses");
        $this->template->registerHelper("CvLangLevel", "\Model\Entity\CvLangEntity::helperGetScale");
        $this->template->registerHelper("CvLangLevelHtm1", "\Model\Entity\CvLangEntity::helperGetScaleHtm1");
        $this->template->registerHelper("CvEducInstitution", "\Model\Entity\CvEducEntity::helperGetInstitution");
        $this->template->registerHelper("CvEducDates", "\Model\Entity\CvEducEntity::helperGetDates");
        $this->template->registerHelper("CvWorkDates", "\Model\Entity\CvWorkEntity::helperGetDates");
        $this->template->registerHelper("CvWorkReferences", "\Model\Entity\CvWorkEntity::helperGetReferences");
        $this->template->registerHelper("currency", "\CommonHelpers::currency");
        $this->template->registerHelper("CvItSkillLang", "\Model\Entity\CvItScaleEntity::helperGetLanguage");
        $this->template->registerHelper("CvItSkillLScale", "\Model\Entity\CvItScaleEntity::helperGetScale");
    }

}
