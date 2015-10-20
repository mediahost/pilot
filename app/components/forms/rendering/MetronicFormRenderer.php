<?php

namespace AppForms\Renderers;

use Nette,
	Nette\Utils\Html,
        Nette\Forms\Rendering\DefaultFormRenderer;


/**
 * Converts a Form into the HTML output.
 * Changes:
 * - Move errors into body
 * - Move all buttons out of body
 *
 * @author     Petr Poupě
 */
class MetronicFormRenderer extends DefaultFormRenderer
{

    
        public function __construct()
        {
                $this->wrappers['form']['actions'] = NULL;
                $this->wrappers['pair']['actions'] = NULL;
                $this->wrappers['control']['actions'] = NULL;
        }


	/**
	 * Check if set section for buttons
	 * @return bool
	 */
        protected function isButtonSection()
        {
                return $this->wrappers['form']['actions'] !== NULL;
        }


        /**
	 * Provides complete form rendering.
	 * @param  Nette\Forms\Form
	 * @param  string 'begin', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;
			$this->init();
		}

		$s = '';
		if (!$mode || $mode === 'begin') {
			$s .= $this->renderBegin();
		}
		if (!$mode || $mode === 'body') {
			$s .= $this->renderBody($mode);
			$s .= $this->renderButtons();
		}
		if (!$mode || $mode === 'end') {
			$s .= $this->renderEnd();
		}
		return $s;
	}


	/**
	 * Renders validation errors (per form or per control).
	 * @return string
	 */
	public function renderErrors(Nette\Forms\IControl $control = NULL, $own = TRUE)
	{
		$errors = $control
			? $control->getErrors()
			: ($own ? $this->form->getErrors() : $this->form->getErrors());
		if (!$errors) {
			return;
		}
		$container = $this->getWrapper($control ? 'control errorcontainer' : 'error container');
                if (!$control) {
                    $closeButton = Html::el('button class=close data-close=alert');
                    $container->add($closeButton);
                }
		$item = $this->getWrapper($control ? 'control erroritem' : 'error item');

		foreach ($errors as $error) {
			$item = clone $item;
			if ($error instanceof Html) {
				$item->add($error);
			} else {
				$item->setText($error);
			}
			$container->add($item);
		}
		return "\n" . $container->render($control ? 1 : 0);
	}


	/**
	 * Renders form body.
	 * @return string
	 */
	public function renderBody($mode = NULL)
	{
		$s = $remains = '';
                
		if (!$mode || strtolower($mode) === 'ownerrors') {
			$s .= $this->renderErrors();
		} elseif ($mode === 'errors') {
			$s .= $this->renderErrors(NULL, FALSE);
		}

		$defaultContainer = $this->getWrapper('group container');
		$translator = $this->form->getTranslator();

		foreach ($this->form->getGroups() as $group) {
			if (!$group->getControls() || !$group->getOption('visual')) {
				continue;
			}

			$container = $group->getOption('container', $defaultContainer);
			$container = $container instanceof Html ? clone $container : Html::el($container);

			$s .= "\n" . $container->startTag();

			$text = $group->getOption('label');
			if ($text instanceof Html) {
				$s .= $this->getWrapper('group label')->add($text);

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= "\n" . $this->getWrapper('group label')->setText($text) . "\n";
			}

			$text = $group->getOption('description');
			if ($text instanceof Html) {
				$s .= $text;

			} elseif (is_string($text)) {
				if ($translator !== NULL) {
					$text = $translator->translate($text);
				}
				$s .= $this->getWrapper('group description')->setText($text) . "\n";
			}

			$s .= $this->renderControls($group);

			$remains = $container->endTag() . "\n" . $remains;
			if (!$group->getOption('embedNext')) {
				$s .= $remains;
				$remains = '';
			}
		}

		$s .= $remains . $this->renderControls($this->form);

		$container = $this->getWrapper('form container');
		$container->setHtml($s);
		return $container->render(0);
	}


	/**
	 * Renders form buttons.
	 * @return string
	 */
	public function renderButtons()
	{
		$defaultContainer = $this->getWrapper('group container');
                
                $buttons = NULL;
                foreach ($this->form->getControls() as $control) {
                        if ($control instanceof Nette\Forms\Controls\Button) {
                                $buttons[] = $control;
                        }
                }
                
                if ($buttons) {
                        $container = $this->getWrapper('form actions');
//                        foreach ($buttons as $button) {
                                $container->add($this->renderPairMulti($buttons));
//                        }
                        return $container->render(0);
                }
                return NULL;
                
//		if ($buttons) {
//			$container->add($this->renderPairMulti($buttons));
//		}
//                
//		$s = '';
//                
//		if (count($container)) {
//			$s .= "\n" . $container . "\n";
//		}
//                
//		return $s;
//                    \Nette\Diagnostics\Debugger::barDump($buttons);
	}


	/**
	 * Renders group of controls.
	 * @param  Nette\Forms\Container|FormGroup
	 * @return string
	 */
	public function renderControls($parent)
	{
		if (!($parent instanceof Nette\Forms\Container || $parent instanceof Nette\Forms\ControlGroup)) {
			throw new Nette\InvalidArgumentException("Argument must be FormContainer or FormGroup instance.");
		}

		$container = $this->getWrapper('controls container');

		foreach ($parent->getControls() as $control) {
			if ($control->getOption('rendered') || $control instanceof Nette\Forms\Controls\HiddenField || $control->getForm(FALSE) !== $this->form) {
				// skip

			} elseif ($control instanceof Nette\Forms\Controls\Button) {
				// skip

			} else {
				$container->add($this->renderPair($control));
			}
		}

		$s = '';
		if (count($container)) {
			$s .= "\n" . $container . "\n";
		}

		return $s;
	}


	/**
	 * Renders single visual row of multiple controls.
	 * @param  IFormControl[]
	 * @return string
	 */
	public function renderPairMulti(array $controls)
	{
		$s = array();
		foreach ($controls as $control) {
			if (!$control instanceof Nette\Forms\IControl) {
				throw new Nette\InvalidArgumentException("Argument must be array of IFormControl instances.");
			}
			$description = $control->getOption('description');
			if ($description instanceof Html) {
				$description = ' ' . $control->getOption('description');

			} elseif (is_string($description)) {
				$description = ' ' . $this->getWrapper('control description')->setText($control->translate($description));

			} else {
				$description = '';
			}

			$s[] = $control->getControl() . $description;
		}
		$pair = $this->getWrapper('pair actions');
		$pair->add($this->renderLabel($control));
		$pair->add($this->getWrapper('control actions')->setHtml(implode(" ", $s)));
		return $pair->render(0);
	}

}
