<?php

use LaraTui\EventBus;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;

describe('test event bus', function () {
    beforeEach(function () {
        $this->eventBus = new EventBus();
    });

    test('can pass char key event to handlers', function () {
        $firstCalledWith = false;
        $secondCalledWith = false;

        $firstHandler = function ($data) use (&$firstCalledWith) {
            $firstCalledWith = $data;
        };

        $secondHandler = function ($data) use (&$secondCalledWith) {
            $secondCalledWith = $data;
        };

        $this->eventBus->listen('k', $firstHandler);
        $this->eventBus->listen('k', $secondHandler);

        $this->eventBus->emit(CharKeyEvent::new('k'), ['some_data']);

        expect($firstCalledWith)->toBe(['some_data']);
        expect($secondCalledWith)->toBe(['some_data']);
    });

    test('can pass key code event to handlers', function () {
        $calledWith = false;

        $handler = function ($data) use (&$calledWith) {
            $calledWith = $data;
        };

        $this->eventBus->listen(KeyCode::Enter, $handler);

        $this->eventBus->emit(CodedKeyEvent::new(KeyCode::Enter), ['some_data']);

        expect($calledWith)->toBe(['some_data']);
    });
});
