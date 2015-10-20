<?php

namespace Model\Service;

use Model\Mapper\Dibi\ActionLogDibiMapper,
    Model\Entity\ActionLogEntity;

/**
 * Action Log Service
 *
 * @author Petr Poupě
 */
class ActionLogService
{

    /** @var ActionLogDibiMapper */
    private $mapper;

    public function __construct(ActionLogDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }
    
    public function logSerie($action, $userId, $attributes = array())
    {
        return $this->log($action, $userId, $attributes, TRUE);
    }

    public function log($action, $userId, $attributes = array(), $serie = FALSE)
    {
        if ($userId === NULL)
            return FALSE;
        $log = new ActionLogEntity;
        $log->action = $action;
        $log->userId = $userId;
        $log->datetime = time();
        $log->attrs = $attributes;
        $log->isSerie = $serie;
        if ($serie) {
            $originSeries = $this->mapper->findSeriesOrigin($log);
            $log->serieId = $originSeries->id;
        }
        return $this->mapper->save($log);
    }
    
    public function getLast($userId, $count = 10)
    {
        return $this->mapper->getLast($userId, $count);
    }

}

?>
