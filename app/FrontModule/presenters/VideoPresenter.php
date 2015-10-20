<?php

namespace FrontModule;

/**
 * Video presenter.
 */
class VideoPresenter extends BasePresenter
{

    public function actionDefault($video, $image = NULL, $title = NULL, $width = 640, $height = 360, $autostart = TRUE)
    {
        $this->template->index = rand(100, 999);
        $this->template->file = $video;
        $this->template->image = $image;
        $this->template->title = $title;
        $this->template->width = $width;
        $this->template->height = $height;
        $this->template->autostart = $autostart;
    }

}
