<?php

use NiftyGrid\Grid,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator,
    Model\Service\JobService;

class JobCandidatesGrid extends Grid
{
    
    /** @var DibiFluent */
    protected $source;
    
    /** @var ITranslator */
    protected $translator;
    
    public function __construct(DibiFluent $source, \Nette\Localization\ITranslator $translator)
    {
        $this->source = $source;
        $this->translator = $translator;
        parent::__construct();
    }
    
    protected function configure($presenter)
    {
        $source = new NiftyGrid\DibiFluentDataSource($this->source, "id");
        $this->setDataSource($source);
        
        $this->setDefaultOrder('id DESC');
        $this->setTranslator($this->translator);
        
        $this->addColumn('firstname', 'Name')
            ->setRenderer(function($row) use ($presenter) {
                return \Nette\Utils\Html::el('a')
                    ->setText($row->firstname.' '.$row->middlename.' '.$row->surname)
                    ->href($presenter->link(':Company:Profile:show', $row->user_id));
            });
            
        $this->addColumn('status', 'Status')
            ->setRenderer(function($row) use ($presenter) {
                $select = \Nette\Utils\Html::el('select');
                $select->name = 'jobuser-'.$row->id;
                $url = $presenter->link(':Admin:Jobs:jobUserStatus', $row->id);
                $select->addAttributes(['data-url' => $url]);
                $select->class = 'jobuser-status-selector';
                foreach (JobService::getCandidateJobStatuses() as $key => $name) {
                    $option = \Nette\Utils\Html::el('option')
                        ->addAttributes(['value' => $key])
                        ->setText($name);
                    if ($row->status == $key) {
                        $option->addAttributes(['selected' => 'selected']);
                    }
                    $select->add($option);
                }
                $span = \Nette\Utils\Html::el('span');
                $span->setText($row->applyed ? 'Applied' : 'Not applied');
                $span->addClass($row->applyed ? 'text-success' : 'text-danger');
                
                $div = \Nette\Utils\Html::el('div');
                $div->add($select);
                $div->add('<br>');
                $div->add($span);
                return $div;
            });
        $this->addColumn('notes', 'Notes')
            ->setRenderer(function ($row) use ($presenter) {
                $a = Nette\Utils\Html::el('a');
                $a->addClass('notes');
                $a->href($presenter->link(':Admin:Jobs:notes', $row->id));
                $count = Nette\Utils\Html::el('div');
                $count->addClass('count');
                $count->setText($row->notes_count);
                $img = Nette\Utils\Html::el('img')
                    ->src($presenter->template->basePath . '/images/notes.png');
                $a->add($img);
                $a->add($count);
                return $a;
            });
            
        $this->addButton('delete', 'Remove')
            ->setAjax(FALSE)
            ->setClass('delete')
            ->setLink(function($row) use ($presenter) {
                return $presenter->link('removeUserFromJob!', $presenter->getParameter('id'), $row->user_id);
            });
        
    }

    
}
