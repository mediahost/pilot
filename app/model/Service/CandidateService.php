<?php

namespace Model\Service;

use Model\Mapper\Dibi\CandidateDibiMapper,
    Model\Entity\CandidateEntity;

/**
 * Candidate Service
 *
 * @author Petr Poupě
 */
class CandidateService
{

    /** @var \Model\Mapper\Dibi\CandidateDibiMapper */
    private $mapper;

    public function __construct(CandidateDibiMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function setFavorite($userId, $candidateId)
    {
        return $this->mapper->setFavorite($userId, $candidateId);
    }

    public function unsetFavorite($userId, $candidateId)
    {
        return $this->mapper->unsetFavorite($userId, $candidateId);
    }

    public function getCandidates(array $where = array(), $offset = NULL, $limit = NULL)
    {
        return $this->mapper->getCandidates($where, $offset, $limit);
    }

    public function getCandidatesCount(array $where = array())
    {
        return $this->mapper->getCandidatesCount($where);
    }

    public function getFavorites($userId, $offset = NULL, $limit = NULL)
    {
        return $this->mapper->getFavorites($userId, $offset, $limit);
    }

    public function getFavoritesCount($userId)
    {
        return $this->mapper->getFavoritesCount($userId);
    }

    public function getMatched($jobId, $onlyApplyed = FALSE, $category = NULL, $order = NULL)
    {
        return $this->mapper->getMatched($jobId, $onlyApplyed, $category, $order);
    }

}
