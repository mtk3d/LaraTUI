<?php

use LaraTui\EventBus;

describe('test event bus', function () {
    beforeEach(function () {
        $this->eventBus = new EventBus();
    });

    test('can pass event to handler', function () {
        $firstCalledWith = false;
        $secondCalledWith = false;

        $firstHandler = function ($data) use (&$firstCalledWith) {
            $firstCalledWith = $data;
        };

        $secondHandler = function ($data) use (&$secondCalledWith) {
            $secondCalledWith = $data;
        };

        $this->eventBus->listenTo('event_name', $firstHandler);
        $this->eventBus->listenTo('event_name', $secondHandler);

        $this->eventBus->emit('event_name', ['some_data']);

        expect($firstCalledWith)->toBe(['some_data']);
        expect($secondCalledWith)->toBe(['some_data']);
    });
});
