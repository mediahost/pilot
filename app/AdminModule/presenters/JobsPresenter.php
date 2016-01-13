<?php

namespace AdminModule;

/**
 * Description of JobsPresenter
 *
 * @author Radim Křek
 */
class JobsPresenter extends BasePresenter
{
    
    /** @var \App\Components\JobUserNotesFactory */
    protected $jobUserNotesFactory;
    
    public function injectJobUserNotesFactory(\App\Components\JobUserNotesFactory $factory)
    {
        $this->jobUserNotesFactory = $factory;
    }

    public function startup()
    {
        parent::startup();
        $this->checkAccess("backend", "access");
    }

    public function actionDefault()
    {
        $this->template->jobs = $this->context->jobs->getAll();
    }

    public function actionadd()
    {
        $this->actionEdit();
        $this->setView("edit");
    }

    public function actionEdit($id = NULL)
    {
        $this['editJobForm']->setId($id);
    }

    public function actionEditCategory($id = NULL)
    {
        $category = $this->context->jobscategory->find($id, $this->lang);
        $this['editCategoryForm']->setDefaults($category->to_array());
    }
	
	public function actionEditLocation($id = NULL, $parent = NULL)
	{
		if ($parent)
		{
			$entity = new \Model\Entity\LocationEntity(array('parent_id' => $parent));
			$this['editLocationForm']->setDefaults($entity);
		}
		else
		{
			$location = $this->context->location->findById($id);
			$this['editLocationForm']->setDefaults($location);
		}
	}

    public function actionCategory()
    {
        $this->template->categories = $this->context->jobscategory->findAll();
    }

    public function handleDeleteCategory($id)
    {
        $return = $this->context->jobscategory->delete($id);
		if ($return === FALSE)
		{
			$this->flashMessage('Category is used for one or more jobs and CANNOT be deleted!', 'warning');
		}
        $this->redirect('category');
    }
	
	public function handleDeleteLocation($id)
	{
		$children = $this->context->location->find('parent_id=%i', $id);
		if (count($children) >=1)
		{
			foreach ($children as $child)
			{
				$this->context->location->delete($child->id);
			}
		}
		$this->context->location->delete($id);
	}

    protected function createComponentJobsGrid()
    {
        $dataSource = $this->context->jobs->getDataGrid();
        $grid = new \JobsGrid($dataSource, $this, $this->translator, $this->context->jobs, $this->lang);
        $grid->setViewLinkFactory($this->jobViewLinkFactory);
        $grid->setEditLinkFactory($this->jobEditLinkFactory);
        $grid->setDeleteLinkFactory($this->jobDeleteLinkFactory);
        $grid->setDeleteCallback($this->deleteJob);
        return $grid;
    }
    
    public function handleDelete($id)
    {
        $this->deleteJob($id);
        $this->redirect('this');
    }
    
    public function deleteJob($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->deleteJob($item);
            }
        } else {
            $entity = $this->context->jobs->find($id);
            if ($this->context->jobs->delete($entity->id)) {
                $this->flashMessage($this->translator->translate("Job '%s' was succesfull deleted", $entity->name), "success");
            } else {
                $this->flashMessage($this->translator->translate("Job '%s' wasn't deleted", $entity->name), "danger");
            }
        }
    }
    
    public function jobViewLinkFactory($row)
    {
        return $this->link(":Front:Jobs:show", $row['code']);
    }
    
    public function jobEditLinkFactory($row)
    {
        return $this->link("edit", $row['id']);
    }
    
    public function jobDeleteLinkFactory($row)
    {
        return $this->link("delete!", $row['id']);
    }
    
    protected function createComponentJobCategoriesGrid()
    {
        $dataSource = $this->context->jobscategory->getDataGrid($this->lang);
        $grid = new \JobCategoriesGrid($dataSource, $this, $this->translator, $this->context->jobscategory, $this->lang);
        return $grid;
    }
	
	protected function createComponentLocationsGrid()
    {
        $dataSource = $this->context->location->getMainDataGrid();
        $grid = new \LocationsGrid($dataSource, $this, $this->translator, $this->context->location, $this->lang);
        return $grid;
    }

// <editor-fold defaultstate="collapsed" desc="form components">

    protected function createComponentEditJobForm()
    {
        $form = new \AppForms\EditJobForm($this, $this->context->jobs, $this->context->location, $this->context->jobscategory, $this->context->getByType('\Model\Service\CompanyService'), $this->context->getByType('Model\Service\AircraftService'));
        $form->setOnSaveCallback($this->onJobSave);
        $form->setOnSaveAndBackCallback($this->onJobSaveAndBack);
        return $form;
    }
    
    public function onJobSave(\Model\Entity\JobEntity $job)
    {
        $this->redirect('edit', $job->id);
    }
    
    public function onJobSaveAndBack(\Model\Entity\JobEntity $job)
    {
        $this->redirect('default');
    }

    protected function createComponentEditCategoryForm()
    {
        $form = new \AppForms\JobCategoryForm($this, $this->context->jobscategory);
        return $form;
    }
	
	protected function createComponentEditLocationForm()
	{
		$form = new \AppForms\EditLocationForm($this, $this->context->location);
		return $form;
	}

// </editor-fold>
    
    public function actionCandidates($id)
    {
        $job = $this->context->jobs->find($id);
        $this->template->job = $job;
    }
    
    public function createComponentJobCandidatesGrid()
    {
        $source = $this->context->jobs->getJobUserGridDataSource($this->presenter->getParameter('id'));
        $grid = new \JobCandidatesGrid($source, $this->translator);
        return $grid;
    }
    
    public function createComponentAddCandidateToJobForm()
    {
        $form = new \AppForms\AddCandidateToJobForm($this, $this->context->jobs, $this->context->cv, $this->context->mail);
        return $form;
    }
    
    public function actionJobUserStatus($id, $status)
    {
        $this->context->jobs->setJobUserStatus($id, $status);
        $this->terminate();
    }
    
    public function handleRemoveUserFromJob($job, $user)
    {
        $this->context->jobs->removeUserFromJob($job, $user);
        $this->redirect('this');
    }
    
    public function actionNotes($id)
    {
        $jobUser = $this->context->jobs->getJobUser($id);
        $job = $this->context->jobs->find($jobUser->job_id);
        $candidate = $this->context->users->find($jobUser->user_id);
        $this->template->job = $job;
        $this->template->candidate = $candidate;
    }
    
    public function createComponentNotes()
    {
        $control = $this->jobUserNotesFactory->create($this->presenter->getParameter('id'));
        $control->setAdminId($this->user->id);
        return $control;
    }
    
}
