<?php

namespace ServiceModule;

use Nette\Diagnostics\Debugger;

/**
 * XML Profesia Parser Presenter
 *
 * @author Petr Poupě
 */
class XmlProfesiaPresenter extends XmlParserPresenter
{

    const BOOK = "book";
    const DATA = "data";
    const LANG_SK = "sk";
    const LANG_EN = "en";
    const LANG_CZ = "cz";
    const LANG_HU = "hu";

    private $logName = "XML Profesia";
    private $importName = "profesia";
    protected $bookUrl = "http://www.profesia.sk/import_code_books.php";
    protected $bookFolder = "profesia/book";
    protected $dataUrlSk = "http://www.grafton.sk/files/inzerce/profesia-detail-1.xml";
    protected $dataUrlCs = "http://www.grafton.cz/files/inzerce/profesia-detail/profesia-detail.xml";
    protected $dataFolder = "profesia/data";

    public function startup()
    {
        parent::startup();
        $this->checkAccess("xml", "access");
    }

    private function getDataLangs($keys = TRUE)
    {
        $langs = array(
            "cs" => "cz",
            "sk" => "sk",
        );
        return $keys ? array_keys($langs) : $langs;
    }

    private function getBookLangs($keys = TRUE)
    {
        $langs = array(
            "cs" => "cz",
            "sk" => "sk",
            "en" => "en",
            "hu" => "hu",
        );
        return $keys ? array_keys($langs) : $langs;
    }

    private function getWebs()
    {
        $webs = array(
            "www.profesia.sk",
            "www.workania.hu",
            "www.profesia.cz",
        );
        return $webs;
    }

    private function translateBookLang($lang)
    {
        $langs = $this->getBookLangs(FALSE);
        return array_key_exists($lang, $langs) ? $langs[$lang] : $lang;
    }

    public function actionDefault()
    {
        $this->context->logger->logMessage("$this->logName started ---------------------");

        $bookLangs = $this->getBookLangs();

        $this->context->logger->logMessage("$this->logName start books");
        foreach ($bookLangs as $lang) {
            $this->downloadBook($lang, $responseBook);
            $this->executeBook($responseBook, $lang);
        }

        $dataLangs = $this->getDataLangs();

        $this->context->logger->logMessage("$this->logName start data");
        foreach ($dataLangs as $lang) {
            $this->context->logger->logMessage("$this->logName set lang to: $lang");
            $this->downloadData($lang, $responseData);
            $importName = \CommonHelpers::concatStrings("_", $this->importName, $lang);
            $this->executeData($responseData, $importName);
        }

        $this->context->logger->logMessage("$this->logName ended -----------------------");
    }

    public function actionDownload($what = self::DATA, $lang = NULL)
    {
        $this->context->logger->logMessage("$this->logName (only download) started (lang: $lang)");
        $this->context->logger->logMessage("Downloading {$what}");

        switch ($what) {
            case self::BOOK:
                $this->downloadBook($lang);
                break;
            case self::DATA:
            default:
                $this->downloadData($lang);
                break;
        }
        $this->context->logger->logMessage("$this->logName (only download) ended");
    }

    public function renderDownload()
    {
        $this->setView("default");
    }

    public function actionFromArchive($what = self::DATA, $lang = NULL)
    {
        $this->context->logger->logMessage("$this->logName (from archive) started (lang: $lang)");
        $this->context->logger->logMessage("Executing {$what}");

        switch ($what) {
            case self::BOOK:
                $folder = \CommonHelpers::concatStrings("/", $this->bookFolder, $lang);
                $source = $this->readLastArchive($folder);
                $this->executeBook($source, $lang);
                break;
            case self::DATA:
            default:
                $folder = \CommonHelpers::concatStrings("/", $this->dataFolder, $lang);
                $source = $this->readLastArchive($folder);
                $importName = \CommonHelpers::concatStrings("_", $this->importName, $lang);
                $this->executeData($source, $importName);
                break;
        }
        $this->context->logger->logMessage("$this->logName (from archive) ended");
    }

    public function renderFromArchive()
    {
        $this->setView("default");
    }

    /*     * ** PRIVATE **** */

    private function downloadBook($lang, &$response = NULL, &$headers = NULL)
    {
        $folder = \CommonHelpers::concatStrings("/", $this->bookFolder, $lang);
        $url = new \Nette\Http\Url($this->bookUrl);
        $url->appendQuery(array(
            "lang" => $this->translateBookLang($lang),
        ));
        if ($this->download($url, $response, $headers)) {
            $this->archive($response, $folder, "xml");
            return TRUE;
        }
        return FALSE;
    }

    private function downloadData($lang, &$response = NULL, &$headers = NULL)
    {
        $this->context->logger->logMessage("$this->logName start downloading data");
        switch ($lang) {
            case "cs":
                $dataUrl = $this->dataUrlCs;
                break;
            case "sk":
            default:
                $dataUrl = $this->dataUrlSk;
                break;
        }
        $folder = \CommonHelpers::concatStrings("/", $this->dataFolder, $lang);
        $url = new \Nette\Http\Url($dataUrl);
        if ($this->download($url, $response, $headers)) {
            $this->archive($response, $folder, "xml");
            return TRUE;
        }
        return FALSE;
    }

    private function executeBook($source, $lang)
    {
        $xml = $this->parseXml($source);

        foreach ($xml->categories->category as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findCategory($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->positions->position as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findPosition((int) $id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = (int) $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->offercategorypositions->catpos as $item) {
            $category = (int) $item["category"];
            $position = (int) $item["position"];
            $entity = $this->context->profesia->findOfferCategoryPosition($category, $position, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrCategory = $category;
            $entity->attrPosition = $position;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->regions->region as $item) {
            $id = (int) $item["id"];
            $parentId = (int) $item["parent_id"];
            $entity = $this->context->profesia->findRegion($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $entity->attrParentId = $parentId;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($this->getWebs() as $webname) {
            foreach ($xml->jobtypes->$webname->jobtype->type as $item) {
                $id = (int) $item["id"];
                $entity = $this->context->profesia->findJobtype($id, $webname, $lang);
                $entity->lang = $lang;
                $entity->name = (string) $item;
                $entity->web = $webname;
                $entity->attrId = $id;
                $this->context->profesia->saveBook($entity);
            }
        }

        foreach ($xml->specializations->specialization as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findSpecialization($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->skills->skill as $item) {
            $id = (int) $item["id"];
            $parentId = (int) $item["parent_id"];
            $entity = $this->context->profesia->findSkill($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $entity->attrParentId = $parentId;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->skillcategories->category as $item) {
            $id = (int) $item["id"];
            $parentId = (int) $item["parent_id"];
            $catLevelId = (int) $item["cat_level_id"];
            $entity = $this->context->profesia->findSkillCategory($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $entity->attrParentId = $parentId;
            $entity->attrCatLevelId = $catLevelId;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->skilllevels->level as $item) {
            $id = (int) $item["id"];
            $catLevelId = (int) $item["cat_level_id"];
            $entity = $this->context->profesia->findSkillLevel($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $entity->attrCatLevelId = $catLevelId;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->businessareas->area as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findBussinesArea($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->educationlevels->level as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findEducationLevel($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->currencies->currency as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findCurrency($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }

        foreach ($xml->summerjobs->summerjob_position_id as $item) {
            $id = (int) $item["id"];
            $entity = $this->context->profesia->findSummerJob($id, $lang);
            $entity->lang = $lang;
            $entity->name = (string) $item;
            $entity->attrId = $id;
            $this->context->profesia->saveBook($entity);
        }
    }

    private function executeData($source, $importName)
    {
        $this->context->logger->logMessage("$this->logName start executing data");

        $xml = $this->parseXml($source);

        if ($xml) {
            $counter = 0;
            $ids = array();
            foreach ($xml->job as $job) {

                $position = $this->context->profesia->find((int) $job["externalid"]);
//                if ((int) $job["externalid"] === 10128427) {
//                    Debugger::barDump($position);
//                }

                $position->importedFrom = $importName;
                $position->externalid = $job["externalid"];
                $position->position = $job->position;
                $position->refnr = $job->refnr;
                $position->datecreated = $job->datecreated;
                $position->offerlocationDescription = $job->offerlocation["description"];
                $position->jobtasks = $job->jobtasks;
                $position->minsalary = $job->minsalary;
                $position->maxsalary = $job->maxsalary;
                $position->currencyId = $job->currency["id"];
                $position->salaryPeriod = $job->salary_period;
                $position->startdate = $job->startdate;
                $position->otherbenefits = $job->otherbenefits;
                $position->noteforcandidate = $job->noteforcandidate;
                $position->languageconjuction = $job->languageconjuction;
                $position->validforgraduate = $job->validforgraduate;
                $position->prerequisites = $job->prerequisites;
                $position->shortcompanycharacteristics = $job->shortcompanycharacteristics;
                $position->contactemail = $job->contactemail;
                $position->contactname = $job->contactfullname;
                $position->contactphone = $job->contactphone;
                $position->contactaddress = $job->contactaddress;
                $offerlanguage = (string) $job->offerlanguage;
                switch ($offerlanguage) {
                    case "cz":
                        $offerlanguage = "cs";
                        break;
                    default:
                        break;
                }
                $position->offerlanguage = $offerlanguage;
                $position->customcategory = $job->customcategory;

                if ($job->offerlocation->count() && $job->offerlocation->location->count()) {
                    $position->offerLocations = NULL;
                    foreach ($job->offerlocation->location as $item) {
                        $position->offerLocations = $item["id"];
                    }
                } else {
                    $position->offerLocations = NULL;
                }

                if ($job->jobtype->count() && $job->jobtype->type->count()) {
                    $position->jobTypes = NULL;
                    foreach ($job->jobtype->type as $item) {
                        $position->jobTypes = $item["id"];
                    }
                } else {
                    $position->jobTypes = NULL;
                }

                if ($job->educationlevel->count() && $job->educationlevel->education->count()) {
                    $position->educationLevels = NULL;
                    foreach ($job->educationlevel->education as $item) {
                        $position->educationLevels = $item["id"];
                    }
                } else {
                    $position->educationLevels = NULL;
                }

                if ($job->offerskills->count() && $job->offerskills->skill->count()) {
                    $position->offerSkills = NULL;
                    foreach ($job->offerskills->skill as $item) {
                        $position->offerSkills = array(
                            "id" => (int) $item["id"],
                            "level" => (int) $item["level"],
                        );
                    }
                } else {
                    $position->offerSkills = NULL;
                }

                if ($job->offercategories->count() && $job->offercategories->category->count()) {
                    $position->offerCategories = NULL;
                    foreach ($job->offercategories->category as $item) {
                        $position->offerCategories = $item["id"];
                    }
                } else {
                    $position->offerCategories = NULL;
                }

                if ($job->offercategorypositions->count() && $job->offercategorypositions->catpos->count()) {
                    $position->offerCategoryPositions = NULL;
                    foreach ($job->offercategorypositions->catpos as $item) {
                        $position->offerCategoryPositions = array(
                            "category" => (int) $item["category"],
                            "position" => (int) $item["position"],
                        );
                    }
                } else {
                    $position->offerCategoryPositions = NULL;
                }

                $this->context->profesia->save($position);
                $ids[$position->id] = $position->id;

//                if ((int) $job["externalid"] === 10128427) {
//                    Debugger::barDump($position);
//                    $this->terminate();
//                }

                $counter++;
//            break;
            }
            $this->context->profesia->deleteUnused(array_keys($ids), $importName);
            $this->context->logger->logMessage("$this->logName $counter items was saved");
        }
    }

}
