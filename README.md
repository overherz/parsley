[![Build Status](https://travis-ci.org/pinepain/parsley.png?branch=master)](https://travis-ci.org/pinepain/parsley)

Parsley
=======

Dependencies
------------

PEAR extensions

 - [amqp](http://pecl.php.net/package/amqp) (requires librabbitmq)

Planned dependencies
--------------------

NOTE: currently, library doesn't depend on it

 - [inotify](http://pecl.php.net/package/inotify) (requires libinotify)

TODO
----

- [ ] - set and check tasks and payloads reguired plugins list

- [ ] - (re)move hardcoded argument typehinging to phpdoc comment
- [ ] - task extended redelivery (via plugin)
- [ ] - task chaining (via plugin)
- [ ] - delayed tasks (a-la cron), (via plugin)
- [ ] - specify worker to run task on (via plugins)
- [ ] - message signature (via plugins, may require some changes in AMQPy)
- [ ] - RPC support (synchronous task execution, result polling) - via plugin?
- [ ] - local task execution (may be part of RPC)

- [ ] - cross-task events + handlers (later, on demand)

- [ ] - move basic functionality from TasksPool and SerializersPool to separate Pool class
- [ ] - move uuid generation via `uuid` to plugins (by default use system uniquid generator or something else)
        (currently generated manually - we migrate from uuid extension).
- [ ] - move workers monitoring via `inotify` to plugins
- [ ] - application protocol documentation and conventions
- [ ] - unit tests
- [ ] - docs
- [ ] - code coverage
- [ ] - custom exchanges for tasks
- [ ] - custom queues for tasks
- [ ] - resource usage plugin
- [ ] - trace plugin (or use built-in from rabbitmq?)
- [ ] - examples

- [x] - cleanup (uuid stuff)
- [x] - use di container
- [x] - events support


Protocol
--------

If task received by Broker which can't process it then such task should be redelivered **without** `try (TODO: name it)`
header field changed. NOTE: same broker may receive rejected message again, so we may add some delay (but what it gives
for us?)

OR it should publish same message to origin exchange **with** `try` header flag decrement while such flag is > 0

### Headers

parsley-retries + parsley-attempted

### Messages flow

Default:

Default queue goes bound to default exchange by task name

TODO:

- discover task
- register task
- deregister task

### Known issues

 1. When blocking broker used 'started' event is