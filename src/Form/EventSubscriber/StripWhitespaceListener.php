<?php

/*
 * Copyright (C) 2018 Techpike s.r.o.
 * All Rights Reserved.
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

namespace App\Form\EventSubscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StripWhitespaceListener implements EventSubscriberInterface
{
    // TODO: Maybe leave delete this method and make this one of the tasks - to ensure that spaces get removed on submit?
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();

        if (is_string($data)) {
            $event->setData(preg_replace("/\s/", '', $data));
        }
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'onPreSetData'];
    }
}
