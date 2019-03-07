<?php


namespace Parsley\Plugins\Core;


use Illuminate\Container\Container;
use Parsley\Core\Payload;
use Parsley\Plugins\SegmentInterface;
use Parsley\Plugins\PluginTrait;
use Parsley\Plugins\SegmentTrait;

class PayloadCheckerPlugin implements SegmentInterface
{
    use SegmentTrait;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->type = 'plugin';
        $this->name = get_class($this);
    }

    /**
     * Return array of events and their handlers (with optional priority).
     *
     * @return array
     */
    public function handles()
    {
        $me = __CLASS__ . '@';

        return [
            ['parsley.plugin: *, send', $me . 'onSend'],
        ];
    }

    /**
     * @param \Parsley\Plugins\SegmentInterface $segment
     * @param Payload                           $payload
     */
    public function onSend($segment, $payload)
    {
        $props = $payload->getProperties();

        /** @var \Parsley\Application $application */
        $application = $this->container['parsley.application'];

        // TODO: dirty hack, get rid of it

        if (empty($props->getMessageId()) && $props->getMessageId() != 0) {
            // TODO: make id maker be accessible via container (or plugin?)
            // http://php.net/manual/en/function.uniqid.php#94959
            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),

                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,

                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            $props->setMessageId($uuid);
        }

        $props->setAppId($application->getName());
        $props->setType('payload');
        $props->setContentType($this->container['config']->get('parsley.payload.content_type', 'application/json'));

//        if (!$props->getAppId()) {
//            $props->setAppId($segment->getName());
//        }
//
//        if (!$props->getType()) {
//            $props->setType('payload');
//        }
//
//        if (!$props->getContentType()) {
//            $props->setContentType($this->container['config']->get('parsley.payload.content_type', 'application/json'));
//        }
    }
} 