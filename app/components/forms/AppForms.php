<?php

namespace AppForms;

use Nette\Application\UI\Form,
    Nette\Application\UI\Presenter,
    Nette\Localization\ITranslator;

/**
 * AppForms - Abstract class for all forms
 *
 * @author Petr Poupě
 */
abstract class AppForms extends \Nette\Application\UI\Control
{

    const STYLE_METRONIC = "metronic";

    protected $defaults = array();
    protected $templatePath = 'templates';
    protected $templateName;

    /** @var string */
    protected $name = NULL;

    /** @var Form */
    protected $form;

    /** @var Presenter */
    protected $presenter;

    /** @var ITranslator */
    protected $translator;

    /** @var \Nette\Security\User */
    protected $user;

    /** @var lang */
    protected $lang;

    /** @var bool */
    protected $ownTemplate = FALSE;
    private $renderAdvanced = NULL;

    public function __construct($name, Presenter $presenter, $useDefaultTemplate = TRUE)
    {
        $this->name = preg_match("@\\\([^\\\]*)$@", $name, $matches) ? (lcfirst($matches[1])) : $name;
        $this->presenter = $presenter;
        $this->translator = $presenter->translator;
        $this->user = $presenter->user;
        $this->lang = $presenter->lang;
        $this->setOwnTemplate(!$useDefaultTemplate);

        $this->form = new Form;
        $this->form->setTranslator($this->translator);
        $this->form->addProtection("Timed out, submit the form again");
    }

    public function setStyle($style)
    {
        switch ($style) {
            case self::STYLE_METRONIC:
                $this->form->setRenderer(new Renderers\MetronicFormRenderer);

                $renderer = $this->form->getRenderer();
                $renderer->wrappers['form']['container'] = 'div class="form-body"';
                $renderer->wrappers['form']['actions'] = 'div class="form-actions fluid"';
                $renderer->wrappers['error']['container'] = 'div class="alert alert-danger"';
                $renderer->wrappers['error']['item'] = 'p';
                $renderer->wrappers['controls']['container'] = NULL;
                $renderer->wrappers['pair']['container'] = 'div class="form-group"';
                $renderer->wrappers['pair']['actions'] = NULL;
                $renderer->wrappers['pair']['.error'] = 'has-error';
                $renderer->wrappers['control']['container'] = 'div class="col-sm-10"';
                $renderer->wrappers['control']['actions'] = 'div class="col-sm-offset-2 col-sm-10"';
                $renderer->wrappers['label']['container'] = NULL;
                $renderer->wrappers['label']['requiredsuffix'] = \Nette\Utils\Html::el('span class=required')->setText('*');
                $renderer->wrappers['control']['description'] = 'span class="help-block"';
                $renderer->wrappers['control']['errorcontainer'] = 'span class="help-block"';
                $this->form->getElementPrototype()->class('form-horizontal');

                break;
        }
        $this->renderAdvanced = $style;
    }

    protected function getForm()
    {
        return $this->getComponent($this->name);
    }

    protected function setTemplatePath($path)
    {
        $this->templatePath = trim($path, "/");
    }

    protected function getTemplatesPath($file = NULL)
    {
        return __DIR__ . '/' . trim($this->templatePath, "/") . '/' . $file;
    }

    protected function setTemplateName($name)
    {
        $this->templateName = $name;
    }

    protected function setOwnTemplate($ownTemplate = TRUE)
    {
        $this->ownTemplate = (bool) $ownTemplate;
    }

    public function render()
    {
        $this->getForm();
        switch ($this->renderAdvanced) {
            case self::STYLE_METRONIC:
                $this->wrapperMetronic();
                break;
        }

        if ($this->templateName) {
            $this->template->setFile($this->getTemplatesPath($this->templateName . ".latte"));
        } else if ($this->ownTemplate) {
            $this->template->setFile($this->getTemplatesPath($this->name . ".latte"));
        } else {
            $this->template->setFile($this->getTemplatesPath("defaultForm.latte"));
        }
        $this->template->formName = $this->name;
        $this->template->setTranslator($this->translator);
        $this->template->render();
    }

    private function wrapperMetronic()
    {
        $usedPrimary = FALSE;
        foreach ($this->getForm()->getControls() as $control) {

            if ($control->getLabelPrototype() instanceof \Nette\Utils\Html) {
                $control->getLabelPrototype()->class('col-sm-2 control-label', TRUE);
            }

            if ($control instanceof \Nette\Forms\Controls\Button) {
                $control->getControlPrototype()->class(!$usedPrimary ? 'btn btn-primary' : 'btn btn-default', TRUE);
                $usedPrimary = TRUE;
            } else if ($control instanceof \Nette\Forms\Controls\TextBase ||
                    $control instanceof \Nette\Forms\Controls\SelectBox ||
                    $control instanceof \Nette\Forms\Controls\MultiSelectBox
            ) {
                $control->getControlPrototype()->class('form-control', TRUE);
            } else if ($control instanceof \Nette\Forms\Controls\RadioList) {
                $control->getSeparatorPrototype()
                        ->setName('div')
                        ->class($control->getControlPrototype()->type);
            }
        }
    }

    public function saveImage(\Nette\Http\FileUpload $file, $folder = NULL, $newName = NULL)
    {
        return self::saveImg($file, $folder, $newName);
    }
    
    public static function saveImg(\Nette\Http\FileUpload $file, $folder = NULL, $newName = NULL)
    {
        $root = "foto/original";

        $filename = FALSE;
        if ($file->isOk() && $file->isImage()) {
            $folderTrim = trim($folder, "/");
            $folder = !empty($folderTrim) ? "/{$folderTrim}/" : "/";
            $path = $root . $folder;
            if (\CommonHelpers::dir_exists($path)) {
                if ($newName === NULL) {
                    $newName = $file->getName();
                    if (preg_match("@^(.+)\.\w+$@", $newName, $matches)) {
                        $newName = $matches[1];
                    }
                }
				$ext = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
                $filename = $newName . '.' . $ext;
                $file->toImage()->save($path . $filename);
                self::removeThumbnails($folder . $filename);
            }
        }
        return $filename;
    }
    
    public static function copyImage($filePath, $folder = NULL, $newName = NULL)
    {
        $root = "foto/original";
        $ext = ".png";
        $type = \Nette\Image::PNG;

        $filename = FALSE;
        if (file_exists($filePath)) {
            $folderTrim = trim($folder, "/");
            $folder = !empty($folderTrim) ? "/{$folderTrim}/" : "/";
            $path = $root . $folder;
            if (\CommonHelpers::dir_exists($path)) {
                if ($newName === NULL) {
                    $newName = $filePath;
                    if (preg_match("@^(.+)\.\w+$@", $newName, $matches)) {
                        $newName = $matches[1];
                    }
                }
                $filename = $newName . $ext;
                copy($filePath, $path . $filename);
                self::removeThumbnails($folder . $filename);
            }
        }
        return $filename;
    }

    private static function removeThumbnails($filename)
    {
        $path = "foto";
        $folderPattern = "@\d+\-\d+@";
        $filename = trim($filename, "/");
        foreach (scandir($path) as $folder) {
            if (preg_match($folderPattern, $folder)) {
                $file = $path . "/" . $folder . "/" . $filename;
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    public static function removePhoto($folder, $name)
    {
        $path = "foto";
        $folderPattern = "@\d+\-\d+@";
        $filename = $name .'.png';
        foreach (scandir($path) as $fold) {
            if (in_array($fold, array('.', '..'))) {
                continue;
            }
            $file = $path . "/" . $fold . "/" . $folder . "/" . $filename;
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    protected function setDefaultValues($values, $erase = FALSE)
    {
        $form = $this->getForm();
        $form->setDefaults($values, $erase);
    }

}
