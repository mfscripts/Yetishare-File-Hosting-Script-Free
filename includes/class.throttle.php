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
 * The main class.
 */
class Throttle
{
    /**
     * Last heartbeat time in microseconds.
     *
     * @var int
     */
    protected $lastHeartBeat = 0;
 
    /**
     * First (starting) heartbeat time in microseconds.
     *
     * @var int
     */
    protected $firstHeartBeat = 0;
 
    /**
     * Number of bytes already sent.
     *
     * @var int
     */
    protected $bytesSent = 0;
 
    /**
     * Total sending time in microseconds.
     *
     * @var int
     */
    protected $sendingTime = 0;
 
    /**
     * Current transfer rate in bytes per second.
     *
     * @var int
     */
    protected $currentLimit = 0;
 
    /**
     * Is this the last packet to send?
     *
     * @var bool
     */
    protected $isFinishing = false;
 
    /**
     * @var ThrottleConfig
     */
    protected $config;
 
    /**
     * Create new instance of throttler
     *
     * @param IThrottleConfig $config Configuration object or null to use system defaults
     * @return Throttle
     */
    public function __construct(IThrottleConfig $config = null) {
        if(function_exists('apache_setenv')) {
            // disable gzip HTTP compression so it would not alter the transfer rate
            apache_setenv('no-gzip', '1');
        }
        // disable the script timeout if supported by the server
        if(false === strpos(ini_get('disable_functions'), 'set_time_limit')) {
            // suppress the warnings (in case of the safe_mode)
            @set_time_limit(0);
        }
        if($config) {
            $this->config = $config;
        } else {
            $this->config = new ThrottleConfig();
        }
 
        // set the burst rate by default as the current transfer rate
        $this->currentLimit = $this->config->burstLimit;
 
        // set the output callback
        ob_start(array($this, 'onFlush'), $this->config->rateLimit);
    }
 
    /**
     * Throttler destructor
     *
     * @return void
     */
    public function __destruct() {
        $this->isFinishing = true;
    }
 
    /**
     * Throttling mechanism
     *
     * @param string $buffer Input buffer
     * @return string Output buffer (the same as input)
     */
    public function onFlush(& $buffer) {
        // do nothing when buffer is empty (in case of implicit ob_flush() or script halt)
        // and check if this is a last portion of the output, if it is - do not throttle
        if($buffer === "" || $this->isFinishing) {
            return $buffer;
        }
 
        // cache the buffer length for futher use
        $bufferLength = strlen($buffer);
 
        // cache current microtime to save us from executing too many system request
        $now = microtime(true);
 
        // initialize last heartbeat time if this is a first iteration of the callback
        if($this->lastHeartBeat === 0) {
            $this->lastHeartBeat = $this->firstHeartBeat = $now;
        }
 
        // calculate how much data have we have to send to the user, so we can set the appropriate time delay
        // if the buffer is smaller than the current limit (per second) send it proportionally faster than the full
        // data package
        $usleep = $bufferLength / $this->currentLimit;
        if($usleep > 0) {
            usleep($usleep * 1000000);
            $this->sendingTime += $usleep;
        }
 
        // check if the burst rate is active, and if we should switch it off (in both if cases)
        if($this->currentLimit === $this->config->burstLimit && $this->config->burstLimit !== $this->config->rateLimit) {
            if(isset($this->config->burstSize)) {
                if($this->config->burstSize < $this->bytesSent + $bufferLength) {
                    $this->currentLimit = $this->config->rateLimit;
                }
            } else {
                if($now > ($this->firstHeartBeat + $this->config->burstTimeout)) {
                    $this->currentLimit = $this->config->rateLimit;
                }
            }
        }
 
        // update system statistics
        $this->bytesSent += $bufferLength;
        $this->lastHeartBeat = $now;
 
        return $buffer;
    }
 
    /**
     * Returns throttle statistics.
     *
     * @return stdClass
     */
    public function getStatistics() {
        if(ob_get_level() > 0) {
            ob_flush();
        }
        $stats = new stdClass();
        $stats->bytesSent = $this->bytesSent;
        $stats->sendingTime = $this->sendingTime;
        $stats->averageRate = $this->sendingTime > 0 ? $this->bytesSent/$this->sendingTime : $stats->bytesSent;
        return $stats;
    }
}