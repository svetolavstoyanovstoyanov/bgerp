<?php

/**
 * Клас  'tests_Test' - Разни тестове на PHP-to
 *
 *
 * @category  ef
 * @package   test
 * @author    Milen Georgiev <milen@download.bg>
 * @copyright 2006 - 2012 Experta OOD
 * @license   GPL 3
 * @since     v 0.1
 * @link
 */
class tests_Test extends core_Manager {

    function act_Regexp()
    {
        preg_match('/(\d+)[ ]*(d|day|days|д|ден|дни|дена)\b/u', "2 дена", $matches);

        bp($matches);
    }
}