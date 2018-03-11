<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 3/11/18
 * Time: 4:56 PM
 */

namespace App\Api;


interface ApiSerializable
{
    /**
     * remove all array collections, setting them to null
     */
    public function flattenDoctrineStructures();
}