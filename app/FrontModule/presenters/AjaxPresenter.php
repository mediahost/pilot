<?php

namespace FrontModule;

/**
 * Ajax Presenter - For ajax call
 *
 * @author Petr Poupě
 */
class AjaxPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
        $this->setView("default");
    }

    private function setData($data = NULL)
    {
        if (isset($_GET['callback']) and !empty($_GET['callback']))
            $this->template->data = $_GET['callback'] . '(' . json_encode($data) . ')';
        else
            $this->template->data = json_encode($data);
    }

    public function actionDefault()
    {
        $this->setData();
    }

    public function actionTimelineData($type, $attrId = NULL)
    {
        $events = array();
        switch ($type) {
            case "works":
                $cv = $this->context->cv->getCv($attrId);
                /* @var $cv \Model\Entity\CvEntity */
                $user = $this->userService->find($cv->userId);
                if (!$user->profile_token) {
                    if ($cv->userId != $this->user->id
                        && !($this->user->isCompany()
                        && $this->user->companyAllowedToCv($cv))
                        && !($this->user->isInRole('admin') || $this->user->isInRole('superadmin'))
                    ) {
                        $this->error();
                    }
                }
                if ($cv) {
                    foreach ($cv->getWorks() as $work) {
                        if ($work->from === NULL && $work->to === NULL) {
                            continue;
                        } else {
                            if ($work->from !== NULL) {
                                $from = $work->from;
                            }
                            $class = NULL;
                            $subscribe = NULL;
                            if ($work->to === NULL) {
                                $to = new \Nette\DateTime;
                                $class = "tillnow";
                                $subscribe = $this->translator->translate("till now");
                            } else {
                                $to = $work->to;
                            }

                            $position = $work->company . " - " . $work->position . ($subscribe === NULL ? "" : " (" . $subscribe . ")");
                            $events[] = new \Model\Entity\TimelineEventEntity($from, $to, $position, $class, $work->id);
                        }
                    }
                }

                break;

            case "recentActions":
                $list = $this->context->actionlogs->getLast($this->user->getId());
                foreach ($list as &$item) {
                    $events[] = new \Model\Entity\TimelineEventEntity($item->datetime, NULL, $this->translator->translate($item->action));
                }
                break;

            case "jobApplies":
                $list = $this->context->jobapplys->getLast($this->user->getId(), 50);
                foreach ($list as &$item) {
                    $events[] = new \Model\Entity\TimelineEventEntity($item->datetime, NULL, $item->position);
                }
                break;
        }

        $data = array();
        foreach ($events as $event) {
            $data[] = array(
                $event->start->format("U") * 1000,
                $event->end === NULL ? NULL : $event->end->format("U") * 1000,
                "{$event->name}",
                "{$event->class}",
                $event->id,
            );
        }
        $this->setData($data);
    }

    public function actionOrderCategories(array $listItem)
    {
        if ($this->user->isAllowed("category", "edit")) {
            $this->context->forum->sortCategories($listItem);
            $this->setData("OK");
        } else {
            $this->setData("ERR:NOT SORTED");
        }
    }

    public function actionEditContentPage($pageid, $contentid, $data)
    {
        if ($this->user->isAllowed("content", "edit")) {
            $page = $this->context->pages->getPage($pageid, $this->lang);
            if ($page->id !== NULL) {
                $what = NULL;
                switch ($contentid) {
                    case "ckeditor-title":
                        $page->name = $data;
                        $what = "name";
                        break;
                    case "ckeditor-perex":
                        $page->perex = $data;
                        $what = "perex";
                        break;
                    case "ckeditor-content":
                        $page->text = $data;
                        $what = "text";
                        break;
                    default:
                        $this->setData("ERR:UNKNOWN TYPE");
                        return;
                }
                $this->context->pages->save($page, $what);
                $this->setData("OK");
                return;
            }
        }
        $this->setData("ERR:NOT EDITED");
    }

    public function actionEditContentBlog($blogid, $contentid, $data)
    {
        if ($this->user->isAllowed("content", "edit")) {
            $blog = $this->context->blogs->getBlog($blogid, $this->lang);
            if ($blog->id !== NULL) {
                $what = NULL;
                switch ($contentid) {
                    case "ckeditor-title":
                        $blog->name = $data;
                        $what = "name";
                        break;
                    case "ckeditor-perex":
                        $blog->perex = $data;
                        $what = "perex";
                        break;
                    case "ckeditor-content":
                        $blog->text = $data;
                        $what = "text";
                        break;
                    default:
                        $this->setData("ERR:UNKNOWN TYPE");
                        return;
                }
                $this->context->blogs->save($blog, $what);
                $this->setData("OK");
                return;
            }
        }
        $this->setData("ERR:NOT EDITED");
    }

    public function actionValidateMail($mail)
    {
        $this->getHttpResponse()->setHeader('Access-Control-Allow-Origin', '*');
		if ($this->context->users->findByAuthMail(rawurldecode($mail))->id === NULL) {
			$this->setData(['valid' => TRUE]);
		} else {
			$this->setData(['valid' => FALSE, 'msg' => 'This mail is already registered.']);
		}
    }

}