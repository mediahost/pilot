<?php

namespace FrontModule;

/**
 * Forum Presenter
 *
 * @author Petr Poupě
 */
class ForumPresenter extends BasePresenter
{

    public function startup()
    {
        parent::startup();
    }

    public function actionDefault()
    {
        $canForumEdit = $this->user->isAllowed("forum", "edit");
        $canCategoryEdit = $this->user->isAllowed("category", "edit");
        $canCategoryShow = $this->user->isAllowed("category", "show");
        $this->template->categories = $this->context->forum->getCategories($this->lang, NULL, !$canForumEdit);
        $this->template->canForumEdit = $canForumEdit;
        $this->template->canCategoryShow = $canCategoryShow;
        $this->template->canCategoryEdit = $canCategoryEdit;
    }

    public function actionTopics($fid)
    {
        $forum = $this->context->forum->getForum($fid);
        if ($forum->id === NULL) {
            $this->flashMessage("This forum wasn't find.", "warning");
            $this->redirect("Forum:");
        }

        $canAdd = $this->user->isAllowed("topic", "add");
        $canDelete = $this->user->isAllowed("topic", "delete");

        $topicsCount = $this->context->forum->getTopicsCount($fid);

        $vp = new \VisualPaginator($this, 'topic');
        $paginator = $vp->getPaginator();
        $paginator->itemsPerPage = 20;
        $paginator->itemCount = $topicsCount;

        $this->template->forum = $forum;
        $this->template->topics = $this->context->forum->getTopics($fid, $paginator->offset, $paginator->itemsPerPage);

        $this->template->userId = $this->user->id;
        $this->template->canAdd = $canAdd;
        $this->template->canDelete = $canDelete;
    }

    public function actionTopic($tid, $editPost = NULL)
    {
        $topic = $this->context->forum->getTopic($tid);
        if ($topic->id === NULL) {
            $this->flashMessage("This topic wasn't find.", "warning");
            $this->redirect("Forum:");
        }

        $this->template->topic = $topic;
        $this->template->forum = $this->context->forum->getForum($this->template->topic->forumId);
        $this->template->posts = $this->context->forum->getPosts($tid);
        if ($this->user->id !== NULL) {
            $this->context->forum->addTopicView($tid);
        }

        $newPost = new \Model\Entity\ForumPostEntity;
        $newPost->topicId = $tid;
        if ($editPost !== NULL) {
            $findedPost = $this->context->forum->getPost($editPost);
            if ($findedPost->userId == $this->user->id && $findedPost->topicId == $tid) {
                $post = $findedPost;
            } else {
                $post = $newPost;
            }
        } else {
            $post = $newPost;
        }
        $this["postForm"]->setDefaults($post);

        $canAdd = $this->user->isAllowed("post", "add");
        $canDelete = $this->user->isAllowed("post", "delete");
        $canAllow = $this->user->isAllowed("post", "allow");

        $this->template->userId = $this->user->id;
        $this->template->canAdd = $canAdd;
        $this->template->canAllow = $canAllow;
        $this->template->canDelete = $canDelete;
    }

    public function actionPost($pid)
    {
        $post = $this->context->forum->getPost($pid);
        $this->redirect("topic#post-{$pid}", $post->topicId);
    }

    public function actionEditTopic($fid, $tid = NULL)
    {
        $forum = $this->context->forum->getForum($fid);
        if ($forum->id === NULL) {
            $this->flashMessage("This forum wasn't find.", "warning");
            $this->redirect("Forum:");
        } else {
            $topic = $this->context->forum->getTopic($tid);
            if ($topic->id !== NULL) {
                if ($topic->forumId !== $forum->id) {
                    $this->flashMessage("This topic wasn't find for this forum.", "warning");
                    $this->redirect("Forum:topics", $fid);
                }
                $this["topicForm"]->setDefaults($topic);
                $this["topicForm"]->setDisabledBody(!($topic->firstPost->userId == $this->user->id));
            } else {
                $topic->forumId = $fid;
                $this["topicForm"]->setDefaults($topic);
            }
        }
        $this->template->forum = $forum;
        $this->template->topic = $topic;
    }

    public function handleDeletePost($pid)
    {
        $post = $this->context->forum->getPost($pid);
        $canDelete = $this->user->isAllowed("post", "delete");
        $isOwner = ($this->user->id == $post->userId);
        if ($post->id !== NULL && ($isOwner || $canDelete) && $this->context->forum->deletePost($post)) {
            $this->flashMessage("Your post was succesful deleted.", "success");
        } else {
            $this->flashMessage("This post cannot be deleted.", "warning");
        }
        $this->redirect("this");
    }

    public function handleDeleteTopic($tid)
    {
        $topic = $this->context->forum->getTopic($tid);
        if ($topic->id === NULL) {
            $this->flashMessage("This topic wasn't find.", "success");
            $this->redirect("this");
        }
        $canDelete = $this->user->isAllowed("topic", "delete");
        $isOwner = ($this->user->id == $topic->firstPost->userId);
        if (($isOwner || $canDelete) && $this->context->forum->deleteTopic($topic)) {
            $this->flashMessage("Your topic was succesful deleted.", "success");
        } else {
            $this->flashMessage("This topic cannot be deleted.", "warning");
        }
        $this->redirect("this");
    }

    public function handleDeleteForum($fid)
    {
        $forum = $this->context->forum->getForum($fid);
        if ($forum->id === NULL) {
            $this->flashMessage("This forum wasn't find.", "success");
            $this->redirect("this");
        }
        $canDelete = $this->user->isAllowed("forum", "delete");
        if ($canDelete && $this->context->forum->deleteForum($forum)) {
            $this->flashMessage("Your forum was succesful deleted.", "success");
        } else {
            $this->flashMessage("This forum cannot be deleted.", "warning");
        }
        $this->redirect("this");
    }

    public function handleDeleteCategory($cid)
    {
        $category = $this->context->forum->getCategory($cid);
        if ($category->id === NULL) {
            $this->flashMessage("This category wasn't find.", "success");
            $this->redirect("this");
        }
        $canDelete = $this->user->isAllowed("category", "delete");
        if ($canDelete && $this->context->forum->deleteCategory($category)) {
            $this->flashMessage("Your category was succesful deleted.", "success");
        } else {
            $this->flashMessage("This category cannot be deleted.", "warning");
        }
        $this->redirect("this");
    }

    /**
     * Topic form factory.
     * @return Form
     */
    protected function createComponentCategoryForm()
    {
        return new \AppForms\ForumCategoryForm($this, $this->context->forum);
    }

    /**
     * Topic form factory.
     * @return Form
     */
    protected function createComponentTopicForm()
    {
        return new \AppForms\ForumTopicForm($this, $this->context->forum);
    }

    /**
     * Post form factory.
     * @return Form
     */
    protected function createComponentPostForm()
    {
        return new \AppForms\ForumPostForm($this, $this->context->forum);
    }

}
