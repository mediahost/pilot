<?php

namespace Model\Entity;

/**
 * Candidate Entity
 *
 * @author Petr Poupě
 * @property UserDocEntity[] $userDocs
 */
class CandidateEntity extends Entity
{
    /** @var int */
    protected $id;
    /** @var int */
    protected $cvId;
    /** @var CvEntity */
    public $cv;
    /** @var string */
    protected $launchpadVideoUrl;
    /** @var UserDocEntity[] */
    protected $userDocs = array();
    
    /** @var string */
    protected $url_github;
    
    /** @var string */
    protected $url_stackoverflow;
    
    /** @var string */
    protected $url_linkedin;
    
    /** @var string */
    protected $url_facebook;
    
    /** @var string */
    protected $url_twitter;

}
