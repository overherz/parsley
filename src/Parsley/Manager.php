<?php


namespace Parsley;

// Run new applications and manage their number

class Manager {
    public function __construct() {
        // get all tasks list, create one by one and get their name,
        // then get from config their queues and exchanges, if any, or set default ones
        // by design one application listen for one task on one queue, but you can specify multiple queues
    }
}