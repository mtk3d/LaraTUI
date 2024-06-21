<?php

use LaraTui\CommandBus;

describe('test commmand bus', function () {
    beforeEach(function () {
        $this->commandBus = new CommandBus();
    });

    test('can dispatch command handler', function () {
        $calledWith = false;
        $handler = function ($data) use (&$calledWith) {
            $calledWith = $data;
        };

        $this->commandBus->reactTo('command_name', $handler);
        $this->commandBus->dispatch('command_name', ['some_data']);

        expect($calledWith)->toBe(['some_data']);
    });
});
