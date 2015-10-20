<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Model\Service\JobService,
    Model\Service\UserService,
    Nette\Http\Session;

/**
 * Job Filter Form
 *
 * @author Petr Poupě
 */
class JobFilterForm extends AppForms
{

    const FAST_SEARCH = 1;
    const SMART_SEARCH = 2;

    /** @var JobService */
    private $jobs;

    /** @var UserService */
    private $users;

    /** @var Session */
    private $session;

    /** @var mixed  */
    protected $skills;

    public function __construct(Presenter $presenter, JobService $jobs, UserService $users, Session $session)
    {
        parent::__construct(get_class($this), $presenter, FALSE);

        $this->jobs = $jobs;
        $this->users = $users;
        $this->session = $session;
        $this->skills = $jobs->buildSkills();
    }

    private function setDefaults()
    {
        $jobFilter = $this->session->getSection('smartJobFilter');
        $this->form->setDefaults(array(
            'text' => $jobFilter->text,
            'location' => $jobFilter->location,
            'skills' => is_array($jobFilter->skills) || $jobFilter->skills instanceof \Nette\ArrayHash ? $jobFilter->skills : array(),
            'salary' => $jobFilter->isSalary,
            'min' => $jobFilter->salaryMin,
            'max' => $jobFilter->salaryMax,
        ));
    }

    protected function createComponent($name)
    {
        $this->form->setMethod("post");

        $this->form->addText('text', 'Keyword Search')
                ->setAttribute("placeholder", "Input keyword")
                ->setAttribute("class", "search");

        $locations = $this->jobs->getLocations();
        $types = array(1 => 'Full-Time', 2 => 'Part-Time', 3 => 'Contract');

        $this->form->addMultiSelect("location", "Location", $locations, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addMultiSelect("jobtype", "Job type", $types, 10)
                ->setAttribute("class", "tagged-multiselect multiselect-plus");

        $this->form->addCheckbox("salary", "Filter by salary")
                ->addCondition(Form::EQUAL, TRUE)
                ->toggle('sallaryBox');
        $this->form->addText('min', 'From')
                ->setAttribute("class", "salary range from");
        $this->form->addText('max', 'To')
                ->setAttribute("class", "salary range to")
                ->setOption('description', "€ per annum");

        $skills = $this->skills;
        $scale = \Model\Entity\CvItScaleEntity::scale();
        $skillsContainer = $this->form->addContainer("skills");
        foreach ($skills as $skillCategory => $skillGroups) {
            foreach ($skillGroups as $skillGroup) {
                foreach ($skillGroup as $skillId => $skillName) {
                    $container = $skillsContainer->addContainer($skillId);
                    $container->addSelect('scale', 'Scale', $scale)
                            ->setAttribute("class", "slider live");
                    $container->addText('number', "Years")
                            ->setAttribute("type", "number")
                            ->setAttribute("min", "0")
                            ->setAttribute("class", "number")
                            ->setDefaultValue(0);
                }
            }
        }

        $this->form->addSubmit('fast_search', 'Search');

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        $jobFilter = $this->session->getSection('smartJobFilter');

        $jobFilter->text = !empty($form->values->text) ? $form->values->text : NULL;
        $jobFilter->type = self::SMART_SEARCH;

        $jobFilter->location = !empty($form->values->location) ? $form->values->location : NULL;
        $jobFilter->isSalary = (bool) $form->values->salary;
        $jobFilter->salaryMin = !empty($form->values->min) ? $form->values->min : NULL;
        $jobFilter->salaryMax = !empty($form->values->max) ? $form->values->max : NULL;
        $jobFilter->skills = !empty($form->values->skills) ? $form->values->skills : array();
        $this->saveSettings();
        $this->presenter->redirect("this");
    }

    private function saveSettings()
    {
        $jobFilter = $this->session->getSection('smartJobFilter');
        $settings = array();
        foreach ($jobFilter as $key => $item) {
            $settings[$key] = $item;
        }
        $user = $this->users->find($this->user->getId());
        if ($user->id !== NULL) {
            $user->smartFilterSettings = $settings;
            $this->users->save($user);
        }
    }

    public function render()
    {
        $this->template->skills = $this->skills;
        $this->template->maxSalary = $this->jobs->getMaxSalary();
        parent::render();
    }

}

?>
