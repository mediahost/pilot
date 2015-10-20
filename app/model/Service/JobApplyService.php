<?php

namespace Model\Service;

use Model\Mapper\Dibi\JobApplyDibiMapper,
    Model\Entity\JobApplyEntity;

/**
 * Job Apply Service
 *
 * @author Petr Poupě
 */
class JobApplyService
{

    /** @var ActionLogDibiMapper */
    private $mapper;

    public function __construct(JobApplyDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function apply($userId, $jobId, $position, $reciever, $sender, $subject, $text)
    {
        $apply = new JobApplyEntity;
        $apply->userId = $userId;
        $apply->jobId = $jobId;
        $apply->datetime = time();
        $apply->position = $position;
        $apply->reciever = $reciever;
        $apply->sender = $sender;
        $apply->subject = $subject;
        $apply->text = $text;
        return $this->mapper->save($apply);
    }
    
    public function getLast($userId, $count = 10)
    {
        return $this->mapper->getLast($userId, $count);
    }
    
    public function find($id)
    {
        return $this->mapper->find($id);
    }

}

?>
