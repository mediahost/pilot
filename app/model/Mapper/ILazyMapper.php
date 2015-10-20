<?php

namespace Model\Mapper;

/**
 * Mapper Interface
 * 
 * @author Petr Poupě
 */
interface ILazyMapper
{

    /** získat DataSource pro všechny záznamy */
    function allDataSource();

    /** načte potřebná data */
    function load($row);
}

?>
