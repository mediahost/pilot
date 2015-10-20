<?php

namespace AppForms;

use \Nette\Application\UI\Form,
    Model\Entity\CvLangEntity;

/**
 * Step8 Form
 *
 * @author Petr Poupě
 */
class Step8Form extends StepsForm
{

    protected function createComponent($name)
    {
        $languages = CvLangEntity::languages();
        $scale = CvLangEntity::scale();

        $this->form->addGroup("Mother tongue");
        $this->form->addSelect('mother_language', "Mother tongue", $languages)
                ->setPrompt(" - select - ");

        $this->form->addGroup("Other languages");
        $this->form->addHidden('changed_id')
                        ->getControlPrototype()->class = "changeId";
        $this->form->addSelect('other_lang', "Language", $languages)
                ->setPrompt(" - select - ");
//                ->addRule(Form::FILLED, "Select other language");


        $this->form->addSelect('listening', "Listening", $scale)
                ->setAttribute("class", "slider");
        $this->form->addSelect('reading', "Reading", $scale)
                ->setAttribute("class", "slider");

        $this->form->addSelect('interaction', "Spoken interaction", $scale)
                ->setAttribute("class", "slider");
        $this->form->addSelect('production', "Spoken production", $scale)
                ->setAttribute("class", "slider");

        $this->form->addSelect('writing', "Writing", $scale)
                ->setAttribute("class", "slider");

        $this->form->addSubmit('send', 'Save')
                ->setAttribute("class", "button");

        $this->setDefaults();

        $this->form->onSuccess[] = $this->onSuccess;
        return $this->form;
    }

    public function onSuccess(Form $form)
    {
        if ($form['send']->submittedBy && $form->values->other_lang === NULL) {
            $form->addError("Select other language");
        } else {
            parent::onSuccess($form);
        }
    }
    
    public function afterSuccess(Form $form, $submittedBy = FALSE)
    {
        if ($submittedBy) {
            $form->setValues(array(
                'other_lang' => NULL,
                'listening' => NULL,
                'reading' => NULL,
                'interaction' => NULL,
                'production' => NULL,
                'writing' => NULL,
            ));
        } else {
            parent::afterSuccess($form, $submittedBy);
        }
    }

    private function setDefaults()
    {
        $this->form->setDefaults(array(
            'mother_language' => $this->cv->motherLanguage,
        ));
        $this->form->setDefaults($this->defaults);
    }

    public function setLang(CvLangEntity $lang)
    {
        $this->defaults = array(
            'changed_id' => $lang->id,
            'other_lang' => $lang->lang,
            'listening' => $lang->listening,
            'reading' => $lang->reading,
            'interaction' => $lang->interaction,
            'production' => $lang->production,
            'writing' => $lang->writing,
        );
    }

    /**
     * Fill entity from form
     * @param \Nette\ArrayHash $values
     * @param \Model\Entity\CvEntity $entity
     */
    protected function formToEntity(\Nette\ArrayHash $values, \Model\Entity\CvEntity &$entity, $submByBtn = FALSE)
    {
        $keys = array(// itemKey => valueKey
            'motherLanguage' => "mother_language",
        );
        $this->fillEntity($entity, $values, $keys);

        if ($submByBtn) {
            $lang = new CvLangEntity;
            $keys = array(
                'id' => "changed_id",
                'lang' => "other_lang",
                'listening' => "listening",
                'reading' => "reading",
                'interaction' => "interaction",
                'production' => "production",
                'writing' => "writing",
            );
            foreach ($keys as $itemKey => $valueKey) {
                if (isset($values->$valueKey) && $values->$valueKey !== "")
                    $lang->$itemKey = $values->$valueKey;
            }
            $entity->addLanguage($lang);
        }
    }

    public function render()
    {
        $this->template->languages = $this->cv->getLanguages();
        $this->template->registerHelper("CvLanguage", "\Model\Entity\CvEntity::helperGetLanguage");
        parent::render();
    }

    public function handleEditLang($langId)
    {
        /* @var $lang \Model\Entity\CvLangEntity */
        $lang = $this->cv->getLanguage($langId);
        $this->setLang($lang);
    }

    public function handleDeleteLang($langId)
    {
        $this->service->deleteLanguage($this->cv, $langId);
        $this->presenter->flashMessage('Selected language was deleted', 'success');
    }

}

?>
