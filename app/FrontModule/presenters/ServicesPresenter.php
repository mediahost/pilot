<?php

namespace FrontModule;

/**
 * Services Presenter - For services call
 *
 * @author Petr Poupě
 */
class ServicesPresenter extends BasePresenter
{

    public function actionTexy($text)
    {
        $texy = new \MyTexy;
        $this->template->html = $texy->process($text);
    }

}