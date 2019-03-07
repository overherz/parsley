<?php


namespace Parsley\Core;


class GroupTask extends Task
{

    public function handles()
    {
        $me   = __CLASS__ . '@';
        $name = $this->getName();

        return array_merge(
            parent::handles(),
            [
                ['parsley.*: *, group.before: ' . $name,],
                ['parsley.*: *, group.after: ' . $name,],
                ['parsley.*: *, group.failure: ' . $name,],
                ['parsley.*: *, group.always: ' . $name,],
            ]
        );
    }
    //    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function before($segment, $payload, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param \Exception                        $exception
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function failure($segment, $payload, $exception, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param mixed                             $result
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @return mixed
//     */
//    public function after($segment, $payload, $result, $args, $task)
//    {
//    }
//
//    /**
//     * @param \Parsley\Plugins\SegmentInterface $segment
//     * @param \Parsley\Core\Payload             $payload
//     * @param \Exception                        $exception
//     * @param mixed                             $result
//     * @param array                             $args
//     * @param \Parsley\Core\Task                $task
//     *
//     * @throws \Exception
//     * @return mixed
//     */
//    public function always($segment, $payload, $exception, $result, $args, $task)
//    {
//        if ($exception) {
//            throw $exception;
//        }
//    }


}