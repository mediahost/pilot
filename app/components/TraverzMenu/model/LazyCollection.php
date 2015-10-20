<?php

namespace Pupek\TraversalMenu\Model;

/**
 * LazyCollection DataSource
 *
 * @author Petr Poupě
 */
class LazyCollection implements \IDataSource // z dibi
{

    private $mapper;
    private $query;
    private $limit;
    private $offset;

    public function __construct(ITraversalMapper $mapper, \DibiFluent $query)
    {
        $this->mapper = $mapper;
        $this->query = $query;
    }

    /**
     * vyžadováno interfacem IteratorAggregate
     * je možné pak procházet jako foreach ($datasource as $row) { ...
     */
    public function getIterator()
    {
        $result = $this->query->getIterator($this->limit, $this->offset);

        $data = array();
        foreach ($result as $row) {
            $data[$row->id] = $this->mapper->load($row);
        }

        return new \ArrayIterator($data);
    }

    public function count()
    {
        return $this->query->count();
    }

    public function where($cond)
    {
        if (func_num_args() > 1) {
            $cond = func_get_args();
        }

        $this->query->where('%ex', (array) $cond);

        return $this;
    }

    public function applyLimit($limit, $offset)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function orderBy(array $order)
    {
        $this->query->orderBy($order);
    }
    
}

?>
