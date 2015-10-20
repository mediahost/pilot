<?php

namespace Model\DataSource;

/**
 * LazyCollection for Job DataSource
 *
 * @author Petr Poupě
 */
class JobLazyCollection extends LazyCollection
{

    private $job = "profesia_job";
    private $categories = "profesia_job_offercategories";
    private $categorypositions = "profesia_job_offercategorypositions";
    private $educations = "profesia_job_educationlevel";
    private $jobtype = "profesia_job_jobtype";
    private $locations = "profesia_job_offerlocation";
    private $job_tag = "job_tag";
    private $joinedPosition = FALSE;

    public function filterLang($lang)
    {
        $this->query->where("{$this->job}.offerlanguage = %s", $lang);
    }

    public function filterText($text)
    {
        $words = preg_split("~\s~", $text, -1, PREG_SPLIT_NO_EMPTY);
        $unWords = array_unique($words);
        $i = 0;
        $wordsCnt = count($unWords);
        foreach ($unWords as $word) {
            $isFirst = !$i;
            $isLast = $wordsCnt - 1 === $i;
            $op = $isFirst ? "and" : "or";
            $this->query->$op(($isFirst ? "(" : "") . "({$this->job}.position LIKE %~like~ OR 
                                {$this->job}.jobtasks LIKE %~like~ OR 
                                {$this->job}.jobtasks LIKE %~like~)" . ($isLast ? ")" : ""), $word, $word, $word);
            $i++;
        }
    }

    public function filterPhrase($phrase)
    {
        if (!empty($phrase)) {
            $this->query->where("({$this->job}.position LIKE %~like~ OR 
                                {$this->job}.jobtasks LIKE %~like~ OR 
                                {$this->job}.jobtasks LIKE %~like~)", $phrase, $phrase, $phrase);
        }
    }

    public function filterSalary($min = NULL, $max = NULL)
    {
        if ((int) $min > 1) {
            $this->query->where("{$this->job}.minsalary >= %i", $min);
            $this->query->where("({$this->job}.maxsalary >= %i OR {$this->job}.maxsalary = %i OR {$this->job}.maxsalary IS NULL)", $min, 0);
        }
        if ((int) $max > 1) {
            $this->query->where("{$this->job}.maxsalary <= %i", $max);
            $this->query->where("({$this->job}.minsalary <= %i OR {$this->job}.minsalary = %i OR {$this->job}.minsalary IS NULL)", $max, 0);
        }
    }

    public function filterInterval($value)
    {
        switch ($value) {
            case 1:
                $interval = "1 day";
                break;
            case 2:
                $interval = "2 day";
                break;
            case 3:
                $interval = "3 day";
                break;
            case 4:
                $interval = "1 week";
                break;
            case 5:
                $interval = "2 week";
                break;
            case 6:
                $interval = "1 month";
                break;
            case 7:
                $interval = "2 month";
                break;
            default:
                $interval = NULL;
                break;
        }
        if (!empty($interval)) {
            $last = new \Nette\DateTime("-" . $interval);
            $this->query->where("{$this->job}.datecreated >= %t", $last);
        }
    }

    public function filterLocations($values)
    {
        if (is_array($values) && !empty($values)) {
            $this->query->leftJoin($this->locations)
                    ->on("{$this->locations}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->locations}.location_id IN %l", $values);
        }
    }

    public function filterCategories($values)
    {
        if (is_array($values) && !empty($values)) {
            $this->query->leftJoin($this->categories)
                    ->on("{$this->categories}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->categories}.category_id IN %l", $values);
        }
    }

    private function joinPositions()
    {
        if (!$this->joinedPosition) {
            $this->query->leftJoin($this->categorypositions)
                    ->on("{$this->categorypositions}.profesia_job_id = {$this->job}.id");
            $this->joinedPosition = TRUE;
        }
    }

    public function filterPositions($values)
    {
        if (is_array($values) && !empty($values)) {
            $this->joinPositions();
            $this->query->where("{$this->categorypositions}.position IN %l", $values);
        }
    }

    public function filterEducation($value)
    {
        if (!empty($value)) {
            $this->query->leftJoin($this->educations)
                    ->on("{$this->educations}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->educations}.education_id = %i", $value);
        }
    }

    public function filterEducations($values)
    {
        if (is_array($values) && !empty($values)) {
            $this->query->leftJoin($this->educations)
                    ->on("{$this->educations}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->educations}.education_id IN %l", $values);
        }
    }

    public function filterJobtype($value)
    {
        if (!empty($value)) {
            $this->query->leftJoin($this->jobtype)
                    ->on("{$this->jobtype}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->jobtype}.type_id = %i", $value);
        }
    }

    public function filterJobtypes($values)
    {
        if (is_array($values) && !empty($values)) {
            $this->query->leftJoin($this->jobtype)
                    ->on("{$this->jobtype}.profesia_job_id = {$this->job}.id");
            $this->query->where("{$this->jobtype}.type_id IN %l", $values);
        }
    }

    public function filterTags($tags)
    {
        if (!empty($tags) && is_array($tags))
            $this->query->where("{$this->job_tag}.tag IN %in", $tags);
    }

    public function filterTagIds($tags)
    {
        if (!empty($tags) && is_array($tags))
            $this->query->where("{$this->job_tag}.id IN %in", $tags);
    }

    public function limit($limit = NULL)
    {
        if ($limit > 0) {
            $this->query->limit($limit);
        }
        return $this;
    }

    public function sort($sort = NULL)
    {
        switch ($sort) {
            case "categ":
                $this->query->orderBy("customcategory");
                break;

            case "position":
                $this->joinPositions();
                $this->query->orderBy("{$this->categorypositions}.position ASC");
                break;

            case "salary":
                $this->query->orderBy("minsalary ASC, maxsalary ASC");
                break;

            case "from":
                break;

            case "time":
            default:
                break;
        }
        $this->query->orderBy("datecreated DESC");
    }

}

?>
