<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvItScaleEntity,
    Nette\Application\UI\Presenter,
    Model\Service\CvService,
    Model\Entity\CvEntity;

/**
 * Step11 Form
 *
 * @author Petr Poupě
 * @author Marek Šneberger
 */
class Step11Form extends StepsForm
{

    /** @var mixed  */
    protected $skills;

    public function __construct(Presenter $presenter, CvService $service, CvEntity $cv, $step)
    {
        parent::__construct($presenter, $service, $cv, $step);
        $this->skills = $this->service->buildSkills();
    }

    /**
     * @param $name
     *
     * @return Form|\Nette\ComponentModel\IComponent
     */
    protected function createComponent($name)
    {
        $skills = $this->skills;
        $scale = CvItScaleEntity::scale();
        foreach ($skills as $skillCategory => $skillGroups) {
            foreach ($skillGroups as $skillGroup) {
                foreach ($skillGroup as $skillId => $skillName) {
                    $container = $this->form->addContainer($skillId);
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
        
        $this->form->addTextArea("other_it_skills", "Any other IT skills");

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;

        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function onSuccess(Form $form)
    {
        parent::onSuccess($form);
    }

    /**
     * Sets default form values for skills section
     */
    private function setDefaults()
    {
        $defaults = [];
        foreach ($this->cv->itSkills as $skill) {
            $defaults[$skill->skill_id]['scale'] = $skill->scale;
            $defaults[$skill->skill_id]['number'] = $skill->years;
        }
        $defaults["other_it_skills"] = $this->cv->otherItSkills;
        $this->form->setDefaults($defaults);
    }

    /**
     * Fill entity from form
     *
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     * @param bool $submByBtn
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $entity->addSkill((array) $values);
        $entity->otherItSkills = $values->other_it_skills;
    }

    public function render()
    {
        $this->template->skills = $this->skills;
        parent::render();
    }

}
