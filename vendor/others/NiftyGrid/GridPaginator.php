<?php
/**
 * NiftyGrid - DataGrid for Nette
 *
 * @author	Jakub Holub
 * @copyright	Copyright (c) 2012 Jakub Holub
 * @license	New BSD Licence
 * @link	http://addons.nette.org/cs/niftygrid
 */
namespace NiftyGrid;

use Nette\Utils\Paginator;

class GridPaginator extends \Nette\Application\UI\Control
{
	/** @persistent int */
	public $page = 1;

	/**
	 * @var Paginator
	*/
	public $paginator;

        /** @var Nette\Localization\ITranslator */
        private $translator;

	public function __construct(\Nette\Localization\ITranslator $translator = NULL)
	{
		parent::__construct();
		$this->paginator = new Paginator;
                if ($translator !== NULL) {
                    $this->setTranslator($translator);
	}
	}

        public function setTranslator(\Nette\Localization\ITranslator $translator)
        {
            $this->translator = $translator;
        }

	/**
	 * @return Paginator
	 */
	public function getPaginator()
	{
		if (!$this->paginator) {
			$this->paginator = new Paginator();
		}
		return $this->paginator;
	}
        
	/**
	 * @return array
	 */
        public function getSteps(Paginator $paginator)
        {
                $page = $paginator->page;
		if ($paginator->pageCount < 2) {
			$steps = array($page);

		} else {
			$arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
			$count = 4;
			$quotient = ($paginator->pageCount - 1) / $count;
			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}
			sort($arr);
			$steps = array_values(array_unique($arr));
		}
                return $steps;
        }

	public function render()
	{
                $paginator = $this->getPaginator();
		$this->template->steps = $this->getSteps($paginator);
		$this->template->paginator = $paginator;
		$this->template->setFile(__DIR__ . '/templates/paginator.latte');
                if (isset($this->translator)) {
                    $this->template->setTranslator($this->translator);
                    $this->template->translate = TRUE;
                }
		$this->template->render();
	}

	/**
	 * @param array $params
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->paginator->page = $this->page;
	}
}