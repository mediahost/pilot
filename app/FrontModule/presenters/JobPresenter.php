<?php

namespace FrontModule;

use Model\Entity\ActionLogEntity;

/**
 * Job presenter.
 */
class JobPresenter extends BasePresenter
{
    
    public function startup()
    {
        $this->flashMessage("This section has been removed!", "warning");
        $this->redirect("Dashboard:");
    }

    public function actionDefault($id)
    {
        $job = $this->context->profesia->find($id);
        if ($job->id !== NULL) {

            $link = "#";
            switch ($job->importedFrom) {
                case "profesia_cs":
                    $url = "http://www.grafton.cz/cs-CZ/nabidka-prace/";
                    break;
                case "profesia_sk":
                    $url = "http://www.grafton.sk/ponuka-prace/";
                    break;
            }

            if (isset($url) && preg_match("~^\d+\-\d+\-(\d+)/\w+$~i", $job->refnr, $matches)) {
                $refN = $matches[1];
                $position = $job->position;
                $place = \CommonHelpers::helperFirst($job->offerLocationNames);
                $path = \Nette\Utils\Strings::webalize("$position $place $refN");
                $link = "{$url}{$path}/";
            }

            $skills = NULL;
            $conjuct = $this->translator->translate(($job->languageconjuction === "or" ? "or" : "and"));
            if (is_array($job->offerSkills)) {
                foreach ($job->offerSkills as $offerskill) {
                    if (is_array($offerskill) && array_key_exists("id", $offerskill) && array_key_exists("level", $offerskill)) {
                        if (array_key_exists($offerskill["id"], $job->offerSkillNames) && array_key_exists($offerskill["level"], $job->offerSkillLevels)) {
                            if ($skills !== NULL) {
                                $skills .= " $conjuct ";
                            }
                            $skills .= $job->offerSkillNames[$offerskill["id"]] . " (" . $job->offerSkillLevels[$offerskill["level"]] . ")";
                        }
                    }
                }
            }
            $this->template->skills = $skills;

            $this->template->origLink = $link;

            if (is_array($job->tags))
                $this["tagsForm"]->setDefaults($id, $job->tags);

            $this->context->actionlogs->logSerie(ActionLogEntity::READ_JOB, $this->user->getId(), array($job->externalid));
            $this->template->canEdit = $this->user->isAllowed("backend", "access");
        }
        $this->template->job = $job;
    }

    protected function createComponentTagsForm()
    {
        $form = new \AppForms\EditJobTagsForm($this, $this->context->profesia);
        return $form;
    }

}
