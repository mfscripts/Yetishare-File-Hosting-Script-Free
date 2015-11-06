<?php
 
/**
 * QoS Bandwidth Throttler (part of Lotos Framework)
 *
 * Copyright (c) 2005-2010 Artur Graniszewski (aargoth@boo.pl)
 * All rights reserved.
 *
 * @category   Library
 * @package    Lotos
 * @subpackage QoS
 * @copyright  Copyright (c) 2005-2010 Artur Graniszewski (aargoth@boo.pl)
 * @license    GNU LESSER GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * @version    $Id$
 */
 
/**
 * Configuration interface.
 */
interface IThrottleConfig {}
 
/**
 * Configuration class.
 */
class ThrottleConfig implements IThrottleConfig
{
    /**
     * Burst rate limit in bytes per second.
     *
     * @var int
     */
    public $burstLimit = 10000;
 
    /**
     * Burst transfer rate time in seconds before reverting to the standard transfer rate.
     *
     * @var int
     */
    public $burstTimeout = 10;
 
    /**
     * Standard rate limit in bytes per second.
     *
     * @var int
     */
    public $rateLimit = 10000;
 
    /**
     * Enable/disable this module.
     *
     * @var bool
     */
    public $enabled = false;
}
