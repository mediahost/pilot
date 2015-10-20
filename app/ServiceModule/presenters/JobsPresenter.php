<?php

namespace ServiceModule;

class JobsPresenter extends BasePresenter
{
    
    public function actionFillCodes()
    {
        $this->context->jobs->fillEmptyCodes();
        $this->terminate();
    }
    
}
