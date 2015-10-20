<?php

namespace FrontModule;

/**
 * Description of JobsPresenter
 *
 * @author Radim Křek
 */
class JobsPresenter extends BasePresenter
{

	private $jobs;
	private $where = array();

	public function actiondefault()
	{
        $this->flashMessage("This section has been removed!", "warning");
        $this->redirect("Dashboard:");
        
		$this->where['jobs.lang'] = $this->lang;
		$this->jobs = $this->context->jobs->getAll(NULL, NULL, $this->where);
	}

	public function renderDefault()
	{
		$this->where['jobs.lang'] = $this->lang;
		$paginator = new \VisualPaginator($this, 'jobs');
		$paginator->setTranslator($this->translator);
		$vp = $paginator->getPaginator();
		$vp->itemsPerPage = 4;
		$vp->itemCount = $test = count($this->jobs);
		$this->template->jobs = $this->jobs = $this->context->jobs->getAll($vp->offset, $vp->itemsPerPage, $this->where);
	}

	public function actionShow($id)
	{
        if (
            !$this->user->isLoggedIn() &&
            !$this->user->isCompany()
        ) {
            $bl = $this->storeRequest();
            $this->redirect('Sign:in', array(
                'backlink' => $bl
            ));
            
        }
        if ($this->user->isCompany() || $this->user->isInRole('admin') || $this->user->isInRole('superadmin')) {
            $job = $this->context->jobs->findByCode($id);
        } else {
            $job = $this->context->jobs->findByCode($id, $this->user->id);
        }
		$offers = explode(',', $job->offers);
		$requirments = explode(',', $job->requirments);
		$this->template->job = $job;
		$this->template->offers = $offers;
		$this->template->requirments = $requirments;
		$this->template->skills = $this->context->jobs->loadCategorizedSkills($id);
        $this->template->cv = $this->context->cv->getDefaultCv($this->user->getId());
	}

	public function createComponentFilter()
	{
		$form = new \Nette\Application\UI\Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Name');
		$form->addText('company', 'Company');
		$form->addText('location', 'Location');
		$form->addText('salary_from', 'Min. Salary');
		$form->addSelect('type', 'Type', array(0 => 'Select..', 1 => 'Full-Time', 2 => 'Part-Time', 3 => 'Contract'));
		$category[0] = 'Select...';
		foreach ($this->context->jobscategory->findAll($this->lang) as $value)
		{
			$category[$value->id] = $value->name;
		}
		$form->addSelect('category', 'Category', $category);
		$form->addSubmit('search', 'Search');
		$form->addSubmit('reset', 'Reset Filter');

		$form->onSuccess[] = callback($this, 'search');
		return $form;
	}

	public function search(\Nette\Application\UI\Form $form)
	{
		if ($form['reset']->submittedBy)
		{
			$this->redirect('default');
		}
		$values = $form->getValues();

		$where = array();
		$where['jobs.lang'] = $this->lang;
		foreach ($values as $key => $value)
		{
			if ($key === 'name' && $value != NULL)
			{
				$where['jobs.name%s'] = $value;
			}
			if ($key === 'company' && $value != NULL)
			{
				$where['jobs.company%s'] = $value;
			}
			if ($key === 'type' && $value != 0)
			{
				$where['jobs.type%i'] = $value;
			}
			if ($key === 'salary_from' && $value != 0)
			{
				$where['jobs.salary_from'] = $value;
			}
			if ($key === 'location' && $value != NULL) {
				$where['jobs.locations'] = array();
				$locations = $this->context->location->find('name=%s', $value);
				foreach ($locations as $location)
				{
					$locConn = $this->context->location->findConn(array('location_id'=>$location->id));
					foreach ($locConn as $loc)
					{
						$where['jobs.locations'][] = $loc->jobs_id;
					}
				}
			}
			if ($key === 'category' && $value != 0)
			{
				$where['jobs.category%i'] = $value;
			}
		}

		$this->where = $where;
		$this->jobs = Null;
		$this->jobs = $this->jobs = $this->context->jobs->getAll(NULL, NULL, $this->where);
		$this->invalidateControl('jobs');
	}

}
