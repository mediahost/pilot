<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\ProfesiaService,
    Model\Service\UserService,
    Nette\Http\Session;

/**
 * Job Filter Form - advanced version
 *
 * @author Petr Poupě
 */
class JobFilterAdvancedForm extends AppForms
{

    const FAST_SEARCH = 1;
    const SMART_SEARCH = 2;

    /** @var ProfesiaService */
    private $profesia;

    /** @var UserService */
    private $users;

    /** @var Session */
    private $session;

    public function __construct(Presenter $presenter, ProfesiaService $profesia, UserService $users, Session $session)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->profesia = $profesia;
        $this->users = $users;
        $this->session = $session;
    }

    private function setDefaults()
    {
        $jobFilter = $this->session->getSection('smartJobFilter');
        $this->form->setDefaults(array(
            'text' => $jobFilter->text,
            'location' => $jobFilter->location,
            'tag' => $jobFilter->tag,
            'education' => $jobFilter->education,
            'lang' => $jobFilter->lang,
            'category' => $jobFilter->category,
            'position' => $jobFilter->position,
            'jobtype' => $jobFilter->jobtype,
            'interval' => $jobFilter->interval,
            'available' => $jobFilter->available instanceof \Nette\DateTime ? $jobFilter->available->format("Y-m-d") : NULL,
            'salary' => $jobFilter->isSalary,
            'min' => $jobFilter->salaryMin,
            'max' => $jobFilter->salaryMax,
        ));
    }

    protected function createComponent($name)
    {
        $this->form->setMethod("post");

        $this->form->addText('text', 'Contain')
                ->setAttribute("placeholder", "input what you search (position, locality, etc.)")
                ->setAttribute("class", "search");

        switch ($this->presenter->lang) {
            case "cs":
                $parentRegionId = 12;
                break;
            case "sk":
                $parentRegionId = 9;
                break;
            case "hu":
                $parentRegionId = 1100;
                break;
            default:
                $parentRegionId = NULL;
                break;
        }
        $intervals = array(
            1 => "1 day",
            2 => "2 days",
            3 => "3 days",
            4 => "1 week",
            5 => "2 weeks",
            6 => "1 month",
            7 => "2 months",
        );
        $langs = array();
        $tags = $this->profesia->getTags($this->lang);
        $locations = $this->profesia->getLocations($this->lang, $parentRegionId);
        $positions = $this->profesia->getPositions($this->lang);
        $categories = $this->profesia->getCategories($this->lang);
        $educations = $this->profesia->getEducations($this->lang);
        $types = $this->profesia->getJobtypes($this->lang);


        $this->form->addMultiSelect("location", "Location", $locations, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("tag", "Tag", $tags, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("education", "Education", $educations, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("lang", "Languages", $langs, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("category", "Category", $categories, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("position", "Position", $positions, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("jobtype", "Job type", $types, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addDatePicker('available', "Available date")
                ->setAttribute("readonly", "readonly")
                ->addCondition(Form::FILLED)
                ->addRule(Form::RANGE, 'Entered date is not within allowed range.', array(new \DateTime('today'), NULL));

        $this->form->addCheckbox("salary", "Filter by salary")
                ->addCondition(Form::EQUAL, TRUE)
                ->toggle('sallaryBox');
        $this->form->addText('min', 'From')
                ->setAttribute("class", "salary range from");
        $this->form->addText('max', 'To')
                ->setAttribute("class", "salary range to")
                ->setOption('description', "€ per annum");

        $this->form->addSubmit('fast_search', 'Fast Search');
        $this->form->addSubmit('smart_search', 'Smart Search');

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $jobFilter = $this->session->getSection('smartJobFilter');

        if ($form['fast_search']->submittedBy) {
            $jobFilter->type = self::FAST_SEARCH;
            $jobFilter->text = !empty($form->values->text) ? $form->values->text : NULL;
        } else if ($form['smart_search']->submittedBy) {
            $jobFilter->type = self::SMART_SEARCH;
            $jobFilter->text = NULL;

            $jobFilter->location = !empty($form->values->location) ? $form->values->location : NULL;
            $jobFilter->tag = !empty($form->values->tag) ? $form->values->tag : NULL;
            $jobFilter->education = !empty($form->values->education) ? $form->values->education : NULL;
            $jobFilter->lang = !empty($form->values->lang) ? $form->values->lang : NULL;
            $jobFilter->category = !empty($form->values->category) ? $form->values->category : NULL;
            $jobFilter->position = !empty($form->values->position) ? $form->values->position : NULL;
            $jobFilter->jobtype = !empty($form->values->jobtype) ? $form->values->jobtype : NULL;
            $jobFilter->interval = !empty($form->values->interval) ? $form->values->interval : NULL;

            $jobFilter->available = !empty($form->values->available) ? new \Nette\DateTime($form->values->available) : NULL;
            $jobFilter->isSalary = (bool) $form->values->salary;
            $jobFilter->salaryMin = !empty($form->values->min) ? $form->values->min : NULL;
            $jobFilter->salaryMax = !empty($form->values->max) ? $form->values->max : NULL;
        }
        $this->saveSettings();
        $this->presenter->redirect("this");
    }

    private function saveSettings()
    {
        $jobFilter = $this->session->getSection('smartJobFilter');
        $settings = array();
        foreach ($jobFilter as $key => $item) {
            switch ($key) {
                case "available":
                    if ($item instanceof \Nette\DateTime)
                        $settings[$key] = $item->format('Y-m-d H:i:s');
                    break;
                default:
                    $settings[$key] = $item;
                    break;
            }
        }
        $user = $this->users->find($this->user->getId());
        if ($user->id !== NULL) {
            $user->smartFilterSettings = $settings;
            $this->users->save($user);
        }
    }

}

?>
