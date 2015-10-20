<?php

namespace Model\Service;

class UserDocService extends \Nette\Object
{
    
    /** @var \Model\Mapper\Dibi\UserDocDibiMapper */
    protected $userDocsMapper;
    
    /** @var string */
    protected $docFolderPath;
    
    function __construct(\Model\Mapper\Dibi\UserDocDibiMapper $userDocsMapper, $docFolderPath)
    {
        $this->userDocsMapper = $userDocsMapper;
        $this->docFolderPath = $docFolderPath;
    }
    
    public function create(\Nette\Http\FileUpload $file, $userId)
    {
        $userDoc = new \Model\Entity\UserDocEntity;
        $userDoc->userId = $userId;
        $userDoc->originalName = $file->name;
        $userDoc->name = $this->getUniqDocFileName($userDoc->created,$file);
        $this->userDocsMapper->save($userDoc);
        $file->move($this->docFolderPath . '/' . $userDoc->name);
    }
    
    public function save(\Model\Entity\UserDocEntity $userDoc)
    {
        $this->userDocsMapper->save($userDoc);
    }
    
    public function getUniqDocFileName(\Nette\DateTime $date, \Nette\Http\FileUpload $file)
    {
        $pathInfo = pathinfo($file->name);
        $extension = $pathInfo['extension'];
        $year = $date->format('Y');
        $month = $date->format('m');
        if (!file_exists($this->docFolderPath.'/'.$year)) {
            mkdir($this->docFolderPath.'/'.$year);
        }
        if (!file_exists($this->docFolderPath.'/'.$year.'/'.$month)) {
            mkdir($this->docFolderPath.'/'.$year.'/'.$month);
        }
        do {
            $filename = \Nette\Utils\Strings::random();
        } while (file_exists($this->docFolderPath.'/'.$year.'/'.$month.'/'.$filename.'.'.$extension));
        return $year.'/'.$month.'/'.$filename.'.'.$extension;
    }
    
    /**
     * @return \Model\Entity\UserDocEntity[]
     */
    public function findByUser($userId, $limit = NULL)
    {
        return $this->userDocsMapper->findByUser($userId, $limit);
    }
    
    /**
     * @return \Model\Entity\UserDocEntity
     */
    public function get($id)
    {
        return $this->userDocsMapper->get($id);
    }
    
    public function delete(\Model\Entity\UserDocEntity $userDoc)
    {
        $return = $this->userDocsMapper->delete($userDoc);
        unlink($this->docFolderPath . '/' . $userDoc->name);
        return $return;
    }
    
}
